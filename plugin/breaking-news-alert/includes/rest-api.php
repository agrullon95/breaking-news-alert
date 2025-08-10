<?php

namespace BNA;

use BNA\AlertQueueClient;

function bna_register_rest_routes(AlertQueueClient $sqsClient) {
    add_action('rest_api_init', function () use ($sqsClient) {
        register_rest_route('bna/v1', '/alerts', [
            'methods' => 'GET',
            'callback' => function() use ($sqsClient) {
                $messages = $sqsClient->getDecodedMessages(1);

                error_log(print_r($messages, true));

                $alerts = [];
                foreach ($messages as $msg) {
                    $alerts[] = [
                        'id'             => $msg['id'] ?? uniqid(),
                        'message'        => $msg['message'] ?? '',
                        'time'           => $msg['time'] ?? '',
                        'receiptHandle'  => $msg['receiptHandle'] ?? '',
                        
                        // 👇 Block-specific metadata
                        'type'           => $msg['type'] ?? 'info',
                        'isDismissible'  => $msg['dismissible'] ?? true,
                    ];
                }
                
                return rest_ensure_response($alerts);
            },
            'permission_callback' => function() {
                return '__return_true';
            },
        ]);
    });
}