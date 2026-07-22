<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VelociPix_Image_Resolver {

    protected $resolver;

    public function __construct() {

        $this->resolver =
            new VelociPix_Image_Resolver();

    }

    /**
     * Resolve an image URL to a WordPress attachment ID.
     *
     * @param string $url
     * @return int|null
     */
    public function resolve_attachment_id( string $url ) {

        return null;

    }

    /**
     * Return the best image URL for an attachment.
     *
     * @param int $attachment_id
     * @return string|null
     */
    public function get_best_delivery_url( int $attachment_id ) {

        return null;

    }

}