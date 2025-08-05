<?php
/**
 * Plugin Name: Breaking News Alert
 * Description: Adds a custom Gutenberg block to create news alerts.
 * Version: 1.0.0
 * Author: Anthony Grullon
 */

defined( 'ABSPATH' ) || exit;

function bna_register_block() {
    // We'll register the block here later.
}
add_action( 'init', 'bna_register_block' );
