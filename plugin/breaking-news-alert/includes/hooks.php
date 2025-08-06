<?php

namespace BNA;

function register_hooks($sqsClient) {
    add_action('save_post', function($post_id) use ($sqsClient) {
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        $post = get_post($post_id);

        if (strpos($post->post_content, 'bna/alert') !== false) {
            $message = [
                'post_id' => $post_id,
                'title' => $post->post_title,
                'content' => $post->post_content,
                'timestamp' => current_time('mysql'),
            ];

            $sqsClient->sendMessage($message);
        }
    });
}
