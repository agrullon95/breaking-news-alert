<?php

namespace BNA;

// ADD THIS TO breaking-news-alert.php bna_register_block()
//
// if (defined('WP_DEBUG') && WP_DEBUG) {
//     bna_test_sqs_client($sqsClient);
// }

function bna_test_sqs_client($sqsClient) {
    // Send a test message
    $messageBody = json_encode(['test' => 'Hello from test!']);
    $sendResult = $sqsClient->sendMessage($messageBody);

    if ($sendResult) {
        error_log('SQS test message sent successfully.');
    } else {
        error_log('Failed to send SQS test message.');
        return;
    }

    // Receive messages
    $messages = $sqsClient->receiveMessages(1);
    foreach ($messages as $msg) {
        error_log('Received message: ' . $msg['Body']);

        // Delete message after processing
        $deleted = $sqsClient->deleteMessage($msg['ReceiptHandle']);
        error_log('Message deleted: ' . ($deleted ? 'yes' : 'no'));
    }
}