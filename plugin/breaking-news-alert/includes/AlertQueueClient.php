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

    public function sendMessage($messageBody) {
        try {
            $result = $this->client->sendMessage([
                'QueueUrl' => $this->queueUrl,
                'MessageBody' => $messageBody,
            ]);
            error_log('SQS Message sent. MessageId: ' . $result->get('MessageId'));
            return true;
        } catch (AwsException $e) {
            error_log('SQS sendMessage error: ' . $e->getMessage());
            return false;
        }
    }

    public function getDecodedMessages($maxNumber = 1) {
        try {
            error_log('MaxNumberOfMessages: ' . $maxNumber);
            $rawMessages = $this->receiveMessages($maxNumber);
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