(function( $ ) {
	'use strict';

	$(function() {

		// If the comment form is visible, set it's enctype to support uploading files
		if ( 0 < $('#commentform').length ) {
			$( '#commentform' ).attr( 'enctype', 'multipart/form-data' );
		}

		// Setup an event handler so we can notify the user whether or not the file type is valid
		$( '#comment_image' ).change(function () {

			// If the file isn't empty, verify it's a valid file
			if ( '' !== $.trim( $(this).val() ) ) {

				var aFileName, sFileType;

				aFileName = $(this).val().split( '.' );
				sFileType = aFileName[ aFileName.length - 1 ].toString().toLowerCase();

				if ( 'png' === sFileType || 'gif' === sFileType || 'jpg' === sFileType || 'jpeg' === sFileType ) {
					$( '#comment-image-error' ).hide();
				} else {

                    // show localised error message
					$( '#comment-image-error' )
						.html( cm_imgs.fileTypeError )
						.show();

                    // clear file upload input value
                    $( this ).val( '' );

                    // return to prevent hide message by next checks
                    return;

				}

                // check if browser support html5 FILE
                /*
                if ( window.FileReader && window.File && window.FileList && window.Blob ){

                    // check filesize before upload
                    if( cm_imgs.limitFileSize > this.files[0].size ){
                        $( '#comment-image-error' ).hide();
                    } else {

                        $( '#comment-image-error' )
                        	.html( cm_imgs.fileSizeError + ( parseInt( cm_imgs.limitFileSize / 1024 ) ) + 'kb' )
                        	.show();

                        $( this ).val( '' );

                    }
                }
                */
			}

		});

	});

})( jQuery );
