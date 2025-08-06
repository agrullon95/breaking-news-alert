<?php
/**
 * Plugin Name: Breaking News Alert
 * Description: Adds a custom Gutenberg block to create news alerts.
 * Version: 1.0.0
 * Author: Anthony Grullon
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';
// require_once __DIR__ . '/includes/SQSClient.php';
require_once __DIR__ . '/includes/hooks.php';

// use BNA\SQSClient;
use BNA\AlertQueueClient;
use function BNA\register_hooks;

$region = defined('BNA_AWS_REGION') ? BNA_AWS_REGION : '';
$key    = defined('BNA_AWS_ACCESS_KEY_ID') ? BNA_AWS_ACCESS_KEY_ID : '';
$secret = defined('BNA_AWS_SECRET_ACCESS_KEY') ? BNA_AWS_SECRET_ACCESS_KEY : '';
$queueUrl = defined('BNA_AWS_SQS_QUEUE_URL') ? BNA_AWS_SQS_QUEUE_URL : '';

// Instantiate the SQS client with AWS credentials and queue URL
$sqsClient = new AlertQueueClient($region, $key, $secret, $queueUrl);

// Register WordPress hooks and pass in the SQS client instance
register_hooks($sqsClient);

// function bna_test_sqs_client() {
//     $region = defined('BNA_AWS_REGION') ? BNA_AWS_REGION : '';
//     $key    = defined('BNA_AWS_ACCESS_KEY_ID') ? BNA_AWS_ACCESS_KEY_ID : '';
//     $secret = defined('BNA_AWS_SECRET_ACCESS_KEY') ? BNA_AWS_SECRET_ACCESS_KEY : '';
//     $queueUrl = defined('BNA_AWS_SQS_QUEUE_URL') ? BNA_AWS_SQS_QUEUE_URL : '';

//     $sqsClient = new BNA\AlertQueueClient($region, $key, $secret, $queueUrl);

//     // Send a test message
//     $messageBody = json_encode(['test' => 'Hello from test!']);
//     $sendResult = $sqsClient->sendMessage($messageBody);

//     if ($sendResult) {
//         error_log('SQS test message sent successfully.');
//     } else {
//         error_log('Failed to send SQS test message.');
//         return;
//     }

//     // Receive messages
//     $messages = $sqsClient->receiveMessages(1);
//     foreach ($messages as $msg) {
//         error_log('Received message: ' . $msg['Body']);

//         // Delete message after processing
//         $deleted = $sqsClient->deleteMessage($msg['ReceiptHandle']);
//         error_log('Message deleted: ' . ($deleted ? 'yes' : 'no'));
//     }
// }


function bna_register_block() {
    // if (defined('WP_DEBUG') && WP_DEBUG) {
    //     bna_test_sqs_client();
    // }
    register_block_type( __DIR__ );
}
add_action( 'init', 'bna_register_block' );
