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

        $unique_images = [];
        $seen_images   = [];

        foreach ( $images as $image ) {

            $key =
                ( $image['post_id'] ?? $post_id )
                . ':'
                . ( $image['attachment_id'] ?? 0 )
                . ':'
                . ( $image['image_size'] ?? 'full' );

            if ( isset( $seen_images[ $key ] ) ) {
                continue;
            }

            $seen_images[ $key ] = true;

            $unique_images[] = $image;
        }

        return $unique_images;
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

    public function get_background_attachment_map( $post_id ) {

        $map = [];

        $document = \Elementor\Plugin::$instance
            ->documents
            ->get( $post_id );

        if ( ! $document ) {
            return $map;
        }

        $elements = $document->get_elements_data();

        $this->extract_background_selectors(
            $elements,
            $map
        );

        return $map;
    }

    private function extract_background_selectors(
        $elements,
        &$map
    ) {

        if ( empty( $elements ) ) {
            return;
        }

        foreach ( $elements as $element ) {

            if (
                empty( $element['id'] )
            ) {

                if ( ! empty( $element['elements'] ) ) {

                    $this->extract_background_selectors(
                        $element['elements'],
                        $map
                    );

                }

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Desktop Background
            |--------------------------------------------------------------------------
            */

            if (
                ! empty(
                    $element['settings']['background_image']['id']
                )
            ) {

                $map[
                    $element['id']
                ] = (int)
                    $element['settings']['background_image']['id'];

            }

            /*
            |--------------------------------------------------------------------------
            | Tablet Background
            |--------------------------------------------------------------------------
            */

            if (
                ! empty(
                    $element['settings']['background_image_tablet']['id']
                )
            ) {

                $map[
                    $element['id']
                ] = (int)
                    $element['settings']['background_image_tablet']['id'];

            }

            /*
            |--------------------------------------------------------------------------
            | Mobile Background
            |--------------------------------------------------------------------------
            */

            if (
                ! empty(
                    $element['settings']['background_image_mobile']['id']
                )
            ) {

                $map[
                    $element['id']
                ] = (int)
                    $element['settings']['background_image_mobile']['id'];

            }

            /*
            |--------------------------------------------------------------------------
            | Overlay
            |--------------------------------------------------------------------------
            */

            if (
                ! empty(
                    $element['settings']['background_overlay_image']['id']
                )
            ) {

                $map[
                    $element['id']
                ] = (int)
                    $element['settings']['background_overlay_image']['id'];

            }

            if ( ! empty( $element['elements'] ) ) {

                $this->extract_background_selectors(
                    $element['elements'],
                    $map
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

                $has_background =

                    ! empty(
                        $element['settings']['background_image']['id']
                    )

                    ||

                    ! empty(
                        $element['settings']['background_overlay_image']['id']
                    );

                if ( ! $has_background ) {

                    if ( ! empty( $element['elements'] ) ) {

                        $this->extract_image_data(
                            $element['elements'],
                            $images,
                            $post_id
                        );
                    }

                    continue;

                }

            }

            $image_id = 0;

            $image_size = 'full';

            $additional_image_ids = [];

            // Classic Elementor Image Widget
            if (
                isset( $element['widgetType'] )
                &&
                'image' === $element['widgetType']
            ) {

                $image_id =
                    $element['settings']['image']['id']
                    ?? 0;

                $image_size =
                    $element['settings']['image_size']
                    ?? 'full';
            }

            // Elementor Atomic Image Widget
            if (
                isset( $element['widgetType'] )
                &&
                'e-image' === $element['widgetType']
            ) {

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
                    'Container Background';
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
                    'Background Overlay';
            }

            $recommended_width =
                $this->get_image_size_width(
                    $image_size
                );

            /*
            |--------------------------------------------------------------------------
            | Theme Widget Nested Images
            |--------------------------------------------------------------------------
            |
            | Some Elementor widgets supplied by themes store their images inside
            | nested widget settings instead of Elementor's standard image fields.
            |
            */

            if (
                ! empty( $element['settings']['slides'] )
                &&
                is_array( $element['settings']['slides'] )
            ) {

                foreach (
                    $element['settings']['slides'] as $slide
                ) {

                    if (
                        ! empty(
                            $slide['bg_image']['id']
                        )
                    ) {

                        $additional_image_ids[] = [
                            'id' =>
                                (int) $slide['bg_image']['id'],

                            'size' =>
                                $slide['bg_image']['size']
                                ?? 'full',

                            'type' =>
                                'Theme Widget Background',
                        ];

                    }

                    if (
                        ! empty(
                            $slide['image']['id']
                        )
                    ) {

                        $additional_image_ids[] = [
                            'id' =>
                                (int) $slide['image']['id'],

                            'size' =>
                                $slide['image']['size']
                                ?? 'full',

                            'type' =>
                                'Theme Widget Image',
                        ];

                    }

                }

            }

            /*
            |--------------------------------------------------------------------------
            | Theme Widget Top-Level Images
            |--------------------------------------------------------------------------
            */

            if (
                ! in_array(
                    $element['widgetType'] ?? '',
                    [
                        'image',
                        'e-image',
                    ],
                    true
                )
                &&
                ! empty(
                    $element['settings']['image']['id']
                )
            ) {

                $additional_image_ids[] = [
                    'id' =>
                        (int)
                        $element['settings']['image']['id'],

                    'size' =>
                        $element['settings']['image']['size']
                        ?? 'full',

                    'type' =>
                        'Theme Widget Image',
                ];

            }

            if (
                ! empty(
                    $element['settings']['image2']['id']
                )
            ) {

                $additional_image_ids[] = [
                    'id' =>
                        (int)
                        $element['settings']['image2']['id'],

                    'size' =>
                        $element['settings']['image2']['size']
                        ?? 'full',

                    'type' =>
                        'Theme Widget Image',
                ];

            }

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

            $image_entries = [];

            if ( ! empty( $image_id ) ) {

                $image_entries[] = [
                    'id'   => (int) $image_id,
                    'size' => $image_size,
                    'type' => $element['widgetType'],
                ];

            }

            $image_entries = array_merge(
                $image_entries,
                $additional_image_ids
            );

            foreach ( $image_entries as $image_entry ) {

                $image_id =
                    $image_entry['id'];

                $image_size =
                    $image_entry['size'];

                if ( ! $image_id ) {
                    continue;
                }

                $recommended_width =
                    $this->get_image_size_width(
                        $image_size
                    );

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
                        $image_entry['type'],

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
            }

            if ( ! empty( $element['elements'] ) ) {

                $this->extract_image_data(
                    $element['elements'],
                    $images,
                    $post_id
                );
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

}