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
    private $analyzer;

    public function __construct() {

        $this->loader = new VelociPix_Loader();

        $this->optimizer = new VelociPix_Image_Optimizer();

        $this->media_library = new VelociPix_Media_Library();

        $this->bulk_optimizer = new VelociPix_Bulk_Optimizer();

        $this->delivery_engine = new VelociPix_Delivery_Engine();

        /*
        |--------------------------------------------------------------------------
        | Analyzer
        |--------------------------------------------------------------------------
        */

        $this->analyzer =
            new VelociPix_Elementor_Analyzer();

        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        */

        $this->admin =
            new VelociPix_Admin(
                $this->analyzer
            );

        /*
        |--------------------------------------------------------------------------
        | Elementor Integration
        |--------------------------------------------------------------------------
        */

        $this->elementor_integration =
            new VelociPix_Elementor_Integration(
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

        $background_attachments = [];

        if (
            is_singular()
            &&
            get_the_ID()
        ) {

            $background_attachments =

                $this->analyzer
                    ->get_background_attachment_map(
                        get_the_ID()
                    );

        }

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

                'post_id' => get_the_ID(),

                'backgroundAttachments' => $background_attachments,

                'debug' => current_user_can( 'manage_options' )
                && ! empty(
                    get_option(
                        'velocipix_settings'
                    )['enable_debug_mode']
                )

            ]
        );
    }

    public function receive_frontend_analysis() {

        check_ajax_referer(
            'velocipix_frontend_analysis',
            'nonce'
        );

        $attachment_id = intval(
            $_POST['attachment_id']
            ?? 0
        );

        $image_url = esc_url_raw(
            wp_unslash(
                $_POST['image_url']
                ?? ''
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Resolve rendered background URLs
        |--------------------------------------------------------------------------
        |
        | Theme-generated background images may not be present in Elementor's
        | background attachment map. Resolve their rendered URL back to the
        | WordPress attachment ID.
        |
        */

        if (
            ! $attachment_id &&
            $image_url
        ) {

            $attachment_id =
                attachment_url_to_postid(
                    $image_url
                );

            /*
            |--------------------------------------------------------------------------
            | AVIF/WebP fallback
            |--------------------------------------------------------------------------
            |
            | VelociPix may already have replaced the original image URL with
            | AVIF/WebP before the analyzer sees it. WordPress cannot resolve
            | those generated URLs with attachment_url_to_postid(), so try the
            | original JPG/JPEG/PNG equivalents.
            |
            */

            if (
                ! $attachment_id &&
                preg_match(
                    '/\.(avif|webp)$/i',
                    $image_url
                )
            ) {

                $original_base_url =
                    preg_replace(
                        '/\.(avif|webp)$/i',
                        '',
                        $image_url
                    );

                foreach (
                    [
                        '.jpg',
                        '.jpeg',
                        '.png',
                    ] as $extension
                ) {

                    $candidate_url =
                        $original_base_url .
                        $extension;

                    $attachment_id =
                        attachment_url_to_postid(
                            $candidate_url
                        );

                    if ( $attachment_id ) {
                        break;
                    }

                }

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Do not store analysis without a valid attachment ID
        |--------------------------------------------------------------------------
        */

        if ( ! $attachment_id ) {
            wp_send_json_success(
                [
                    'message' =>
                        'Frontend analysis skipped: attachment could not be resolved.',
                ]
            );
        }

        $data = [

            'attachment_id' =>
                $attachment_id,

            'post_id' => intval(
                $_POST['post_id']
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

        $key =

            $data['post_id']

            . ':'

            . $data['attachment_id'];

        $stored[ $key ] = $data;

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