<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once VELOCIPIX_PLUGIN_PATH . 'admin/class-velocipix-admin.php';
require_once VELOCIPIX_PLUGIN_PATH . 'includes/class-velocipix-image-optimizer.php';
require_once VELOCIPIX_PLUGIN_PATH . 'admin/class-velocipix-media-library.php';
require_once VELOCIPIX_PLUGIN_PATH . 'includes/class-velocipix-bulk-optimizer.php';
require_once VELOCIPIX_PLUGIN_PATH . 'includes/class-velocipix-elementor-analyzer.php';
require_once VELOCIPIX_PLUGIN_PATH . 'includes/class-velocipix-delivery-engine.php';
require_once VELOCIPIX_PLUGIN_PATH . 'includes/class-velocipix-elementor-integration.php';

class VelociPix_Plugin {

    protected $loader;
    protected $admin;
    protected $optimizer;
    protected $media_library;
    protected $bulk_optimizer;
    protected $delivery_engine;
    /**
     * Elementor integration.
     *
     * @var VelociPix_Elementor_Integration
     */
    private $elementor_integration;

    public function __construct() {

        $this->loader     = new VelociPix_Loader();
        $this->admin      = new VelociPix_Admin();
        $this->optimizer  = new VelociPix_Image_Optimizer();
        $this->media_library = new VelociPix_Media_Library();
        $this->bulk_optimizer  = new VelociPix_Bulk_Optimizer();
        $this->delivery_engine = new VelociPix_Delivery_Engine();
        $this->elementor_integration = new VelociPix_Elementor_Integration(
                $this->optimizer
            );

        $this->define_admin_hooks();
        add_action(
            'wp_enqueue_scripts',
            [ $this, 'enqueue_frontend_assets' ]
        );

        add_action(

            'wp_ajax_nopriv_velocipix_frontend_analysis',

            [
                $this,
                'receive_frontend_analysis'
            ]
        );

        add_action(

            'wp_ajax_velocipix_frontend_analysis',

            [
                $this,
                'receive_frontend_analysis'
            ]
        );
        // add_action(
        //     'admin_notices',
        //     function () {

        //         $post_id = 36;

        //         $analyzer =
        //             new VelociPix_Elementor_Analyzer();

        //         $analyzer->debug_test(
        //             $post_id
        //         );
        //     }
        // );
    }

    private function define_admin_hooks() {

        $this->loader->add_action(
            'admin_enqueue_scripts',
            $this->admin,
            'enqueue_assets'
        );
    }

    public function run() {
        $this->loader->run();
    }

    public function enqueue_frontend_assets() {

        wp_enqueue_script(

            'velocipix-frontend-analyzer',

            VELOCIPIX_PLUGIN_URL .
            'assets/js/velocipix-frontend-analyzer.js',

            [],

            VELOCIPIX_VERSION,

            true
        );

        wp_localize_script(

            'velocipix-frontend-analyzer',

            'velocipixFrontend',

            [
                'ajaxUrl' => admin_url(
                    'admin-ajax.php'
                ),

                'nonce' => wp_create_nonce(
                    'velocipix_frontend_analysis'
                ),

                'debug' => ! empty(
                    get_option(
                        'velocipix_settings'
                    )['enable_debug_mode']
                ),
            ]
        );
    }

    public function receive_frontend_analysis() {

        check_ajax_referer(
            'velocipix_frontend_analysis',
            'nonce'
        );

        $data = [

            'attachment_id' => intval(
                $_POST['attachment_id']
                ?? 0
            ),

            'rendered_width' => intval(
                $_POST['rendered_width']
                ?? 0
            ),

            'natural_width' => intval(
                $_POST['natural_width']
                ?? 0
            ),

            'ratio' => floatval(
                $_POST['ratio']
                ?? 0
            ),

            'page_url' => esc_url_raw(
                wp_unslash(
                    $_POST['page_url']
                    ?? ''
                )
            ),
        ];

        $stored =
            get_transient(
                'velocipix_frontend_analysis'
            );

        if ( ! is_array( $stored ) ) {
            $stored = [];
        }

        $stored[
            $data['attachment_id']
        ] = $data;

        set_transient(

            'velocipix_frontend_analysis',

            $stored,

            HOUR_IN_SECONDS
        );

        wp_send_json_success(
            [
                'message' =>
                    'Frontend analysis received.',
            ]
        );
    }
}