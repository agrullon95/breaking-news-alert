<?php
// Load WordPress environment to use plugin and autoloaders
require_once __DIR__ . '/wp-load.php';

use BNA\SQSClient;

// Read AWS config from wp-config or hardcoded (adjust as needed)
$region = defined('BNA_AWS_REGION') ? BNA_AWS_REGION : 'us-east-1';
$key = defined('BNA_AWS_ACCESS_KEY_ID') ? BNA_AWS_ACCESS_KEY_ID : 'YOUR_KEY';
$secret = defined('BNA_AWS_SECRET_ACCESS_KEY') ? BNA_AWS_SECRET_ACCESS_KEY : 'YOUR_SECRET';
$queueUrl = defined('BNA_AWS_SQS_QUEUE_URL') ? BNA_AWS_SQS_QUEUE_URL : 'YOUR_QUEUE_URL';

// Instantiate the SQS client
$sqsClient = new SQSClient($region, $key, $secret, $queueUrl);

// Receive one message
$messages = $sqsClient->receiveMessages(1);

if (!empty($messages)) {
    foreach ($messages as $message) {
        // Log message body
        error_log('Worker received SQS message: ' . $message['Body']);

        // TODO: Processing logic. I'll add this later.

        // Delete message from queue after processing
        $sqsClient->deleteMessage($message['ReceiptHandle']);

        echo "Processed and deleted message ID: " . $message['MessageId'] . PHP_EOL;
    }
} else {
    echo "No messages in queue." . PHP_EOL;
}
