<?php


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VelociPix_Admin {

    public function __construct() {

        add_action(
            'admin_menu',
            [ $this, 'register_menu' ]
        );

        add_action(
            'admin_init',
            [ $this, 'register_settings' ]
        );

        add_action(
            'update_option_velocipix_settings',
            [ $this, 'handle_settings_updated' ],
            10,
            2
        );

    }

    private $option_name = 'velocipix_settings';

    public function register_menu() {
        add_menu_page(
            __( 'VelociPix', 'velocipix' ),
            __( 'VelociPix', 'velocipix' ),
            'manage_options',
            'velocipix',
            [ $this, 'render_settings_page' ],
            'dashicons-format-image',
            65
        );
    }

    public function register_settings() {
        register_setting(
            'velocipix_settings_group',
            $this->option_name,
            [ $this, 'sanitize_settings' ]
        );

        add_settings_section(

            'velocipix_image_formats_section',

            __( 'Image Formats', 'velocipix' ),

            '__return_false',

            'velocipix'
        );

        add_settings_section(

            'velocipix_frontend_section',

            __( 'Frontend Delivery', 'velocipix' ),

            '__return_false',

            'velocipix'
        );

        add_settings_section(

            'velocipix_intelligence_section',

            __( 'Intelligence Features', 'velocipix' ),

            '__return_false',

            'velocipix'
        );

        add_settings_field(
            'enable_webp',
            __( 'Enable WebP', 'velocipix' ),
            [ $this, 'render_enable_webp_field' ],
            'velocipix',
            'velocipix_image_formats_section'
        );

        add_settings_field(
            'enable_avif',
            __( 'Enable AVIF', 'velocipix' ),
            [ $this, 'render_enable_avif_field' ],
            'velocipix',
            'velocipix_image_formats_section'
        );

        add_settings_field(
            'webp_quality',
            __( 'WebP Quality', 'velocipix' ),
            [ $this, 'render_webp_quality_field' ],
            'velocipix',
            'velocipix_image_formats_section'
        );

        add_settings_field(
            'avif_quality',
            __( 'AVIF Quality', 'velocipix' ),
            [ $this, 'render_avif_quality_field' ],
            'velocipix',
            'velocipix_image_formats_section'
        );

        add_settings_field(
            'enable_frontend_replacement',
            __( 'Enable Frontend Replacement', 'velocipix' ),
            [ $this, 'render_frontend_replacement_field' ],
            'velocipix',
            'velocipix_frontend_section'
        );

        add_settings_field(
            'enable_debug_mode',

            __( 'Enable Debug Mode', 'velocipix' ),

            [ $this, 'render_checkbox_field' ],

            'velocipix',

            'velocipix_intelligence_section',

            [
                'label_for' =>
                    'enable_debug_mode',
            ]
        );
    }

    public function sanitize_settings( $input ) {

        $settings = [

            'enable_webp' => isset(
                $input['enable_webp']
            ) ? 1 : 0,

            'enable_avif' => isset(
                $input['enable_avif']
            ) ? 1 : 0,

            'webp_quality' => isset(
                $input['webp_quality']
            )
                ? max(
                    0,
                    min(
                        100,
                        (int) $input['webp_quality']
                    )
                )
                : 80,

            'avif_quality' => isset(
                $input['avif_quality']
            )
                ? max(
                    0,
                    min(
                        100,
                        (int) $input['avif_quality']
                    )
                )
                : 60,

            'enable_frontend_replacement' => isset(
                $input['enable_frontend_replacement']
            ) ? 1 : 0,

            'enable_debug_mode' => isset(
                $input['enable_debug_mode']
            )
                ? 1
                : 0,

        ];

        return $settings;
    }

    public function handle_settings_updated(
        $old_value,
        $new_value
    ) {

        if (

            ( $old_value['enable_frontend_replacement'] ?? 0 )

            !==

            ( $new_value['enable_frontend_replacement'] ?? 0 )

        ) {

            $this->regenerate_elementor_css();

        }

    }

    public function enqueue_assets( $hook ) {
        if ( 'toplevel_page_velocipix' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'velocipix-admin',
            VELOCIPIX_PLUGIN_URL . 'assets/css/admin.css',
            [],
            VELOCIPIX_VERSION
        );

        wp_enqueue_script(
            'velocipix-admin',
            VELOCIPIX_PLUGIN_URL . 'assets/js/admin.js',
            [ 'jquery' ],
            VELOCIPIX_VERSION,
            true
        );

        wp_localize_script(
            'velocipix-admin',
            'velocipix_bulk_optimizer',
            [
                'nonce' => wp_create_nonce(
                    'velocipix_bulk_optimizer'
                ),
            ]
        );
    }

    public function render_settings_page() {
        ?>
        <div class="wrap"
            style="
                max-width:none;
            ">
            <h1><?php esc_html_e( 'VelociPix', 'velocipix' ); ?></h1>
            <p class="description">
                <?php esc_html_e(
                    'Image optimization, modern image delivery, and Elementor image intelligence.',
                    'velocipix'
                ); ?>
            </p>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'velocipix_settings_group' );

                echo '<div style="

                display:grid;

                grid-template-columns:
                    minmax(700px, 2fr)
                    minmax(320px, 1fr);

                gap:32px;

                align-items:start;

                margin-top:24px;

                ">';

                    echo '<div style="

                        background:#fff;

                        border:1px solid #dcdcde;

                        border-radius:12px;

                        padding:28px;

                        box-shadow:
                            0 1px 2px rgba(
                                0,
                                0,
                                0,
                                0.04
                            );

                    ">';

                        echo '<div style="

                            display:grid;

                            grid-template-columns:
                                repeat(
                                    auto-fit,
                                    minmax(280px, 1fr)
                                );

                            gap:40px;

                            align-items:start;

                        ">';

                            echo '<div>';

                            echo '<h2>';

                            esc_html_e( 'Image Formats', 'velocipix' );

                            echo '</h2>';

                            echo '<p class="description">';
                                esc_html_e(
                                    'Automatically generate modern image formats for newly uploaded media. Existing images can be optimized using Bulk Optimization.',
                                    'velocipix'
                                );
                            echo '</p>';

                            echo '<table class="form-table">';

                            do_settings_fields(
                                'velocipix',
                                'velocipix_image_formats_section'
                            );

                            echo '</table>';
                            
                            echo '</div>';

                            echo '<div>';

                            echo '<h2>';

                            esc_html_e( 'Frontend Delivery', 'velocipix' );

                            echo '</h2>';

                            echo '<p class="description">';
                                esc_html_e(
                                    'Serve WebP or AVIF images automatically when available. Refresh the frontend after changing this setting.',
                                    'velocipix'
                                );
                            echo '</p>';

                            echo '<table class="form-table">';

                            do_settings_fields(
                                'velocipix',
                                'velocipix_frontend_section'
                            );

                            echo '</table>';

                            echo '<h2 style="
                                margin-top:40px;
                            ">';

                            esc_html_e( 'Intelligence Features', 'velocipix' );

                            echo '</h2>';

                            echo '<p class="description">';
                                esc_html_e(
                                    'Enable browser console logs while testing image delivery and rendering analysis.',
                                    'velocipix'
                                );
                            echo '</p>';

                            echo '<table class="form-table">';

                            do_settings_fields(
                                'velocipix',
                                'velocipix_intelligence_section'
                            );

                            echo '</table>';

                            echo '</div>';

                        echo '</div>';

                        echo '<div style="
                            margin-top:0px;
                        ">';

                        submit_button(
                            'Save Settings'
                        );

                        echo '</div>';

                    echo '</div>';

                    echo '<div style="

                        background:#fff;

                        border:1px solid #dcdcde;

                        border-radius:12px;

                        padding:28px;

                        box-shadow:
                            0 1px 2px rgba(
                                0,
                                0,
                                0,
                                0.04
                            );

                    ">';
                
                        ?>

                        <h2>
                            <?php esc_html_e(
                                'Bulk Optimization',
                                'velocipix'
                            ); ?>
                        </h2>

                        <p class="description">
                            <?php esc_html_e(
                                'Optimize images already stored in your Media Library. Enable WebP and/or AVIF before starting optimization.',
                                'velocipix'
                            ); ?>
                        </p>
                        <?php
                        echo '<div style="
                            margin:12px 0 18px;
                            padding:10px 14px;
                            background:#f6f7f7;
                            border-left:4px solid #2271b1;
                            border-radius:4px;
                            color:#50575e;
                            font-size:13px;
                        ">
                            Optimization runs in your current browser session. Please keep this page open until processing is complete.
                        </div>';
                        ?>
                        <button
                            type="button"
                            class="button button-primary"
                            id="velocipix-start-bulk-optimization"
                        >
                            <?php esc_html_e(
                                'Optimize Existing Media',
                                'velocipix'
                            ); ?>
                        </button>

                        <div
                            id="velocipix-progress-bar-wrapper"
                            style="
                                display:none;
                                width:100%;
                                max-width:400px;
                                height:24px;
                                background:#e0e0e0;
                                border-radius:4px;
                                overflow:hidden;
                                margin-top:20px;
                            "
                        >

                            <div
                                id="velocipix-progress-bar"
                                style="
                                    width:0%;
                                    height:100%;
                                    background:#2271b1;
                                    transition:width 0.3s ease;
                                "
                            ></div>

                        </div>

                        <div
                            id="velocipix-bulk-optimization-status"
                            style="margin-top:20px;"
                        ></div>

                        <?php

                    echo '</div>';

                echo '</div>';

                echo '<hr style="
                    margin:50px 0 40px;
                ">';

                echo '<h2 style="
                    font-size:1.4em;
                ">';

                esc_html_e( 'Elementor Intelligence', 'velocipix' );

                echo '</h2>';

                $this->render_elementor_analysis();

                echo '<hr style="
                    margin:40px 0;
                ">';

                echo '<h2>';

                esc_html_e( 'System Status', 'velocipix' );

                echo '</h2>';

                $this->render_system_status();
                                
                ?>
            </form>
            
        </div>
        <?php
    }

    public function render_enable_webp_field() {

        $options = get_option( $this->option_name );

        ?>

        <input
            type="checkbox"
            name="<?php echo esc_attr( $this->option_name ); ?>[enable_webp]"
            value="1"
            <?php checked( ! empty( $options['enable_webp'] ) ); ?>
        />

        <?php
    }

    public function render_enable_avif_field() {

        $options = get_option( $this->option_name );

        ?>

        <input
            type="checkbox"
            name="<?php echo esc_attr( $this->option_name ); ?>[enable_avif]"
            value="1"
            <?php checked( ! empty( $options['enable_avif'] ) ); ?>
        />

        <?php
    }

    public function render_webp_quality_field() {

        $options = get_option( $this->option_name );

        ?>

        <input
            type="number"
            min="1"
            max="100"
            name="<?php echo esc_attr( $this->option_name ); ?>[webp_quality]"
            value="<?php echo esc_attr( $options['webp_quality'] ?? 80 ); ?>"
        />

        <?php
    }

    public function render_avif_quality_field() {

        $options = get_option( $this->option_name );

        ?>

        <input
            type="number"
            min="1"
            max="100"
            name="<?php echo esc_attr( $this->option_name ); ?>[avif_quality]"
            value="<?php echo esc_attr( $options['avif_quality'] ?? 60 ); ?>"
        />

        <?php
    }

    public function render_frontend_replacement_field() {

        $options = get_option( $this->option_name );

        ?>

        <input
            type="checkbox"
            value="1"
            name="velocipix_settings[enable_frontend_replacement]"
            <?php checked(
                1,
                get_option(
                    $this->option_name
                )['enable_frontend_replacement']
                ?? 0
            ); ?>
        >

        <?php
    }

    public function render_checkbox_field(
        $args
    ) {

        $options = get_option(
            $this->option_name
        );

        $field_name =
            $args['label_for'];

        $value = ! empty(
            $options[ $field_name ]
        );

        ?>

        <input
            type="checkbox"
            value="0"
            name="velocipix_settings[enable_debug_mode]"
            <?php checked(
                1,
                get_option(
                    $this->option_name
                )['enable_debug_mode']
                ?? 0
            ); ?>
        >

        <?php
    }

    public function render_elementor_analysis() {

        if (
            ! did_action(
                'elementor/loaded'
            )
        ) {

            echo '<div style="

                background:#fff8e5;

                border:1px solid #e6db96;

                border-radius:12px;

                padding:24px 28px;

                color:#6c5a00;

                line-height:1.7;

                margin-top:24px;

                box-shadow:
                    0 1px 2px rgba(
                        0,
                        0,
                        0,
                        0.04
                    );

            ">';

            echo '<h3 style="

                margin-top:0;

                margin-bottom:10px;

                color:#6c5a00;

            ">';

            echo 'Elementor plugin not detected';

            echo '</h3>';

            echo '<p style="
                margin:0;
                font-size:14px;
            ">';

            echo 'Install and activate Elementor to enable advanced image intelligence analysis.';

            echo '</p>';

            echo '</div>';

            return;
        }

        $posts = get_posts(
            [
                'post_type' => [ 'page', 'post' ],
                'posts_per_page' => -1,
                'meta_key' => '_elementor_data',
            ]
        );

        $analyzer = new VelociPix_Elementor_Analyzer();

        $frontend_analysis =
            get_transient(
                'velocipix_frontend_analysis'
            );

        if ( ! is_array( $frontend_analysis ) ) {
            $frontend_analysis = [];
        }

        echo '<div style="margin-top:10px;">';

        if ( empty( $posts ) ) {

            echo '<div style="

                background:#f6f7f7;

                border:1px solid #dcdcde;

                border-radius:12px;

                padding:24px 28px;

                color:#1d2327;

                line-height:1.7;

                margin-top:24px;

                box-shadow:
                    0 1px 2px rgba(
                        0,
                        0,
                        0,
                        0.04
                    );

            ">';

            echo '<h3 style="

                margin-top:0;

                margin-bottom:10px;

            ">';

            echo 'No Elementor pages found yet';

            echo '</h3>';

            echo '<p style="
                margin:0;
                font-size:14px;
            ">';

            echo 'Create or edit a page with Elementor to begin analyzing frontend image delivery behavior.';

            echo '</p>';

            echo '</div>';

            echo '</div>';

            return;
        }

        $issues_found = false;

        foreach ( $posts as $post ) {

            $images =
                $analyzer->get_elementor_image_data(
                    $post->ID
                );

            $images_found = false;

            $displayed_images = [];

            foreach ( $images as $image ) {

                $has_elementor_issue = ! empty( $image['is_oversized'] );

                $has_frontend_issue =
                    ! empty( $image['frontend_data'] ) &&
                    ! empty( $image['frontend_data']['ratio'] ) &&
                    floatval( $image['frontend_data']['ratio'] ) > 1.5;

                if ( $has_elementor_issue || $has_frontend_issue ) {

                    $displayed_images[] = $image;
                }
            }

            if ( empty( $displayed_images ) ) {
                continue;
            }

            foreach ( $displayed_images as $image ) {

                if ( ! $images_found ) {    

                $total_images = count( $displayed_images );

                $severe_count   = 0;
                $moderate_count = 0;

                foreach ( $displayed_images as $count_image ) {

                    $elementor_status =
                        $count_image['severity'] ?? 'optimized';

                    $frontend_status =
                        $this->get_frontend_status(
                            $count_image['frontend_data'] ?? []
                        );

                    $overall_status =
                        $this->get_overall_status(

                            $elementor_status,

                            $frontend_status

                        );

                    switch ( $overall_status ) {

                        case 'high':
                            $severe_count++;
                            break;

                        case 'medium':
                            $moderate_count++;
                            break;
                    }
                }

                    echo '<div style="
                        margin-bottom:24px;
                    ">';

                    echo '<h2 style="
                        margin-bottom:8px;
                        font-size:1.2em;
                    ">';

                    echo esc_html(
                        $post->post_title
                    );

                    echo '</h2>';

                    echo '<div style="
                        color:#50575e;
                        font-size:13px;
                        font-weight:500;
                    ">';

                    echo intval(
                        $total_images
                    );

                    echo ' Images';

                    if ( $severe_count > 0 ) {

                        echo ' • ';

                        echo '<span style="
                            color:#d63638;
                            font-weight:600;
                        ">';

                        echo intval(
                            $severe_count
                        );

                        echo ' Severe';

                        echo '</span>';
                    }

                    if ( $moderate_count > 0 ) {

                        echo ' • ';

                        echo '<span style="
                            color:#dba617;
                            font-weight:600;
                        ">';

                        echo intval(
                            $moderate_count
                        );

                        echo ' Moderate';

                        echo '</span>';
                    }

                    echo '</div>';

                    echo '</div>';

                    echo '<div class="velocipix-page-grid" style="

                        display:grid;

                        grid-template-columns:
                            repeat(
                                auto-fit,
                                minmax(520px, 1fr)
                            );

                        gap:24px;

                        align-items:start;

                        margin-bottom:48px;

                    ">';

                    $images_found = true;
                    
                }
                

                $issues_found = true;

                $frontend_data =
                    $frontend_analysis[
                        $image['attachment_id']
                    ]
                    ?? null;

                if (

                    ! empty( $frontend_data )

                    &&

                    (

                        empty(
                            $frontend_data['rendered_width']
                        )

                        ||

                        empty(
                            $frontend_data['natural_width']
                        )

                        ||

                        ! is_numeric(
                            $frontend_data['rendered_width']
                        )

                        ||

                        ! is_numeric(
                            $frontend_data['natural_width']
                        )

                        ||

                        (
                            isset(
                                $frontend_data['ratio']
                            )

                            &&

                            (
                                ! is_numeric(
                                    $frontend_data['ratio']
                                )

                                ||

                                $frontend_data['ratio'] <= 0

                                ||

                                $frontend_data['ratio'] > 20
                            )
                        )
                    )

                ) {

                    $frontend_data = null;
                }

                $severity =
                    $image['severity']
                    ?? 'optimized';

                                /*
                |--------------------------------------------------------------------------
                | Frontend Status
                |--------------------------------------------------------------------------
                */

                $frontend_status =
                    $this->get_frontend_status(
                        $frontend_data
                    );

                /*
                |--------------------------------------------------------------------------
                | Overall Card Status
                |--------------------------------------------------------------------------
                */

                $overall_status =
                    $this->get_overall_status(

                        $severity,

                        $frontend_status

                    );

                $overall =
                    $this->get_overall_display(
                        $overall_status
                    );

                $overall_color =
                    $overall['color'];

                $overall_label =
                    $overall['label'];

                $elementor =
                    $this->get_status_display(
                        $severity
                    );

                $severity_color =
                    $elementor['color'];

                $severity_label =
                    $elementor['label'];

                echo '<div style="

                background:#fff;

                border:1px solid #dcdcde;

                // border-left:4px solid #2271b1;

                border-radius:8px;

                padding:24px;

                margin-bottom:24px;

                display:flex;

                gap:20px;

                align-items:flex-start;

                box-shadow:
                    0 1px 2px rgba(
                        0,
                        0,
                        0,
                        0.04
                    );

                transition:
                    transform .18s ease,
                    box-shadow .18s ease;

                "

                onmouseover="

                    this.style.transform=\'translateY(-2px)\';

                    this.style.boxShadow=\'0 6px 18px rgba(0,0,0,.08)\';

                "

                onmouseout="

                    this.style.transform=\'translateY(0)\';

                    this.style.boxShadow=\'0 1px 2px rgba(0,0,0,.04)\';

                "

                >';

                if (

                    empty(
                        $image['attachment_id']
                    )

                    ||

                    ! get_post(
                        $image['attachment_id']
                    )

                ) {

                    continue;
                }

                echo wp_get_attachment_image(
                    $image['attachment_id'],
                    'thumbnail',
                    false,
                    [
                        'style' => '
                            width:90px;
                            height:90px;
                            object-fit:cover;
                            border-radius:8px;
                            border:1px solid #dcdcde;
                            flex-shrink:0;
                        ',
                    ]
                );
                
                echo '<div style="

                    flex:1;

                    display:flex;

                    flex-direction:column;

                    line-height:1.5;

                    min-width:0;

                ">';

                    echo '<div style="
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                    margin-bottom:18px;
                    ">';

                    echo '<h4 style="
                        margin:0 0 0 0;
                        font-size:16px;
                        font-weight:600;">
                    Elementor Image Analysis
                    </h4>';

                    echo '<span style="
                    padding:6px 14px;
                    border-radius:999px;
                    background:' . esc_attr( $overall_color ) . '20;
                    color:' . esc_attr( $overall_color ) . ';
                    font-weight:600;
                    font-size:12px;
                    ">';

                    echo esc_html( $overall_label );

                    echo '</span>';

                    echo '</div>';

                    echo '<div style="
                        margin-bottom:4px;
                    ">';

                    echo '<strong>Attachment ID:</strong> ';

                    echo esc_html(
                        $image['attachment_id']
                    );

                    echo '</div>';

                    echo '<div style="
                        margin-bottom:4px;
                    ">';

                    echo '<strong>Widget Type:</strong> ';

                    echo esc_html(
                        $image['widget_type']
                    );

                    echo '</div>';

                    echo '<div style="
                        margin-bottom:4px;
                    ">';

                    echo '<strong>Selected Image Size:</strong> ';

                    $image_size = $image['image_size'];

                    if ( 'custom' === $image_size ) {

                        echo 'Custom (' .
                            intval( $image['recommended_width'] ) .
                            ' × ' .
                            intval( $image['recommended_width'] ) .
                            ' px)';

                    } else {

                        if (
                            preg_match(
                                '/^(\d+)x(\d+)$/',
                                $image_size,
                                $matches
                            )
                        ) {

                            echo 'Custom (' .
                                intval( $matches[1] ) .
                                ' × ' .
                                intval( $matches[2] ) .
                                ' px)';

                        } else {

                            echo ucfirst(
                                str_replace(
                                    '_',
                                    ' ',
                                    $image_size
                                )
                            ) .
                            ' (' .
                            intval( $image['recommended_width'] ) .
                            ' px)';
                        }
                    }

                    echo '</div>';

                    echo '<div style="
                        margin-bottom:4px;
                    ">';

                    echo '<strong>Original Image Width:</strong> ';

                    echo esc_html(
                        $image['width'] . 'px'
                    );

                    echo '</div>';

                    echo '<div style="
                    margin-bottom:4px;
                    ">';

                    echo '<strong>Recommended Width:</strong> ';

                    echo intval(
                        $image['recommended_width']
                    );

                    echo 'px';

                    echo '</div>';

                    echo '<div>';

                        echo '<strong>Status:</strong> ';

                        echo '<span style="

                            display:inline-block;

                            padding:6px 12px;

                            border-radius:20px;

                            background:' . esc_attr( $severity_color ) . '15;

                            color:' . esc_attr( $severity_color ) . ';

                            font-weight:600;

                            font-size:13px;

                            letter-spacing:0.2px;

                            margin-left:6px;

                        ">';

                        echo esc_html(
                            $severity_label
                        );

                        echo '</span>';
                    echo '<div style="

                                margin-top:20px;

                                padding:0 16px;

                                background:#f6f7f7;

                                border:1px solid #dcdcde;

                                border-radius:8px;

                                color:#1d2327;

                                line-height:1.6;

                                font-size:13px;

                            ">';
                    switch ( $severity ) {

                        case 'high':

                            echo '<p>
                    The original image is significantly larger than required for the selected Elementor image size.
                    </p>';

                            break;

                        case 'medium':

                            echo '<p>
                    The original image is somewhat larger than required for the selected Elementor image size.
                    </p>';

                            break;

                        default:

                            echo '<p>
                    The selected Elementor image size is appropriate for this image.
                    </p>';

                            break;
                    }

                    echo '</div>';

                    echo '</div>';

                    echo '<div style="
                        margin-top:28px;
                        padding-top:24px;
                        border-top:1px solid #dcdcde;
                    ">';

                    echo '<h4 style="
                        margin:0 0 18px 0;
                        font-size:16px;
                        font-weight:600;
                    ">Frontend Rendering Analysis</h4>';

                    /*
                    |--------------------------------------------------------------------------
                    | Frontend Rendering Status
                    |--------------------------------------------------------------------------
                    */

                    $frontend =
                        $this->get_status_display(
                            $frontend_status
                        );

                    $frontend_color =
                        $frontend['color'];

                    $frontend_label =
                        $frontend['label'];

                    if (
                        ! empty(
                            $frontend_data['rendered_width']
                        )
                        &&
                        ! empty(
                            $frontend_data['natural_width']
                        )
                    ) {

                        echo '<div>';

                        echo '<strong>Rendered Width:</strong> ';
                        echo esc_html(
                            $frontend_data['rendered_width']
                        );
                        echo 'px<br>';

                        echo '<strong>Downloaded Image Width:</strong> ';
                        echo esc_html(
                            $frontend_data['natural_width']
                        );
                        echo 'px<br>';

                        echo '<strong>Overfetch Ratio:</strong> ';
                        echo esc_html(
                            round(
                                $frontend_data['ratio'],
                                1
                            )
                        );
                        echo 'x';

                        echo '</div>';

                        echo '<div style="margin-bottom:18px; margin-top:10px">';

                        echo '<strong>Status:</strong> ';

                        echo '<span style="
                            display:inline-block;
                            padding:6px 14px;
                            border-radius:999px;
                            background:' . esc_attr( $frontend_color ) . '20;
                            color:' . esc_attr( $frontend_color ) . ';
                            font-weight:600;
                        ">';

                        echo esc_html( $frontend_label );

                        echo '</span>';

                        echo '<div style="
                            margin-top:10px;
                            color:#50575e;
                            line-height:1.6;
                            font-size:13px;
                        ">';

                        // echo esc_html( $frontend_message );

                        echo '</div>';

                        echo '</div>';

                        if ( 'optimized' !== $frontend_status ) {

                            if ( ! empty( $image['insight'] ) ) {

                                echo '<div style="

                                    margin-top:20px;

                                    padding:14px 16px;

                                    background:#f6f7f7;

                                    border:1px solid #dcdcde;

                                    border-radius:8px;

                                    color:#1d2327;

                                    line-height:1.6;

                                    font-size:13px;

                                ">';

                                echo '<strong>Insight:</strong><br>';

                                echo esc_html(
                                    $image['insight']
                                );

                                echo '</div>';
                            }

                            if ( ! empty( $image['fix_suggestion'] ) ) {

                                echo '<div style="

                                    margin-top:16px;

                                    padding:14px 16px;

                                    background:#f0f6fc;

                                    border:1px solid #c5d9ed;

                                    border-radius:8px;

                                    color:#1d4f91;

                                    line-height:1.6;

                                    font-size:13px;

                                ">';

                                echo '<strong>Suggested Fix:</strong><br>';

                                echo esc_html(
                                    $image['fix_suggestion']
                                );

                                echo '</div>';
                            }

                        } else {

                            echo '<div style="

                                margin-top:20px;

                                padding:14px 16px;

                                background:#ecf7ed;

                                border:1px solid #b7dfbb;

                                border-radius:8px;

                                color:#256029;

                                line-height:1.6;

                                font-size:13px;

                                font-weight:600;

                            ">';

                            echo '✓ No frontend rendering issues detected.';

                            echo '</div>';
                        }

                    } else {

                        echo '<div style="

                            padding:12px 14px;

                            background:#fcf9e8;

                            border:1px solid #e6db96;

                            border-radius:8px;

                            color:#6c5a00;

                            font-size:13px;

                            display:inline-flex;

                            align-items:center;

                            gap:8px;

                        ">';

                        echo '<strong>Frontend Analysis Pending</strong>';

                        echo '<span title="
                    Visit this page on the frontend to analyze real browser image delivery behavior.
                    " style="
                            cursor:help;
                            font-weight:700;
                        ">ⓘ</span>';

                        echo '</div>';
                    }

                    echo '</div>';

                    $edit_url = admin_url(
                        'post.php?post=' .
                        $image['post_id'] .
                        '&action=elementor'
                    );

                    echo '<div style="

                        margin-top:auto;

                        margin-top:20px;

                        display:flex;

                        justify-content:space-between;

                        align-items:center;

                        gap:16px;

                        flex-wrap:wrap;

                    ">';

                        

                        echo '<a
                            class="button button-primary"
                            href="' . esc_url( $edit_url ) . '"
                            target="_blank"
                        >';

                        echo 'Edit in Elementor';

                        echo '</a>';

                    echo '</div>';

                    echo '</div>';

                echo '</div>';

            }

           if ( $images_found ) {

                echo '</div>';
            }
        }

        if ( ! $issues_found ) {

            echo '<div style="

                background:#ecf7ed;

                border:1px solid #b7dfbb;

                border-radius:12px;

                padding:24px 28px;

                color:#256029;

                line-height:1.7;

                margin-top:24px;

                box-shadow:
                    0 1px 2px rgba(
                        0,
                        0,
                        0,
                        0.04
                    );

            ">';

            echo '<h3 style="

                margin-top:0;

                margin-bottom:10px;

                color:#256029;

            ">';

            echo '✓ No Elementor sizing or frontend delivery issues detected.';

            echo '</h3>';

            echo '<p style="
                margin:0;
                font-size:14px;
            ">';

            echo 'Your Elementor and frontend image delivery currently appears optimized.';

            echo '</p>';

            echo '</div>';
        }

        echo '</div>'; // grid wrapper
    }

    private function regenerate_elementor_css() {

        if ( ! did_action( 'elementor/loaded' ) ) {
            return;
        }

        \Elementor\Plugin::$instance
            ->files_manager
            ->clear_cache();

        \Elementor\Plugin::$instance
            ->files_manager
            ->generate_css();
    }

    private function get_frontend_status( $frontend_data ) {

        if ( empty( $frontend_data ) ) {
            return 'optimized';
        }

        $ratio = floatval(
            $frontend_data['ratio'] ?? 1
        );

        if ( $ratio >= 4 ) {
            return 'high';
        }

        if ( $ratio >= 1.5 ) {
            return 'medium';
        }

        return 'optimized';
    }

    private function get_overall_status(
        $elementor_status,
        $frontend_status
    ) {

        if (
            'high' === $elementor_status ||
            'high' === $frontend_status
        ) {
            return 'high';
        }

        if (
            'medium' === $elementor_status ||
            'medium' === $frontend_status
        ) {
            return 'medium';
        }

        return 'optimized';
    }

    private function get_status_display( $status ) {

        switch ( $status ) {

            case 'high':
                return [
                    'label' => 'Severe',
                    'color' => '#d63638',
                ];

            case 'medium':
                return [
                    'label' => 'Moderate',
                    'color' => '#dba617',
                ];

            default:
                return [
                    'label' => 'Optimized',
                    'color' => '#46b450',
                ];
        }
    }

    private function get_overall_display( $status ) {

        switch ( $status ) {

            case 'high':
                return [
                    'label' => 'Severe Issue',
                    'color' => '#d63638',
                ];

            case 'medium':
                return [
                    'label' => 'Needs Attention',
                    'color' => '#dba617',
                ];

            default:
                return [
                    'label' => 'Healthy',
                    'color' => '#46b450',
                ];
        }
    }

    public function render_system_status() {

        $gd_available =
            extension_loaded( 'gd' );

        $imagick_available =
            class_exists( 'Imagick' );

        $webp_supported =
            function_exists(
                'imagewebp'
            );

        $avif_supported =
            function_exists(
                'imageavif'
            );

        $elementor_active =
            did_action(
                'elementor/loaded'
            );

        $atomic_elements =
            class_exists(
                '\Elementor\Modules\AtomicWidgets\Module'
            );

        ?>

        <table class="widefat striped"
            style="
                max-width:700px;
                margin-top:20px;
            ">

            <tbody>

                <tr>
                    <td>
                        <strong>
                            PHP Version
                        </strong>
                    </td>

                    <td>
                        <?php echo esc_html(
                            PHP_VERSION
                        ); ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>
                            WordPress Version
                        </strong>
                    </td>

                    <td>
                        <?php echo esc_html(
                            get_bloginfo(
                                'version'
                            )
                        ); ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>
                            GD Installed
                        </strong>
                    </td>

                    <td>
                        <?php echo esc_html(
                            $gd_available
                                ? '✅ Yes'
                                : '❌ No'
                        ); ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>
                            Imagick Installed
                        </strong>
                    </td>

                    <td>
                        <?php echo esc_html( 
                            $imagick_available 
                                ? '✅ Yes'
                                : '❌ No'
                        ); ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>
                            WebP Support
                        </strong>
                    </td>

                    <td>
                        <?php echo esc_html( 
                            $webp_supported 
                                ? '✅ Yes'
                                : '❌ No'
                        ); ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>
                            AVIF Support
                        </strong>
                    </td>

                    <td>
                        <?php echo esc_html( 
                            $avif_supported 
                                ? '✅ Yes'
                                : '❌ No'
                        ); ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>
                            Elementor Active
                        </strong>
                    </td>

                    <td>
                        <?php echo esc_html( 
                            $elementor_active 
                                ? '✅ Yes'
                                : '❌ No'
                        ); ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>
                            Atomic Widgets Available
                        </strong>
                    </td>

                    <td>
                        <?php echo esc_html( 
                            $atomic_elements 
                                ? '✅ Detected'
                                : '❌ Not Detected'
                        ); ?>
                    </td>
                </tr>

            </tbody>

        </table>

        <?php
    }

}