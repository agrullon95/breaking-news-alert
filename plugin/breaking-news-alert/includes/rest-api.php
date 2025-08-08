<?php

namespace BNA;

use BNA\AlertQueueClient;

function bna_register_rest_routes(AlertQueueClient $sqsClient) {
    add_action('rest_api_init', function () use ($sqsClient) {
        register_rest_route('bna/v1', '/alerts', [
            'methods' => 'GET',
            'callback' => function() use ($sqsClient) {
                $messages = $sqsClient->getDecodedMessages(5);

                error_log(print_r($messages, true));

                $alerts = [];
                foreach ($messages as $msg) {
                    $alerts[] = [
                        'id' => $msg['id'] ?? uniqid(),
                        'message' => $msg['message'] ?? '',
                        'time' => $msg['time'] ?? '',
                        'receiptHandle' => $msg['receiptHandle'] ?? '',
                    ];
                }

                return rest_ensure_response($alerts);
            },
            'permission_callback' => function() {
                // return current_user_can('read');
                return '__return_true';
            },
        ]);

        register_rest_route('bna/v1', '/test', [
            'methods' => 'GET',
            'callback' => function() {
                return rest_ensure_response(['success' => true, 'message' => 'Test route is working']);
            },
            'permission_callback' => function() {
                // return current_user_can('read');
                return '__return_true';
            },
        ]);
    });
}