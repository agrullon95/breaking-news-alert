<?php

namespace BNA;

use Aws\Sqs\SqsClient;
use Aws\Exception\AwsException;

class AlertQueueClient {
    private $client;
    private $queueUrl;

    public function __construct($region, $key, $secret, $queueUrl) {
        $this->client = new SqsClient([
            'region' => $region,
            'version' => 'latest',
            'credentials' => [
                'key' => $key,
                'secret' => $secret,
            ],
        ]);
        $this->queueUrl = $queueUrl;
    }

    public function sendMessage(string $messageBody, array $options = []): bool
    {
        // Options with sensible defaults
        $type       = $options['type']       ?? 'info';   // info | warning | error | success
        $priority   = $options['priority']   ?? 5;        // numeric priority
        $ttlSeconds = $options['ttlSeconds'] ?? null;     // e.g., 6*60*60 for 6 hours
        $expiresAtMs = $options['expiresAtMs'] ?? null;   // absolute epoch ms
        $extraAttrs = $options['attributes'] ?? [];       // additional MessageAttributes
        $groupId    = $options['groupId']    ?? null;     // for FIFO queues
        $dedupId    = $options['dedupId']    ?? null;     // for FIFO queues

        // Compute expiry if TTL given
        if (!$expiresAtMs && $ttlSeconds) {
            $expiresAtMs = (int) round(microtime(true) * 1000) + ((int) $ttlSeconds * 1000);
        }

        // Base attributes
        $messageAttributes = [
            'type' => [
                'DataType'    => 'String',
                'StringValue' => (string) $type,
            ],
            'priority' => [
                'DataType'    => 'Number',     // for numeric semantics
                'StringValue' => (string) $priority,
            ],
        ];

        if ($expiresAtMs) {
            $messageAttributes['expiresAt'] = [
                'DataType'    => 'Number',
                'StringValue' => (string) $expiresAtMs,
            ];
        }

        // Merge any extra attributes (caller-provided) — these can override defaults if keys collide
        foreach ($extraAttrs as $key => $attr) {
            // Expecting shape: ['DataType' => 'String'|'Number'|'Binary', 'StringValue' => '...', (or 'BinaryValue')]
            if (is_array($attr) && isset($attr['DataType']) && (isset($attr['StringValue']) || isset($attr['BinaryValue']))) {
                $messageAttributes[$key] = $attr;
            }
        }

        // Build params
        $params = [
            'QueueUrl'          => $this->queueUrl,
            'MessageBody'       => $messageBody,
            'MessageAttributes' => $messageAttributes,
        ];

        // FIFO support if the queue is FIFO or caller provided group/dedup
        $isFifo = str_ends_with((string) $this->queueUrl, '.fifo');
        if ($isFifo) {
            $params['MessageGroupId'] = $groupId ?: 'alerts';
            // Create a reasonable default dedup ID if not provided
            $params['MessageDeduplicationId'] = $dedupId ?: hash('sha256', $messageBody . json_encode($messageAttributes));
        }

        try {
            $result = $this->client->sendMessage($params);
            error_log('SQS Message sent. MessageId: ' . $result->get('MessageId'));
            return true;
        } catch (AwsException $e) {
            error_log('SQS sendMessage error: ' . $e->getAwsErrorMessage());
            return false;
        }
    }

    public function getDecodedMessages($maxNumber = 1) {
        try {
            $rawMessages = $this->receiveMessages($maxNumber);

            $decoded = [];

            foreach ($rawMessages as $msg) {
                $body = $msg['Body'] ?? '';
                $payload = $this->tryJson($body);

                $sns = null;

                // Handle SNS → SQS envelope
                if (is_array($payload) && isset($payload['Type']) && isset($payload['Message'])) {
                    $sns = $payload;
                    $inner = $this->tryJson($sns['Message']);
                    $payload = is_array($inner) ? $inner : ['message' => $sns['Message']];
                }

                $messageAttrs = $this->flattenMessageAttributes($msg['MessageAttributes'] ?? []);

                // Extract metadata
                $id = $msg['MessageId'] ?? '';
                $receiptHandle = $msg['ReceiptHandle'] ?? null;
                $sentTs = isset($msg['Attributes']['SentTimestamp']) ? (int) $msg['Attributes']['SentTimestamp'] : null;

                // Time resolution
                $time =
                    ($payload['time'] ?? null) ?:
                    ($payload['timestamp'] ?? null) ?:
                    ($sns['Timestamp'] ?? null) ?:
                    ($sentTs ? date(DATE_ATOM, (int)($sentTs / 1000)) : null);

                // Type resolution
                $type = $payload['type'] ?? ($messageAttrs['type'] ?? null);

                // Merge everything
                $merged = array_merge(
                    $messageAttrs,
                    is_array($payload) ? $payload : [],
                    [
                        'id' => $id,
                        'receiptHandle' => $receiptHandle,
                        'time' => $time,
                        'type' => $type,
                    ]
                );

                if (!isset($merged['message'])) {
                    $merged['message'] = is_string($body) ? $body : json_encode($body);
                }

                $decoded[] = $merged;
            }

            return $decoded;
        } catch (\Throwable $e) {
            error_log('Error receiving or decoding SQS messages: ' . $e->getMessage());
            return [[
                'id' => null,
                'receiptHandle' => null,
                'message' => 'Error retrieving messages',
                'time' => null,
                'type' => 'error',
            ]];
        }
    }

    
    public function receiveMessages($maxNumber = 1) {
        try {
            $result = $this->client->receiveMessage([
                'QueueUrl' => $this->queueUrl,
                'MaxNumberOfMessages' => $maxNumber,
                'WaitTimeSeconds' => 5, // Long polling for 5 seconds
            ]);

            if (empty($result->get('Messages'))) {
                return [];
            }

            return $result->get('Messages');
        } catch (\Exception $e) {
            error_log('Error receiving SQS messages: ' . $e->getMessage());
            return [];
        }
    }

    public function deleteMessage( $receiptHandle ) {
        try {
            $this->client->deleteMessage([
                'QueueUrl' => $this->queueUrl,
                'ReceiptHandle' => $receiptHandle,
            ]);
            return true;
        } catch (\Exception $e) {
            error_log('Error deleting SQS message: ' . $e->getMessage());
            return false;
        }
    }

    private function tryJson($value): ?array {
        if (!is_string($value) || $value === '') return null;
        $data = json_decode($value, true);
        return (json_last_error() === JSON_ERROR_NONE && is_array($data)) ? $data : null;
    }

    private function flattenMessageAttributes(array $attrs): array {
        $out = [];
        foreach ($attrs as $key => $attr) {
            if (isset($attr['StringValue'])) {
                $out[$key] = $attr['StringValue'];
            } elseif (isset($attr['BinaryValue'])) {
                $out[$key] = $attr['BinaryValue'];
            }
        }
        return $out;
    }

}