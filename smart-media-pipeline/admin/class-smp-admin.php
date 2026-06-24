<?php


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SMP_Admin {

    public function __construct() {

        add_action(
            'admin_menu',
            [ $this, 'register_menu' ]
        );

        add_action(
            'admin_init',
            [ $this, 'register_settings' ]
        );

    }

    private $option_name = 'smp_settings';

    public function register_menu() {
        add_menu_page(
            __( 'MediaLens Pipeline', 'smart-media-pipeline' ),
            __( 'MediaLens Pipeline', 'smart-media-pipeline' ),
            'manage_options',
            'smart-media-pipeline',
            [ $this, 'render_settings_page' ],
            'dashicons-format-image',
            65
        );
    }

    public function register_settings() {
        register_setting(
            'smp_settings_group',
            $this->option_name,
            [ $this, 'sanitize_settings' ]
        );

        add_settings_section(

            'smp_image_formats_section',

            __( 'Image Formats', 'smart-media-pipeline' ),

            '__return_false',

            'smart-media-pipeline'
        );

        add_settings_section(

            'smp_frontend_section',

            __( 'Frontend Delivery', 'smart-media-pipeline' ),

            '__return_false',

            'smart-media-pipeline'
        );

        add_settings_section(

            'smp_intelligence_section',

            __( 'Intelligence Features', 'smart-media-pipeline' ),

            '__return_false',

            'smart-media-pipeline'
        );

        add_settings_field(
            'enable_webp',
            __( 'Enable WebP', 'smart-media-pipeline' ),
            [ $this, 'render_enable_webp_field' ],
            'smart-media-pipeline',
            'smp_image_formats_section'
        );

        add_settings_field(
            'enable_avif',
            __( 'Enable AVIF', 'smart-media-pipeline' ),
            [ $this, 'render_enable_avif_field' ],
            'smart-media-pipeline',
            'smp_image_formats_section'
        );

        add_settings_field(
            'webp_quality',
            __( 'WebP Quality', 'smart-media-pipeline' ),
            [ $this, 'render_webp_quality_field' ],
            'smart-media-pipeline',
            'smp_image_formats_section'
        );

        add_settings_field(
            'avif_quality',
            __( 'AVIF Quality', 'smart-media-pipeline' ),
            [ $this, 'render_avif_quality_field' ],
            'smart-media-pipeline',
            'smp_image_formats_section'
        );

        add_settings_field(
            'enable_frontend_replacement',
            __( 'Enable Frontend Replacement', 'smart-media-pipeline' ),
            [ $this, 'render_frontend_replacement_field' ],
            'smart-media-pipeline',
            'smp_frontend_section'
        );

        add_settings_field(
            'enable_debug_mode',

            __( 'Enable Debug Mode', 'smart-media-pipeline' ),

            [ $this, 'render_checkbox_field' ],

            'smart-media-pipeline',

            'smp_intelligence_section',

            [
                'label_for' =>
                    'enable_debug_mode',
            ]
        );
    }

    public function sanitize_settings( $input ) {
        return [

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
    }

    public function enqueue_assets( $hook ) {
        if ( 'toplevel_page_smart-media-pipeline' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'smp-admin',
            SMP_PLUGIN_URL . 'assets/css/admin.css',
            [],
            SMP_VERSION
        );

        wp_enqueue_script(
            'smp-admin',
            SMP_PLUGIN_URL . 'assets/js/admin.js',
            [ 'jquery' ],
            SMP_VERSION,
            true
        );

        wp_localize_script(
            'smp-admin',
            'smp_bulk_optimizer',
            [
                'nonce' => wp_create_nonce(
                    'smp_bulk_optimizer'
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
            <h1><?php esc_html_e( 'MediaLens Pipeline', 'smart-media-pipeline' ); ?></h1>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'smp_settings_group' );

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

                            echo 'Image Formats';

                            echo '</h2>';

                            echo '<table class="form-table">';

                            do_settings_fields(
                                'smart-media-pipeline',
                                'smp_image_formats_section'
                            );

                            echo '</table>';
                            
                            echo '</div>';

                            echo '<div>';

                            echo '<h2>';

                            echo 'Frontend Delivery';

                            echo '</h2>';

                            echo '<table class="form-table">';

                            do_settings_fields(
                                'smart-media-pipeline',
                                'smp_frontend_section'
                            );

                            echo '</table>';

                            echo '<h2 style="
                                margin-top:20px;
                            ">';

                            echo 'Intelligence Features';

                            echo '</h2>';

                            echo '<table class="form-table">';

                            do_settings_fields(
                                'smart-media-pipeline',
                                'smp_intelligence_section'
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
                                'smart-media-pipeline'
                            ); ?>
                        </h2>

                        <p>
                            <?php esc_html_e(
                                'Optimize existing media library images.',
                                'smart-media-pipeline'
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
                            id="smp-start-bulk-optimization"
                        >
                            <?php esc_html_e(
                                'Optimize Existing Media',
                                'smart-media-pipeline'
                            ); ?>
                        </button>

                        <div
                            id="smp-progress-bar-wrapper"
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
                                id="smp-progress-bar"
                                style="
                                    width:0%;
                                    height:100%;
                                    background:#2271b1;
                                    transition:width 0.3s ease;
                                "
                            ></div>

                        </div>

                        <div
                            id="smp-bulk-optimization-status"
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

                echo 'Elementor Intelligence';

                echo '</h2>';

                $this->render_elementor_analysis();

                echo '<hr style="
                    margin:40px 0;
                ">';

                echo '<h2>';

                echo 'System Status';

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
            name="smp_settings[enable_frontend_replacement]"
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
            name="smp_settings[enable_debug_mode]"
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

        $analyzer = new SMP_Elementor_Analyzer();

        $frontend_analysis =
            get_transient(
                'smp_frontend_analysis'
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

            $oversized_found = false;

            foreach ( $images as $image ) {

                if ( empty( $image['is_oversized'] ) ) {
                    continue;
                }

                if ( ! $oversized_found ) {

                $total_images = 0;

                $severe_count = 0;

                $moderate_count = 0;

                foreach (
                    $images
                    as
                    $count_image
                ) {

                    if (
                        empty(
                            $count_image['is_oversized']
                        )
                    ) {

                        continue;
                    }

                    $total_images++;

                    if (
                        'high'
                        ===
                        $count_image['severity']
                    ) {

                        $severe_count++;
                    }

                    elseif (
                        'medium'
                        ===
                        $count_image['severity']
                    ) {

                        $moderate_count++;
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

                    $oversized_found = true;

                    echo '<div class="smp-page-grid" style="

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
                        ?? 'medium';

                    $severity_color =
                        'high' === $severity
                            ? '#d63638'
                            : '#dba617';

                    $severity_label =
                        'high' === $severity
                            ? 'Severely Oversized'
                            : 'Moderately Oversized';

                echo '<div style="

                background:#fff;

                border:1px solid #dcdcde;

                border-left:4px solid ' . esc_attr( $severity_color ) . ';

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

                    echo '<strong>Selected Size:</strong> ';

                    echo esc_html(
                        $image['image_size']
                    );

                    echo '</div>';

                    echo '<div style="
                        margin-bottom:4px;
                    ">';

                    echo '<strong>Actual Width:</strong> ';

                    echo esc_html(
                        $image['width'] . 'px'
                    );

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

                    if (
                        ! empty(
                            $frontend_data['rendered_width']
                        )
                        &&
                        ! empty(
                            $frontend_data['natural_width']
                        )
                    ) {

                        echo '<div style="
                            line-height:1.8;
                            font-size:14px;
                        ">';

                        echo '<strong>Rendered Width:</strong> ';
                        echo esc_html(
                            $frontend_data['rendered_width']
                        );
                        echo 'px<br>';

                        echo '<strong>Downloaded Width:</strong> ';
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

                        if (
                            ! empty( $image['insight'] )
                        ) {

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

                            if (
                                ! empty(
                                    $image['fix_suggestion']
                                )
                            ) {

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

                        }

                        elseif (
                            ! empty( $image['frontend_analyzed'] )
                        ) {

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

                            echo '✓ Frontend image delivery appears optimized.';

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
                    

                    $status_color = '#dba617';

                    if (
                        'severe' === $image['severity']
                    ) {
                        $status_color = '#d63638';
                    }

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

            if ( $oversized_found ) {

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

            echo '✓ No oversized Elementor images detected';

            echo '</h3>';

            echo '<p style="
                margin:0;
                font-size:14px;
            ">';

            echo 'Your Elementor image delivery currently appears optimized.';

            echo '</p>';

            echo '</div>';
        }

        echo '</div>'; // grid wrapper
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