<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bna_register_admin_page() {
    add_menu_page(
        'Breaking News Alerts',     // Page title
        'News Alerts',              // Menu title
        'manage_options',           // Capability needed to see this menu
        'bna-news-alerts',          // Menu slug (unique ID)
        'bna_render_admin_page',    // Callback function to display the page
        'dashicons-megaphone',      // Icon (WordPress dashicon)
        20                          // Position in menu
    );
}

add_action( 'admin_menu', 'bna_register_admin_page' );

function bna_render_admin_page() {
    ?>
    <div class="wrap">
        <h1>Breaking News Alerts</h1>
         <?php settings_errors( 'bna_messages' ); ?>
        <form method="post" action="">
            <?php wp_nonce_field( 'bna_send_alert', 'bna_nonce' ); ?>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label for="alert_message">Alert Message</label></th>
                        <td><input name="alert_message" type="text" id="alert_message" class="regular-text" required></td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button( 'Send Alert' ); ?>
        </form>
    </div>
    <?php
}

function bna_handle_form_submission( $sqsClient ) {
    if (
        ! empty( $_POST['alert_message'] ) &&
        check_admin_referer( 'bna_send_alert', 'bna_nonce' )
    ) {
        $message = sanitize_text_field( wp_unslash( $_POST['alert_message'] ) );

        $messageBody = json_encode([
            'message' => $message,
            'time'    => current_time( 'mysql' ),
        ]);


        $result = $sqsClient->sendMessage( $messageBody );

        set_transient( 'bna_admin_notice', [
            'message' => $result ? 'Alert sent successfully!' : 'Failed to send alert.',
            'type'    => $result ? 'success' : 'error',
        ], 30 ); // expires in 30 seconds

        wp_redirect( admin_url( 'admin.php?page=bna-news-alerts' ) );
        exit;
    }
}

function bna_admin_notices() {
    if ( $notice = get_transient( 'bna_admin_notice' ) ) {
        $class = $notice['type'] === 'success' ? 'notice notice-success' : 'notice notice-error';

        printf(
            '<div class="%1$s"><p>%2$s</p></div>',
            esc_attr( $class ),
            esc_html( $notice['message'] )
        );

        delete_transient( 'bna_admin_notice' );
    }
}
add_action( 'admin_notices', 'bna_admin_notices' );
