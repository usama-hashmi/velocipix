<?php
/**
 * Plugin Name: VelociPix
 * Plugin URI: https://velocipix.com
 * Description: Automatically optimize WordPress images with WebP & AVIF conversion, bulk optimization, smart image delivery, frontend image analysis, and Elementor Intelligence.
 * Version: 1.1.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Usama Hashmi
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: velocipix
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'VELOCIPIX_VERSION', '1.1.0' );
define( 'VELOCIPIX_PLUGIN_FILE', __FILE__ );
define( 'VELOCIPIX_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'VELOCIPIX_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once VELOCIPIX_PLUGIN_PATH . 'includes/class-velocipix-loader.php';
require_once VELOCIPIX_PLUGIN_PATH . 'includes/class-velocipix-plugin.php';

function velocipix_boot_plugin() {
    $plugin = new VelociPix_Plugin();
    $plugin->run();
}

velocipix_boot_plugin();
