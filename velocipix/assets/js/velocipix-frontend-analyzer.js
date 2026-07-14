window.addEventListener(
	'load',
	function () {

		const images =
			document.querySelectorAll( 'img' );

		images.forEach( function ( img ) {

			const renderedWidth =
				img.clientWidth;

			const naturalWidth =
				img.naturalWidth;

			if (
				! renderedWidth ||
				! naturalWidth
			) {
				return;
			}

			const ratio = Number(
                (
                    naturalWidth / renderedWidth
                ).toFixed( 2 )
            );

            const imageId =

                img.getAttribute( 'id' )

                ||

                (
                    (
                        img.getAttribute( 'class' )
                        || ''
                    ).match(
                        /wp-image-(\d+)/
                    )
                    || []
                )[1]

                || '';

			if ( velocipixFrontend.debug && ratio >= 1.5 ) {

                const severity = ratio >= 4 ? 'High' : 'Medium';

                console.warn(
                    `[VelociPix Oversized Image - ${severity}]`,
                    {
                        id: imageId,
                        src: img.src,
                        renderedWidth,
                        naturalWidth,
                        ratio: ratio.toFixed( 2 ),
                    }
                );
            }

            fetch(

                velocipixFrontend.ajaxUrl,

                {
                    method: 'POST',

                    headers: {
                        'Content-Type':
                            'application/x-www-form-urlencoded',
                    },

                    body:

                        new URLSearchParams({

                            action:
                                'velocipix_frontend_analysis',

                            nonce:
                                velocipixFrontend.nonce,

                            attachment_id:
                                imageId,

                            rendered_width:
                                renderedWidth,

                            natural_width:
                                naturalWidth,

                            ratio:
                                ratio,

                            page_url:
                                window.location.href,
                        }),
                }
            )
            .then(
                response => response.json()
            )
            .then(

                data => {

                    if ( velocipixFrontend.debug ) {

                        console.log(
                            '[VelociPix AJAX]',
                            data
                        );
                    }
                }
            );
		} );
	}
);