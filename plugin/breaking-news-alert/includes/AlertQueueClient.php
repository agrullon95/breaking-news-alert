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
            $rawMessages = $this->receiveMessages(1);
            $decodedMessages = [];

            foreach ($rawMessages as $msg) {
                $body = $msg['Body'];
                $data = json_decode($body, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    $decodedMessages[] = [
                        'id' => $msg['MessageId'] ?? '',
                        'receiptHandle' => $msg['ReceiptHandle'] ?? null,
                        'message' => $data['message'] ?? null,
                        'time' => $data['time'] ?? null,
                    ];
                } else {
                    error_log('Invalid JSON in SQS message: ' . $body);
                }
            }

            return $decodedMessages;
        } catch (\Exception $e) {
            error_log('Error receiving or decoding SQS messages: ' . $e->getMessage());
            return [
                [
                    'id' => null,
                    'receiptHandle' => null,
                    'message' => 'Error retrieving messages',
                    'time' => null,
                ]
            ];
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
}