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

    private function replace_background_setting(
        $post_css,
        $element,
        $setting_key,
        $target_query = 'all'
    ) {

        $settings = $element->get_settings();

        if (
            empty( $settings[ $setting_key ]['id'] )
        ) {
            return;
        }

        $plugin_settings = get_option(
            'velocipix_settings',
            []
        );

        if (
            empty(
                $plugin_settings['enable_frontend_replacement']
            )
        ) {
            return;
        }

        $attachment_id = (int)
            $settings[ $setting_key ]['id'];

        $optimized_url =
            $this->optimizer
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

        $unique_selector =
            $post_css->get_element_unique_selector(
                $element
            );

        foreach ( $rules as $query_hash => $selectors ) {

            if ( $query_hash !== $target_query ) {
                continue;
            }

            foreach (
                $selectors as $selector => $properties
            ) {

                if (
                    strpos(
                        $selector,
                        $unique_selector
                    ) === false
                ) {
                    continue;
                }

                if (
                    empty(
                        $properties['background-image']
                    )
                ) {
                    continue;
                }

                $properties['background-image'] =
                    'url("' .
                    esc_url_raw( $optimized_url ) .
                    '")';

                $stylesheet->add_rules(

                    $selector,

                    $properties,

                    'all' === $query_hash
                        ? null
                        : [ 'max' => str_replace( 'max_', '', $query_hash ) ]

                );

                break;
            }

            break;
        }
    }

    public function replace_background_images( $post_css, $element ) {
        
        $this->replace_background_setting(
            $post_css,
            $element,
            'background_image',
            'all'
        );

        $this->replace_background_setting(
            $post_css,
            $element,
            'background_image_tablet',
            'max_tablet'
        );

        $this->replace_background_setting(
            $post_css,
            $element,
            'background_image_mobile',
            'max_mobile'
        );

        $this->replace_background_setting(
            $post_css,
            $element,
            'background_overlay_image',
            'all'
        );

        $this->replace_background_setting(
            $post_css,
            $element,
            'background_overlay_image_tablet',
            'max_tablet'
        );

        $this->replace_background_setting(
            $post_css,
            $element,
            'background_overlay_image_mobile',
            'max_mobile'
        );
    }

}