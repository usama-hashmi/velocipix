<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VelociPix_Elementor_Integration {

    /**
     * Image optimizer.
     *
     * @var VelociPix_Image_Optimizer
     */
    private $optimizer;

    public function __construct(
        VelociPix_Image_Optimizer $optimizer
    ) {

        $this->optimizer = $optimizer;

        add_action(
            'elementor/element/parse_css',
            [ $this, 'replace_background_images' ],
            10,
            2
        );

    }

    public function replace_background_images( $post_css, $element ) {

        $settings = $element->get_settings();

        if ( empty( $settings['background_image']['id'] ) ) {
            return;
        }

        $attachment_id = (int) $settings['background_image']['id'];

        $optimized_url = $this->optimizer
            ->get_best_modern_image_by_attachment(
                $attachment_id
            );

        if ( ! $optimized_url ) {
            return;
        }

        $stylesheet = $post_css->get_stylesheet();

        $rules = $stylesheet->get_rules();

        if ( empty( $rules['all'] ) ) {
                return;
            }

        foreach ( $rules['all'] as $selector => $properties ) {
            
            if ( empty( $properties['background-image'] ) ) {
                continue;
            }
            $unique_selector =
                $post_css->get_element_unique_selector(
                    $element
                );

            if ( strpos( $selector, $unique_selector ) === false ) {
                continue;
            }
            $stylesheet->add_rules(

                $selector,

                [
                    'background-image' =>
                        'url("' . esc_url_raw( $optimized_url ) . '")',
                ]

            );

            break;
        }

        // Don't modify anything yet.
        // Just inspect the array structure.

        error_log(
            print_r( $rules, true )
        );
    }

}