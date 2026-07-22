<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VelociPix_Image_Optimizer {

	private $output_buffer_started = false;

    public function __construct() {
        add_filter(
            'wp_generate_attachment_metadata',
            [ $this, 'process_attachment_images' ],
            10,
            2
        );

		add_action(
			'template_redirect',
			[ $this, 'start_output_buffer' ]
		);
		add_action(
			'shutdown',
			[ $this, 'end_output_buffer' ]
		);
		
		add_action(
			'delete_attachment',
			[ $this, 'cleanup_attachment_formats' ]
		);
    }

    public function process_attachment_images( $metadata, $attachment_id ) {

		$upload_dir = wp_upload_dir();

		$original_file = get_attached_file( $attachment_id );

		$this->generate_modern_formats( $original_file );

		if ( ! empty( $metadata['sizes'] ) ) {

			$base_dir = dirname( $original_file );

			foreach ( $metadata['sizes'] as $size ) {

				if ( empty( $size['file'] ) ) {
					continue;
				}

				$thumbnail_path = $base_dir . '/' . $size['file'];

				$this->generate_modern_formats( $thumbnail_path );
			}
		}

		return $metadata;
	}

	private function get_settings() {

		return wp_parse_args(

			get_option( 'velocipix_settings', [] ),

			[
				'enable_webp'                => 1,
				'enable_avif'                => 1,
				'webp_quality'               => 80,
				'avif_quality'               => 60,
				'enable_frontend_replacement' => 1,
			]
		);
	}

    private function get_best_modern_image_url( $image_url ) {

		$upload_dir = wp_upload_dir();

		$baseurl = $upload_dir['baseurl'];
		$basedir = $upload_dir['basedir'];

		$file_path = str_replace( $baseurl, $basedir, $image_url );

		$avif_path = preg_replace(
			'/\\.(jpg|jpeg|png)$/i',
			'.avif',
			$file_path
		);

		$webp_path = preg_replace(
			'/\\.(jpg|jpeg|png)$/i',
			'.webp',
			$file_path
		);

		// Prefer AVIF
		if ( file_exists( $avif_path ) ) {

			return preg_replace(
				'/\\.(jpg|jpeg|png)$/i',
				'.avif',
				$image_url
			);
		}

		// Fallback to WebP
		if ( file_exists( $webp_path ) ) {

			return preg_replace(
				'/\\.(jpg|jpeg|png)$/i',
				'.webp',
				$image_url
			);
		}

		// Fallback to original
		return $image_url;
	}

    public function get_best_modern_image_by_attachment( int $attachment_id ) {

        $image_url = wp_get_attachment_url(
            $attachment_id
        );

        if ( ! $image_url ) {
            return false;
        }

        return $this->get_best_modern_image_url(
            $image_url
        );

    }

	public function generate_modern_formats( $file_path ) {

		$settings = $this->get_settings();

		if ( ! empty( $settings['enable_webp'] ) ) {

			$this->generate_webp( $file_path );
		}

		if ( ! empty( $settings['enable_avif'] ) ) {

			$this->generate_avif( $file_path );
		}
	}

	private function generate_webp( $file_path ) {

		if ( ! file_exists( $file_path ) ) {
			return;
		}

		$file_info = pathinfo( $file_path );

		if ( empty( $file_info['extension'] ) ) {
			return;
		}

		$extension = strtolower( $file_info['extension'] );

		if ( ! in_array( $extension, [ 'jpg', 'jpeg', 'png' ], true ) ) {
			return;
		}

		$webp_path = $file_info['dirname'] . '/' . $file_info['filename'] . '.webp';

		if ( file_exists( $webp_path ) ) {
			return;
		}

		// Imagick
		if ( class_exists( 'Imagick' ) ) {

			try {

				$image = new Imagick( $file_path );

				if ( $extension === 'png' ) {
					$image->setImageBackgroundColor( 'transparent' );
				}

				$image->setImageFormat( 'webp' );
				$settings = $this->get_settings();

				$image->setImageCompressionQuality(
					(int) $settings['webp_quality']
				);

				$image->writeImage( $webp_path );

				$image->clear();
				$image->destroy();

			} catch ( Exception $e ) {

				return;
			}

		} else {

			// GD fallback
			if ( $extension === 'jpg' || $extension === 'jpeg' ) {

				$image = imagecreatefromjpeg( $file_path );

			} elseif ( $extension === 'png' ) {

				$image = imagecreatefrompng( $file_path );

			} else {

				return;
			}

			if ( $image ) {

				$settings = $this->get_settings();

				imagewebp(
					$image,
					$webp_path,
					(int) $settings['webp_quality']
				);

				imagedestroy( $image );
			}
		}
	}
	private function generate_avif( $file_path ) {

		if ( ! file_exists( $file_path ) ) {
			return;
		}

		$file_info = pathinfo( $file_path );

		if ( empty( $file_info['extension'] ) ) {
			return;
		}

		$extension = strtolower( $file_info['extension'] );

		if ( ! in_array( $extension, [ 'jpg', 'jpeg', 'png' ], true ) ) {
			return;
		}

		$avif_path = $file_info['dirname'] . '/' . $file_info['filename'] . '.avif';

		if ( file_exists( $avif_path ) ) {
			return;
		}

		// Imagick
		if ( class_exists( 'Imagick' ) ) {

			try {

				$image = new Imagick( $file_path );

				if ( $extension === 'png' ) {
					$image->setImageBackgroundColor( 'transparent' );
				}

				$image->setImageFormat( 'avif' );
				$settings = $this->get_settings();

				$image->setImageCompressionQuality(
					(int) $settings['avif_quality']
				);

				$image->writeImage( $avif_path );

				$image->clear();
				$image->destroy();

			} catch ( Exception $e ) {

				return;
			}

		} else {

			// GD fallback
			if ( function_exists( 'imageavif' ) ) {

				if ( $extension === 'jpg' || $extension === 'jpeg' ) {

					$image = imagecreatefromjpeg( $file_path );

				} elseif ( $extension === 'png' ) {

					$image = imagecreatefrompng( $file_path );

				} else {

					return;
				}

				if ( $image ) {

					$settings = $this->get_settings();

					imageavif(
						$image,
						$avif_path,
						(int) $settings['avif_quality']
					);

					imagedestroy( $image );
				}
			}
		}
	}

	public function get_image_optimization_data( $attachment_id ) {

		$file_path = get_attached_file(
			$attachment_id
		);

		if (
			empty( $file_path ) ||
			! file_exists( $file_path )
		) {

			return false;
		}

		$file_info = pathinfo( $file_path );

		$base_path =
			$file_info['dirname'] .
			'/' .
			$file_info['filename'];

		$webp_path = $base_path . '.webp';

		$avif_path = $base_path . '.avif';

		$original_size = filesize(
			$file_path
		);

		$webp_size = file_exists( $webp_path )
			? filesize( $webp_path )
			: 0;

		$avif_size = file_exists( $avif_path )
			? filesize( $avif_path )
			: 0;

		$best_size = $original_size;

		if (
			$webp_size > 0 &&
			$webp_size < $best_size
		) {

			$best_size = $webp_size;
		}

		if (
			$avif_size > 0 &&
			$avif_size < $best_size
		) {

			$best_size = $avif_size;
		}

		$savings = 0;

		if ( $original_size > 0 ) {

			$savings =
				100 -
				round(
					( $best_size / $original_size ) * 100
				);
		}

		return [

			'original_size' => $original_size,

			'webp_size' => $webp_size,

			'avif_size' => $avif_size,

			'savings_percent' => $savings,

			'has_webp' => file_exists(
				$webp_path
			),

			'has_avif' => file_exists(
				$avif_path
			),
		];
	}

	public function serve_webp_images( $url, $attachment_id ) {

		$file_path = get_attached_file( $attachment_id );

			if ( ! file_exists( $file_path ) ) {
				return $url;
			}

		$file_info = pathinfo( $file_path );

		$extension = strtolower( $file_info['extension'] );

			if ( ! in_array( $extension, [ 'jpg', 'jpeg', 'png' ], true ) ) {
				return $url;
			}

			$webp_url = str_replace(
				'.' . $extension,
				'.webp',
				$url
			);

			$webp_path = str_replace(
				'.' . $extension,
				'.webp',
				$file_path
			);

			if ( file_exists( $webp_path ) ) {
				return $webp_url;
			}

		return $url;
	}
	public function serve_webp_image_src( $image, $attachment_id, $size, $icon ) {

		if ( empty( $image[0] ) ) {
			return $image;
		}

		$image_url = $image[0];

		$upload_dir = wp_upload_dir();

		$baseurl = $upload_dir['baseurl'];
		$basedir = $upload_dir['basedir'];

		$file_path = str_replace( $baseurl, $basedir, $image_url );

		$file_info = pathinfo( $file_path );

		if ( empty( $file_info['extension'] ) ) {
			return $image;
		}

		$extension = strtolower( $file_info['extension'] );

		if ( ! in_array( $extension, [ 'jpg', 'jpeg', 'png' ], true ) ) {
			return $image;
		}

		$webp_path = str_replace(
			'.' . $extension,
			'.webp',
			$file_path
		);

		$webp_url = str_replace(
			'.' . $extension,
			'.webp',
			$image_url
		);

		if ( file_exists( $webp_path ) ) {
			$image[0] = $webp_url;
		}

		return $image;
	}

	public function start_output_buffer() {

		$settings = $this->get_settings();

		if ( empty( $settings['enable_frontend_replacement'] ) ) {
			return;
		}

		$this->output_buffer_started = true;

		ob_start(
			[ $this, 'replace_image_urls_with_webp' ]
		);
	}

	public function end_output_buffer() {

		if (
			$this->output_buffer_started
			&& ob_get_level() > 0
		) {

			ob_end_flush();

			$this->output_buffer_started = false;
		}
	}

	public function replace_image_urls_with_webp( $html ) {

		$upload_dir = wp_upload_dir();

		$baseurl = $upload_dir['baseurl'];
		$basedir = $upload_dir['basedir'];

		preg_match_all(
			'/' . preg_quote( $baseurl, '/' ) . '.*?\.(jpg|jpeg|png)/i',
			$html,
			$matches
		);

		if ( empty( $matches[0] ) ) {
			return $html;
		}

		foreach ( $matches[0] as $image_url ) {

			$file_path = str_replace( $baseurl, $basedir, $image_url );

			$modern_url = $this->get_best_modern_image_url( $image_url );

			if ( $modern_url !== $image_url ) {

				$html = str_replace(
					$image_url,
					$modern_url,
					$html
				);
			}
		}

		return $html;
	}

	public function cleanup_attachment_formats(
		$attachment_id
	) {

		$original_file =
			get_attached_file(
				$attachment_id
			);

		if (
			empty( $original_file )
			||
			! file_exists( $original_file )
		) {

			return;
		}

		$this->delete_generated_formats(
			$original_file
		);

		$metadata =
			wp_get_attachment_metadata(
				$attachment_id
			);

		if (
			empty( $metadata['sizes'] )
			||
			! is_array(
				$metadata['sizes']
			)
		) {

			return;
		}

		$base_dir =
			dirname(
				$original_file
			);

		foreach (
			$metadata['sizes']
			as
			$size
		) {

			if (
				empty(
					$size['file']
				)
			) {

				continue;
			}

			$thumbnail_path =
				$base_dir
				. '/'
				. $size['file'];

			$this->delete_generated_formats(
				$thumbnail_path
			);
		}
	}

	private function delete_generated_formats(
		$file_path
	) {

		$file_info =
			pathinfo(
				$file_path
			);

		$base_path =
			$file_info['dirname']
			. '/'
			. $file_info['filename'];

		$webp_path =
			$base_path . '.webp';

		$avif_path =
			$base_path . '.avif';

		if (
			file_exists(
				$webp_path
			)
		) {

			wp_delete_file(
				$webp_path
			);
		}

		if (
			file_exists(
				$avif_path
			)
		) {

			wp_delete_file(
				$avif_path
			);
		}
	}
}