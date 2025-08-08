<?php

namespace BNA;

function register_hooks($sqsClient) {

    function bna_admin_init( $sqsClient ) {
        add_action( 'admin_init', function() use ( $sqsClient ) {
            bna_handle_form_submission( $sqsClient );
        });
    }

    add_action( 'init', function() use ( $sqsClient ) {
        bna_admin_init( $sqsClient );
    });
}
