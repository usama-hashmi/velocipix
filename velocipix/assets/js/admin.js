jQuery(document).ready(function ($) {

	let currentPage = 1;

	let totalOptimized = 0;

	let totalSkipped = 0;

	let processing = false;

	$('#velocipix-start-bulk-optimization').on(
		'click',
		function () {

			if (processing) {
				return;
			}

			processing = true;

			$('#velocipix-progress-bar-wrapper').show();

			$('#velocipix-bulk-optimization-status').html(
				'<p>Optimization in progress...</p>'
			);

			processBatch();
		}
	);

	function processBatch() {

		$.post(
			ajaxurl,
			{
				action: 'velocipix_bulk_optimize',
				nonce: velocipix_bulk_optimizer.nonce,
				page: currentPage
			},
			function (response) {

				if (!response.success) {

					$('#velocipix-bulk-optimization-status').append(
						'<p>Optimization failed.</p>'
					);

					processing = false;

					return;
				}

				let data = response.data;

				let progress = Math.round(
					(data.page / data.max_pages) * 100
				);

				$('#velocipix-progress-bar').css(
					'width',
					progress + '%'
				);

				let totalImages = data.found;

				totalOptimized += data.optimized_count;

				totalSkipped += data.skipped_count;

				let processedSoFar =
					totalOptimized +
					totalSkipped;

				let remaining = Math.max(
					0,
					totalImages - processedSoFar
				);

				if ( data.page >= data.max_pages ) {

					remaining = 0;
				}

				$('#velocipix-bulk-optimization-status').html(

					'<p>' +

					'Processed batch ' +
					data.page +
					' of ' +
					data.max_pages +

					'<br><br>' +

					'Optimized: ' +
					totalOptimized +

					'<br>' +

					'Skipped: ' +
					totalSkipped +

					'<br><br>' +

					'Total Images: ' +
					totalImages +

					'<br>' +

					'Remaining: ' +
					remaining +

					'<br><br>' +

					'Progress: ' +
					progress +
					'%' +

					'</p>'
				);

				if (data.page < data.max_pages) {

					currentPage++;

					processBatch();

				} else {

					$('#velocipix-bulk-optimization-status').append(
						'<p><strong>Optimization complete.</strong></p>'
					);

					processing = false;
				}
			}
		);
	}
});