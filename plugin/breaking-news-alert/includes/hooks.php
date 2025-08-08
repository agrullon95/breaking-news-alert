<?php

namespace BNA;

function register_hooks($sqsClient) {

    // Send message when saving block 
    // (temporarily removed - testing sending messages from admin page)

    // add_action('save_post', function($post_id) use ($sqsClient) {
    //     if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
    //         return;
    //     }

    //     $post = get_post($post_id);

    //     if (strpos($post->post_content, 'bna/alert') !== false) {
    //         $message = [
    //             'post_id' => $post_id,
    //             'title' => $post->post_title,
    //             'content' => $post->post_content,
    //             'timestamp' => current_time('mysql'),
    //         ];

    //         $sqsClient->sendMessage($message);
    //     }
    // });

    function bna_admin_init( $sqsClient ) {
        add_action( 'admin_init', function() use ( $sqsClient ) {
            bna_handle_form_submission( $sqsClient );
        });
    }

    add_action( 'init', function() use ( $sqsClient ) {
        bna_admin_init( $sqsClient );
    });


}
