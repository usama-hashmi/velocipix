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

                            post_id: 
                                velocipixFrontend.post_id,

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
		});

        document
        .querySelectorAll('.elementor-element')
        .forEach(function (element) {

            const elementId =
                element.dataset.id;

            const attachmentId =
                velocipixFrontend
                    .backgroundAttachments[
                        elementId
                    ];

            if ( ! attachmentId ) {
                return;
            }

            let style =
                getComputedStyle(
                    element
                );

            let backgroundImage =
                style.backgroundImage;

            /*
            |--------------------------------------------------------------------------
            | Background Overlay (::before)
            |--------------------------------------------------------------------------
            */

            if (
                ! backgroundImage ||
                backgroundImage === 'none'
            ) {

                style =
                    getComputedStyle(
                        element,
                        '::before'
                    );

                backgroundImage =
                    style.backgroundImage;
            }

            /*
            |--------------------------------------------------------------------------
            | Background Overlay (::after)
            |--------------------------------------------------------------------------
            */

            if (
                ! backgroundImage ||
                backgroundImage === 'none'
            ) {

                style =
                    getComputedStyle(
                        element,
                        '::after'
                    );

                backgroundImage =
                    style.backgroundImage;
            }

            if (
                ! backgroundImage ||
                backgroundImage === 'none'
            ) {
                return;
            }

            const url =
                backgroundImage
                    .replace(
                        /^url\(["']?/,
                        ''
                    )
                    .replace(
                        /["']?\)$/,
                        ''
                    );

            const image =
                new Image();

            const analyzeBackground =
                function () {

                    const renderedWidth =
                        element.clientWidth;

                    const naturalWidth =
                        image.naturalWidth;

                    if (
                        ! renderedWidth ||
                        ! naturalWidth
                    ) {
                        return;
                    }

                    const ratio = Number(

                        (

                            naturalWidth /

                            renderedWidth

                        ).toFixed( 2 )

                    );

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
                                        attachmentId,

                                    post_id:
                                        velocipixFrontend.post_id,

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

                    );

                };

            image.onload =
                analyzeBackground;

            image.src =
                url;

            /*
            |--------------------------------------------------------------------------
            | Cached images
            |--------------------------------------------------------------------------
            */

            if ( image.complete ) {

                analyzeBackground();

            }

        });
	}
);