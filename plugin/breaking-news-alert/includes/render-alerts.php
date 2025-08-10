<?php

namespace BNA;

function bna_render_alerts_block( $attributes ) {
    $display_globally  = ! empty( $attributes['displayGlobally'] );
    $is_dismissible    = ! empty( $attributes['isDismissible'] );
    $valid_types       = [ 'info', 'warning', 'error' ];
    $type              = 'info'; // fallback
    $message           = 'No alerts available.';
    $unique_id         = uniqid( 'bna-alert-' );

    if ( ! is_admin() && !( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        $api_url = defined( 'BNA_API_URL' ) ? BNA_API_URL : home_url( '/wp-json/bna/v1/alerts' );
        $response = wp_remote_get( $api_url );

        error_log(print_r($response, true));
        error_log('API URL: ' . $api_url);

        if ( ! is_wp_error( $response ) ) {
            $data = json_decode( wp_remote_retrieve_body( $response ), true );
            $fetched_type = $data[0]['type'] ?? null;
            $fetched_msg  = $data[0]['message'] ?? null;

            if ( in_array( $fetched_type, $valid_types, true ) ) {
                $type = $fetched_type;
            }

            if ( is_string( $fetched_msg ) && $fetched_msg !== '' ) {
                $message = $fetched_msg;
            }
        }
    }

    $classes = [
        'wp-block-bna-alert',
        'alert-block',
        'alert-' . $type,
    ];

    if ( ! is_admin() && !( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        $classes[] = 'is-mounted';
    }

    if ( $is_dismissible && ! $display_globally ) {
        $classes[] = 'is-dismissible';
    }

    $output = sprintf(
        '<div class="%s" id="alert-%s" data-alert-id="%s">',
        esc_attr( implode( ' ', $classes ) ),
        esc_attr( $unique_id ),
        esc_attr( $unique_id )
    );

    $output .= '<div class="alert-body" data-alert-id="' . esc_attr( $unique_id ) . '">';
    $output .= '<p class="alert-message">' . esc_html( $message ) . '</p>';
    $output .= '</div>';

    // ✖️ Show dismiss only for local alerts
    if ( $is_dismissible && ! $display_globally ) {
        $output .= '<button class="alert-dismiss" type="button" aria-label="' . esc_attr__( 'Dismiss alert', 'bna' ) . '">';
        $output .= '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">';
        $output .= '<path d="M4 4L12 12M12 4L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />';
        $output .= '</svg></button>';
    }

    $output .= '</div>';

    // 🪄 Store for global injection
    if ( $display_globally ) {
        global $bna_global_alerts;
        $bna_global_alerts[] = $output;
        return ''; // Don't render inline
    }

    return $output;
}

