(function ($) {
	"use strict";

	$(function () {

		// Duck out if the comment image options aren't visible
		if ( 0 === $('#disable_comment_images').length ) {
			return;
		} // end if

		// Setup an event handler so we can notify the user whether or not the file type is valid
		$('#comment_image_toggle').on( 'click', function () {

			/* Translators: Until this is localized using the WordPress API, you'll need to localize this value. */
			if ( confirm( 'By doing this, you will toggle Comment Images for all posts on your blog. Are you sure you want to do this?' ) ) {

				$(this).attr('disabled', 'disabled');
				$('#comment_image_source').val('button');
				$('#publish').trigger('click');

			} // end if

		});

	});

}(jQuery));