<?php

namespace BNA;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bna_inject_global_alerts() {
    global $bna_global_alerts;

    if ( ! empty( $bna_global_alerts ) ) {
        echo '<div class="bna-global-alerts-carousel">';
        foreach ( $bna_global_alerts as $alert_html ) {
            echo '<div class="bna-alert-slide">' . $alert_html . '</div>';
        }
        echo '</div>';
    }
}

add_action( 'wp_body_open', 'BNA\bna_inject_global_alerts' );

