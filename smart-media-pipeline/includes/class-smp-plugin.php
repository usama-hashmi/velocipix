<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once SMP_PLUGIN_PATH . 'admin/class-smp-admin.php';
require_once SMP_PLUGIN_PATH . 'includes/class-smp-image-optimizer.php';
require_once SMP_PLUGIN_PATH . 'admin/class-smp-media-library.php';
require_once SMP_PLUGIN_PATH . 'includes/class-smp-bulk-optimizer.php';
require_once SMP_PLUGIN_PATH . 'includes/class-smp-elementor-analyzer.php';

class SMP_Plugin {

    protected $loader;
    protected $admin;
    protected $optimizer;
    protected $media_library;
    protected $bulk_optimizer;

    public function __construct() {

        $this->loader     = new SMP_Loader();
        $this->admin      = new SMP_Admin();
        $this->optimizer  = new SMP_Image_Optimizer();
        $this->media_library = new SMP_Media_Library();
        $this->bulk_optimizer  = new SMP_Bulk_Optimizer();

        $this->define_admin_hooks();
        add_action(
            'wp_enqueue_scripts',
            [ $this, 'enqueue_frontend_assets' ]
        );

        add_action(

            'wp_ajax_nopriv_smp_frontend_analysis',

            [
                $this,
                'receive_frontend_analysis'
            ]
        );

        add_action(

            'wp_ajax_smp_frontend_analysis',

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
        //             new SMP_Elementor_Analyzer();

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

            'smp-frontend-analyzer',

            SMP_PLUGIN_URL .
            'assets/js/smp-frontend-analyzer.js',

            [],

            SMP_VERSION,

            true
        );

        wp_localize_script(

            'smp-frontend-analyzer',

            'smpFrontend',

            [
                'ajaxUrl' => admin_url(
                    'admin-ajax.php'
                ),

                'nonce' => wp_create_nonce(
                    'smp_frontend_analysis'
                ),

                'debug' => ! empty(
                    get_option(
                        'smp_settings'
                    )['enable_debug_mode']
                ),
            ]
        );
    }

    public function receive_frontend_analysis() {

        check_ajax_referer(
            'smp_frontend_analysis',
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
                $_POST['page_url']
                ?? ''
            ),
        ];

        $stored =
            get_transient(
                'smp_frontend_analysis'
            );

        if ( ! is_array( $stored ) ) {
            $stored = [];
        }

        $stored[
            $data['attachment_id']
        ] = $data;

        set_transient(

            'smp_frontend_analysis',

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