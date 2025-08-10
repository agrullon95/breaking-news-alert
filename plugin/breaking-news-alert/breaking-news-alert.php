<?php
/**
 * Plugin Name: Breaking News Alert
 * Description: Adds a custom Gutenberg block to create news alerts.
 * Version: 1.0.0
 * Author: Anthony Grullon
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/hooks.php';
require_once __DIR__ . '/includes/admin-page.php';
require_once __DIR__ . '/includes/rest-api.php';
require_once __DIR__ . '/includes/render-alerts.php';
require_once __DIR__ . '/includes/global-handler.php';

use BNA\AlertQueueClient;
use function BNA\register_hooks;
use function BNA\bna_test_sqs_client;
use function BNA\bna_register_rest_routes;

$region = defined('BNA_AWS_REGION') ? BNA_AWS_REGION : '';
$key    = defined('BNA_AWS_ACCESS_KEY_ID') ? BNA_AWS_ACCESS_KEY_ID : '';
$secret = defined('BNA_AWS_SECRET_ACCESS_KEY') ? BNA_AWS_SECRET_ACCESS_KEY : '';
$queueUrl = defined('BNA_AWS_SQS_QUEUE_URL') ? BNA_AWS_SQS_QUEUE_URL : '';

// Instantiate the SQS client with AWS credentials and queue URL
$sqsClient = new AlertQueueClient($region, $key, $secret, $queueUrl);

bna_register_rest_routes($sqsClient);

register_hooks($sqsClient);

function bna_register_block_assets() {
    wp_register_style(
        'bna-alerts-style',
        plugins_url( 'build/editor/style-index.css', __FILE__ ),
        [],
        filemtime( plugin_dir_path( __FILE__ ) . 'build/editor/style-index.css' )
    );

    wp_register_script(
        'bna-alerts-frontend-script',
        plugins_url( 'build/frontend/frontend.js', __FILE__ ),
        [],
        filemtime( plugin_dir_path( __FILE__ ) . 'build/frontend/frontend.js' ),
        true
    );

    wp_register_script(
        'bna-alerts-editor-script',
        plugins_url( 'build/editor/index.js', __FILE__ ),
        [ 
            'wp-i18n',
            'wp-components',
            'wp-block-editor',
            'wp-element',
            'wp-api-fetch',
        ],
        filemtime( plugin_dir_path( __FILE__ ) . 'build/editor/index.js' ),
        true
    );
}
add_action( 'init', 'bna_register_block_assets' );

function bna_register_block() {
    register_block_type( __DIR__, [
        'render_callback' => 'BNA\bna_render_alerts_block',
    ] );
}
add_action( 'init', 'bna_register_block' );
