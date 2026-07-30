<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VelociPix_Media_Library {

    public function __construct() {

        add_filter(
            'manage_upload_columns',
            [ $this, 'add_columns' ]
        );

        add_action(
            'manage_media_custom_column',
            [ $this, 'render_columns' ],
            10,
            2
        );
    }

    public function add_columns( $columns ) {

        $columns['velocipix_webp_stats'] = 'Media Optimization';

        return $columns;
    }

    public function render_columns( $column_name, $attachment_id ) {

        if ( 'velocipix_webp_stats' !== $column_name ) {
            return;
        }

        $image_optimizer = new VelociPix_Image_Optimizer();

        $data = $image_optimizer->get_image_optimization_data(
            $attachment_id
        );

        $mime_type =
            get_post_mime_type(
                $attachment_id
            );

        if ( empty( $data ) ) {

            echo '—';

            return;
        }

        if (
            'image/svg+xml'
            ===
            $mime_type
        ) {

            echo '<strong>Vector Format (SVG)</strong><br>';

            echo 'No raster optimization required.';

            return;
        }

        $is_modern_upload =
            in_array(
                $mime_type,
                [
                    'image/webp',
                    'image/avif',
                ],
                true
            );

        if ( $is_modern_upload ) {

            echo '<strong>Modern Format Detected</strong><br>';

            echo esc_html(
                strtoupper(
                    str_replace(
                        'image/',
                        '',
                        $mime_type
                    )
                )
            );

            echo ' upload already optimized for modern delivery.';

            return;
        }

        echo '<strong>Original:</strong> '
            . esc_html(
                size_format(
                    $data['original_size']
                )
            )
            . '<br>';

        if ( $data['has_webp'] ) {

            echo '<strong>WebP:</strong> '
                . esc_html(
                    size_format(
                        $data['webp_size']
                    )
                )
                . '<br>';

        } else {

            echo '<strong>WebP:</strong> Missing<br>';
        }

        if ( $data['has_avif'] ) {

            echo '<strong>AVIF:</strong> '
                . esc_html(
                    size_format(
                        $data['avif_size']
                    )
                )
                . '<br>';

        } else {

            echo '<strong>AVIF:</strong> Missing<br>';
        }

        echo '<strong>Saved:</strong> '
            . esc_html(
                $data['savings_percent']
            )
            . '%<br>';

        if (
            $data['has_webp'] ||
            $data['has_avif']
        ) {

            echo '<span style=\"color:green;\"><strong>✓ Optimized</strong></span>';

        } else {

            echo '<span style=\"color:red;\"><strong>Not Optimized</strong></span>';
        }

        $frontend_analysis =
            get_transient(
                'velocipix_frontend_analysis'
            );

        $frontend_matches = [];

        if ( is_array( $frontend_analysis ) ) {

            foreach ( $frontend_analysis as $key => $analysis ) {

                $parts = explode( ':', $key );

                if (
                    count( $parts ) === 2
                    && intval( $parts[1] ) === $attachment_id
                ) {

                    $frontend_matches[] = $analysis;
                }
            }
        }

        echo '<div style="
            margin-top:8px;
        ">';

        echo '<strong>Frontend:</strong> ';

        if ( empty( $frontend_matches ) ) {

            echo '<span style="
                color:#996800;
                font-weight:600;
            ">
                Pending
            </span>';

        }

        else {

            $highest_severity = 'optimized';

            foreach ( $frontend_matches as $analysis ) {

                $ratio = floatval(
                    $analysis['ratio'] ?? 1
                );

                if ( $ratio >= 4 ) {

                    $highest_severity = 'severe';
                    break;

                }

                if (
                    $ratio >= 1.5
                    && $highest_severity !== 'severe'
                ) {

                    $highest_severity = 'moderate';
                }
            }

            switch ( $highest_severity ) {

                case 'severe':

                    echo '<span style="
                        color:#d63638;
                        font-weight:600;
                    ">
                        Severe
                    </span>';

                    break;

                case 'moderate':

                    echo '<span style="
                        color:#dba617;
                        font-weight:600;
                    ">
                        Moderate
                    </span>';

                    break;

                default:

                    echo '<span style="
                        color:#256029;
                        font-weight:600;
                    ">
                        ✓ Optimized
                    </span>';

                    break;
            }
        }

        echo '</div>';
    }
}