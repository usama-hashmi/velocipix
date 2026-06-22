<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SMP_Bulk_Optimizer {

	const BATCH_SIZE = 3;

	public function __construct() {

		add_action(
			'wp_ajax_smp_bulk_optimize',
			[ $this, 'process_bulk_optimization' ]
		);
	}

	public function process_bulk_optimization() {

		check_ajax_referer(
			'smp_bulk_optimizer',
			'nonce'
		);

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error(
				'Unauthorized'
			);
		}

		// Prevent concurrent runs via a short-lived lock.
		if ( get_transient( 'smp_bulk_running' ) ) {

			wp_send_json_error(
				'A bulk optimization is already in progress. Please wait and try again.'
			);
		}

		set_transient( 'smp_bulk_running', 1, 30 );

		$page = max( 1, (int) ( $_POST['page'] ?? 1 ) );

		$per_page = (int) apply_filters( 'smp_bulk_batch_size', self::BATCH_SIZE );

		$query = new WP_Query(
			[
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'image',
				'posts_per_page' => $per_page,
				'paged'          => $page,
				'fields'         => 'ids',
			]
		);

		$processed = [];

		$optimized_count = 0;

		$skipped_count = 0;

		if ( ! empty( $query->posts ) ) {

			$image_optimizer = new SMP_Image_Optimizer();

			foreach ( $query->posts as $attachment_id ) {

				$file_path = get_attached_file(
					$attachment_id
				);

				if ( empty( $file_path ) ) {
					$processed[] = $attachment_id;
					continue;
				}

				$file_info = pathinfo( $file_path );

				$base_dir  = $file_info['dirname'];

				$base_path =
					$base_dir .
					'/' .
					$file_info['filename'];

				$webp_before = file_exists(
					$base_path . '.webp'
				);

				$avif_before = file_exists(
					$base_path . '.avif'
				);

				$result = $image_optimizer->generate_modern_formats(
					$file_path
				);

				if ( is_wp_error( $result ) ) {
					// Log the error but continue processing remaining images.
					error_log(
						sprintf(
							'SMP Bulk Optimizer: failed to process attachment %d — %s',
							$attachment_id,
							$result->get_error_message()
						)
					);

					$processed[] = $attachment_id;
					continue;
				}

				$new_webp_generated =
					! $webp_before &&
					file_exists( $base_path . '.webp' );

				$new_avif_generated =
					! $avif_before &&
					file_exists( $base_path . '.avif' );

				if (
					$new_webp_generated ||
					$new_avif_generated
				) {

					$optimized_count++;

				} else {

					$skipped_count++;
				}

				$metadata = wp_get_attachment_metadata(
					$attachment_id
				);

				if ( ! empty( $metadata['sizes'] ) ) {

					foreach (
						$metadata['sizes']
						as $size
					) {

						if ( empty( $size['file'] ) ) {
							continue;
						}

						$thumbnail_path =
							$base_dir .
							'/' .
							$size['file'];

						$image_optimizer->generate_modern_formats(
							$thumbnail_path
						);
					}
				}

				$processed[] = $attachment_id;
			}
		}

		delete_transient( 'smp_bulk_running' );

		wp_send_json_success(
			[
				'processed'       => $processed,
				'page'            => $page,
				'found'           => $query->found_posts,
				'max_pages'       => $query->max_num_pages,
				'optimized_count' => $optimized_count,
				'skipped_count'   => $skipped_count,
			]
		);
	}
}