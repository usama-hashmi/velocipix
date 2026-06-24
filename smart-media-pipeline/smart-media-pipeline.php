<?php
/**
 * Plugin Name: MediaLens Pipeline
 * Description: Optimize WordPress image delivery with WebP, AVIF, frontend rendering analysis, and Elementor image intelligence.
 * Version: 1.0.0
 * Author: Usama Hashmi
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: smart-media-pipeline
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SMP_VERSION', '1.0.0' );
define( 'SMP_PLUGIN_FILE', __FILE__ );
define( 'SMP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SMP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once SMP_PLUGIN_PATH . 'includes/class-smp-loader.php';
require_once SMP_PLUGIN_PATH . 'includes/class-smp-plugin.php';

function smp_boot_plugin() {
    $plugin = new SMP_Plugin();
    $plugin->run();
}

smp_boot_plugin();
