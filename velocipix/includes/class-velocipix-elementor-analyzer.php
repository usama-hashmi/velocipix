<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VelociPix_Elementor_Analyzer {

	public function get_elementor_image_ids( $post_id ) {

		$elementor_data = get_post_meta(
			$post_id,
			'_elementor_data',
			true
		);

		if ( empty( $elementor_data ) ) {
			return [];
		}

		$data = json_decode(
			$elementor_data,
			true
		);

		if ( empty( $data ) ) {
			return [];
		}

		$image_ids = [];

		$this->extract_image_ids(
			$data,
			$image_ids
		);

		return array_unique(
			$image_ids
		);
	}

    public function get_elementor_image_data( $post_id ) {

        $elementor_data = get_post_meta(
            $post_id,
            '_elementor_data',
            true
        );

        if ( empty( $elementor_data ) ) {
            return [];
        }

        $data = json_decode(
            $elementor_data,
            true
        );

        if ( empty( $data ) ) {
            return [];
        }

        $images = [];

        $this->extract_image_data(
            $data,
            $images,
            $post_id
        );

        return $images;
    }

	private function extract_image_ids(
		$elements,
		&$image_ids
	) {

		if ( empty( $elements ) ) {
			return;
		}

		foreach ( $elements as $element ) {

			// Image widget
			if ( ! empty( $element['widgetType'] ) ) {

                // Classic Elementor Image Widget
                if ( 'image' === $element['widgetType'] ) {
                    
                    $image_id =
                        $element['settings']['image']['id']
                        ?? 0;

                    if ( ! empty( $image_id ) ) {

                        $image_ids[] = (int) $image_id;
                    }
                }

                // Elementor Atomic Image Widget
                if ( 'e-image' === $element['widgetType'] ) {

                    $image_id =
                        $element['settings']['image']['value']['src']['value']['id']['value']
                        ?? 0;

                    if ( ! empty( $image_id ) ) {

                        $image_ids[] = (int) $image_id;
                    }
                }
            }

			// Nested elements
			if ( ! empty( $element['elements'] ) ) {

				$this->extract_image_ids(
					$element['elements'],
					$image_ids
				);
			}
		}
	}

    private function extract_image_data(
        $elements,
        &$images,
        $post_id
    ) {

        if ( empty( $elements ) ) {
            return;
        }

        foreach ( $elements as $element ) {

            if ( empty( $element['widgetType'] ) ) {

                if ( ! empty( $element['elements'] ) ) {

                    $this->extract_image_data(
                        $element['elements'],
                        $images,
                        $post_id
                    );
                }

                continue;
            }

            $image_id = 0;

            $image_size = 'full';

            // Classic Elementor Image Widget
            if ( 'image' === $element['widgetType'] ) {
                
                $image_id =
                    $element['settings']['image']['id']
                    ?? 0;

                $image_size =
                    $element['settings']['image_size']
                    ?? 'full';
            }

            // Elementor Atomic Image Widget
            if ( 'e-image' === $element['widgetType'] ) {

                $image_id =
                    $element['settings']['image']['value']['src']['value']['id']['value']
                    ?? 0;

                $image_size =
                    $element['settings']['image']['value']['size']['value']
                    ?? 'full';
            }

            /*
            |--------------------------------------------------------------------------
            | Classic Container Background
            |--------------------------------------------------------------------------
            */

            if (

                empty( $image_id )

                &&

                ! empty(
                    $element['settings']['background_image']['id']
                )

            ) {

                $image_id =
                    $element['settings']['background_image']['id'];

                $image_size =
                    $element['settings']['background_image']['size']
                    ?? 'full';

                $element['widgetType'] =
                    'container-background';
            }

            /*
            |--------------------------------------------------------------------------
            | Classic Background Overlay
            |--------------------------------------------------------------------------
            */

            if (

                empty( $image_id )

                &&

                ! empty(
                    $element['settings']['background_overlay_image']['id']
                )

            ) {

                $image_id =
                    $element['settings']['background_overlay_image']['id'];

                $image_size =
                    $element['settings']['background_overlay_image']['size']
                    ?? 'full';

                $element['widgetType'] =
                    'background-overlay';
            }

            $recommended_width =
                $this->get_image_size_width(
                    $image_size
                );

            /*
            |--------------------------------------------------------------------------
            | Elementor Custom Image Size
            |--------------------------------------------------------------------------
            */

            if (
                'custom' === $image_size &&
                ! empty( $element['settings']['image_custom_dimension']['width'] )
            ) {

                $recommended_width = intval(
                    $element['settings']['image_custom_dimension']['width']
                );
            }

            if ( ! empty( $image_id ) ) {

                $metadata = wp_get_attachment_metadata(
                    $image_id
                );

                if (

                    empty( $metadata )

                    ||

                    ! is_array( $metadata )

                    ||

                    empty( $metadata['width'] )

                ) {

                    continue;
                }

                $frontend_analysis =
                    get_transient(
                        'velocipix_frontend_analysis'
                    );

                $key =

                    $post_id

                    . ':'

                    . $image_id;

                $frontend_data =

                    $frontend_analysis[
                        $key
                    ]

                    ?? [];

                /*
                |--------------------------------------------------------------------------
                | Elementor sizing analysis
                |--------------------------------------------------------------------------
                */

                $actual_width = $metadata['width'] ?? 0;

                $is_oversized = false;
                $severity     = 'optimized';

                if ( $actual_width > ( $recommended_width * 4 ) ) {

                    $is_oversized = true;
                    $severity     = 'high';

                } elseif ( $actual_width > ( $recommended_width * 2 ) ) {

                    $is_oversized = true;
                    $severity     = 'medium';

                }

                $images[] = [

                    'post_id' => $post_id,

                    'attachment_id' => $image_id,

                    'widget_type' =>
                        $element['widgetType'],

                    'image_size' =>
                        $image_size,

                    'recommended_width' =>
                        $recommended_width,
                    
                    'is_oversized' => $is_oversized,

                    'severity' => $severity,

                    'width' =>
                        $actual_width,

                    'height' =>
                        $metadata['height']
                        ?? 0,

                    'frontend_data' => $frontend_data,

                    'frontend_analyzed' =>
                        ! empty(
                            $frontend_data
                        ),

                    'insight' => '',

                    'fix_suggestion' =>
                        $this->generate_fix_suggestion(

                            $image_size,

                            $recommended_width,

                            $frontend_data
                        ),
                ];

                $last_index = array_key_last( $images );

                $images[ $last_index ]['insight'] =
                    $this->generate_insight_message(

                        $image_size,

                        $metadata['width'] ?? 0,

                        $recommended_width,

                        (
                            (
                                $metadata['width']
                                ?? 0
                            )

                            >

                            (
                                $recommended_width * 4
                            )
                        )

                        ? 'high'

                        : 'medium',

                        $frontend_data
                    );
            }

            if ( ! empty( $element['elements'] ) ) {

                $this->extract_image_data(
                    $element['elements'],
                    $images,
                    $post_id
                );
            }
        }
    }

    private function get_image_size_width( $image_size ) {

        $sizes = [

            'thumbnail'     => 150,
            'medium'        => 300,
            'medium_large'  => 768,
            'large'         => 1024,
            'full'          => 99999,

        ];

        if ( isset( $sizes[ $image_size ] ) ) {
            return $sizes[ $image_size ];
        }

        /*
        |--------------------------------------------------------------------------
        | Elementor custom dimensions (e.g. 1536x1536)
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/^(\d+)x(\d+)$/',
                $image_size,
                $matches
            )
        ) {

            return intval( $matches[1] );
        }

        return 99999;
    }

    private function generate_insight_message(

        $image_size,
        $actual_width,
        $recommended_width,
        $severity,
        $frontend_data = []

    ) {

        $ratio =

            floatval(
                $frontend_data['ratio']
                ?? 0
            );

        if ( $ratio > 1.5 ) {

            return
                'The browser downloaded more image data than necessary for this display size.';
        }

        return '';
    }

    private function generate_fix_suggestion(

        $image_size,
        $recommended_width,
        $frontend_data = []

    ) {

        $ratio =
            floatval(
                $frontend_data['ratio']
                ?? 1
            );

        /*
        |--------------------------------------------------------------------------
        | Severe frontend overfetch
        |--------------------------------------------------------------------------
        */

        if ( $ratio >= 4 ) {

            return
                'Consider reducing oversized responsive image variants or limiting large image candidates for this section.';
        }

        /*
        |--------------------------------------------------------------------------
        | Moderate frontend overfetch
        |--------------------------------------------------------------------------
        */

        if ( $ratio >= 1.5 ) {

            return
                'Review this section in Elementor and test smaller responsive image candidates if visual quality remains acceptable.';
        }

        /*
        |--------------------------------------------------------------------------
        | Tiny displayed elements
        |--------------------------------------------------------------------------
        */

        if (
            'thumbnail' === $image_size
        ) {

            return
                'Use appropriately sized source images for very small displayed elements where possible.';
        }

        /*
        |--------------------------------------------------------------------------
        | Default
        |--------------------------------------------------------------------------
        */

        return
            'No optimization changes currently recommended.';
    }

    // public function debug_test( $post_id ) {

    //     $analyzer = new VelociPix_Elementor_Analyzer();

    //     echo '<pre>';

    //     print_r(
    //         $analyzer->get_elementor_image_data(
    //             $post_id
    //         )
    //     );

    //     echo '</pre>';
    // }
}