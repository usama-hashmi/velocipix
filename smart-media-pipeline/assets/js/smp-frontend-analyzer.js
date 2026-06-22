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

			if ( smpFrontend.debug ) {

                console.log(
                    '[SMP Oversized Image]',

                    {
                        id:
                            imageId,

                        src:
                            img.src,

                        renderedWidth:
                            renderedWidth,

                        naturalWidth:
                            naturalWidth,

                        ratio:
                            ratio.toFixed( 2 ),
                    }
			    );
            }

            fetch(

                smpFrontend.ajaxUrl,

                {
                    method: 'POST',

                    headers: {
                        'Content-Type':
                            'application/x-www-form-urlencoded',
                    },

                    body:

                        new URLSearchParams({

                            action:
                                'smp_frontend_analysis',

                            nonce:
                                smpFrontend.nonce,

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

                    if ( smpFrontend.debug ) {

                        console.log(
                            '[SMP AJAX]',
                            data
                        );
                    }
                }
            );
		} );
	}
);