<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VelociPix_Delivery_Engine {

    public function __construct() {

    }

    public function process_html( $html ) {

        $references = $this->extract_image_references( $html );

        return $html;

    }

    private function extract_image_references( $html ) {

        $references = [];

        libxml_use_internal_errors( true );

        $document = new DOMDocument();

        $document->loadHTML(
            mb_convert_encoding(
                $html,
                'HTML-ENTITIES',
                'UTF-8'
            )
        );

        libxml_clear_errors();

        $images = $document->getElementsByTagName( 'img' );

        foreach ( $images as $image ) {

            $references[] = [

                'type' => 'img',

                'src' => $image->getAttribute( 'src' ),

                'srcset' => $image->getAttribute( 'srcset' ),

                'attachment_id' => null,

                'optimized_url' => null,

                'element' => $image,

            ];

        }

        return $references;

    }

    private function resolve_references( array $references ) {

        return $references;

    }

    private function rewrite_html(
        $html,
        array $references
    ) {

        return $html;

    }

}