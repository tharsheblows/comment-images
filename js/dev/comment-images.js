/*! MJJ Comment Images - v0.1.0 - 2015-11-10
 * Copyright (c) 2015; * Licensed GPLv2+ */
jQuery( document ).ready( function ($){

	'use strict';

	// If the comment form is visible, set it's enctype to support uploading files
	if ( 0 < $( '#commentform' ).length ) {
		$( '#commentform' ).attr( 'enctype', 'multipart/form-data' );
	}

	// for http://stackoverflow.com/questions/25095863/how-to-detect-file-extension-with-javascript-filereader
	var allowedCommentImageTypes = ['jpg', 'jpeg', 'png', 'gif'];  //acceptable file types
	var uploadCommentImageBackground = '/src/wp-content/themes/bloodsugarfix/images/dist/recipe-images/fork-knife-plate.png';


	$( '.comment-image-wrapper' ).on( 'change', 'input[type="file"].mjj-file-upload-box ', function(){
    	commentImagesDisplayPreview( this );
	});

	$( '.comment-image-wrapper' ).on( 'click', 'a.clear_image_upload', function( e ){
		e.preventDefault();
    	commentImagesClearImageUpload( this );
	});

	// http://stackoverflow.com/questions/4459379/preview-an-image-before-it-is-uploaded/4459419#4459419
	// this is pretty much duplicated from the bsf theme but in the interest of completeness I'm putting it here
	function commentImagesDisplayPreview( input ) {

		var clear_this = $( input ).parent().find( 'a.clear_image_upload' );

		if( window.FileReader !== undefined ){
			// This requires FileReader API so test for FileReader
	    	if (input.files && input.files[0] ) {

	    	    var reader = new FileReader();

	    	    var extension = input.files[0].name.split('.').pop().toLowerCase();
	    	    var isOK = allowedCommentImageTypes.indexOf(extension) > -1;
	    	    var notTooBig = input.files[0].size < 8000000;

	    	    if( isOK && notTooBig ){
	    	    	reader.onload = function (e) {
	    	    	    $( input ).css({ 'background-image': 'url(' + e.target.result + ')', 'background-size': 'auto 90%'});
	    	    	}

	    	    	reader.readAsDataURL(input.files[0]);
	    	    }
	    	    else{
	    	    	commentImagesClearImageUpload( clear_this );
	    	    	alert( 'You may only uploads images of types jpg, png or gif and they must be less than 8MB.' );
	    	    }

	    	}
	    	else{
	    		$( input ).css({ 'background-image': 'url(' + uploadCommentImageBackground + ')', 'background-size': 'auto 230px'});
	    	}
		}

	}

	// need a clear image function https://css-tricks.com/snippets/jquery/clear-a-file-input/
	function commentImagesClearImageUpload( input ){
		var imageToClear = $( input ).attr( 'data-clear' );
		$( '#' + imageToClear ).replaceWith( $( '#' + imageToClear ).css({'val': '', 'background-image': 'url(' + uploadCommentImageBackground + ')'}).clone(true) );
	}

});
