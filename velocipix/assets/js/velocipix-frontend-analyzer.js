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

                            image_url:
                                img.currentSrc || img.src,

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

                const mappedAttachmentId =
                    velocipixFrontend
                        .backgroundAttachments[
                            elementId
                        ] || 0;

                /*
                |--------------------------------------------------------------------------
                | Existing Elementor background handling
                |--------------------------------------------------------------------------
                */

                if ( mappedAttachmentId ) {

                    analyzeBackgroundElement(
                        element,
                        mappedAttachmentId
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Rendered background fallback
                |--------------------------------------------------------------------------
                |
                | Some themes create background-image elements directly in their
                | rendered HTML instead of storing the image in Elementor's
                | background_image settings.
                |
                | Example:
                |
                | <div class="img-bg"
                |      style="background-image:url(...)">
                |
                | Search the Elementor element and its descendants so these
                | rendered backgrounds can also be analyzed.
                |
                */

                const backgroundElements = [
                    element
                ].concat(
                    Array.from(
                        element.querySelectorAll('*')
                    )
                );

                backgroundElements.forEach(
                    function ( backgroundElement ) {

                        analyzeBackgroundElement(
                            backgroundElement,
                            0
                        );

                    }
                );

            });

        function analyzeBackgroundElement(
            element,
            attachmentId
        ) {

            const backgroundImages = [];

            let style =
                getComputedStyle(
                    element
                );

            let backgroundImage =
                style.backgroundImage;

            if (
                backgroundImage &&
                backgroundImage !== 'none'
            ) {

                backgroundImages.push(
                    backgroundImage
                );

            }

            /*
            |--------------------------------------------------------------------------
            | Background Overlay (::before)
            |--------------------------------------------------------------------------
            */

            style =
                getComputedStyle(
                    element,
                    '::before'
                );

            backgroundImage =
                style.backgroundImage;

            if (
                backgroundImage &&
                backgroundImage !== 'none'
            ) {

                backgroundImages.push(
                    backgroundImage
                );

            }

            /*
            |--------------------------------------------------------------------------
            | Background Overlay (::after)
            |--------------------------------------------------------------------------
            */

            style =
                getComputedStyle(
                    element,
                    '::after'
                );

            backgroundImage =
                style.backgroundImage;

            if (
                backgroundImage &&
                backgroundImage !== 'none'
            ) {

                backgroundImages.push(
                    backgroundImage
                );

            }

            if ( ! backgroundImages.length ) {
                return;
            }

            backgroundImages.forEach(
                function ( backgroundImage ) {

                    const urlMatch =
                        backgroundImage.match(
                            /url\(["']?([^"')]+)["']?\)/
                        );

                    if ( ! urlMatch || ! urlMatch[1] ) {
                        return;
                    }

                    const url =
                        urlMatch[1];

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

                                            image_url:
                                                url,

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

                }
            );

        }
	}
);