<?php
/*
Plugin Name: Comment Images
Plugin URI: http://tommcfarlin.com/comment-images
Description: Allow your readers easily to attach an image to their comment.
Version: 1.1
Author: Tom McFarlin
Author URI: http://tommcfarlin.com
Author Email: tom@tommcfarlin.com
License:

  Copyright 2012 Tom McFarlin (tom@tommcfarlin.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// TODO 
// - Next update show image preview in the admin of the image
// - 

class Comment_Image {

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	
	/**
	 * Initializes the plugin by setting localization, admin styles, and content filters.
	 */
	function __construct() {
	
		load_plugin_textdomain( 'comment-images', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	
		// Determine if the hosting environment can save files.
		if( $this->can_save_files() ) {
	
			// Add comment related stylesheets, scripts, form manipulation, and image serialization
			add_action( 'wp_enqueue_scripts', array( &$this, 'add_styles' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'add_scripts' ) );
			add_action( 'comment_form' , array( &$this, 'add_image_upload_form' ) );
			add_filter( 'wp_insert_comment', array( &$this, 'save_comment_image' ) );
			add_filter( 'comment_text', array( &$this, 'display_comment_image' ) );
		
		// If not, display a notice.	
		} else {
		
			add_action( 'admin_notices', array( &$this, 'save_error_notice' ) );
			
		} // end if/else

	} // end constructor
	
	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/
	 
	 /**
	  * Display a WordPress error to the administrator if the hosting environment does not support 'file_get_contents.'
	  */
	 function save_error_notice() {
		 
		 $html = '<div id="comment-image-notice" class="error">';
		 	$html .= '<p>';
		 		$html .= __( '<strong>Comment Images Notice:</strong> Unfortunately, your host does not allow uploads from the comment form. This plugin will not work for your host.', 'comment-images' );
		 	$html .= '</p>';
		 $html .= '</div><!-- /#comment-image-notice -->';
		 
		 echo $html;
		 
	 } // end save_error_notice
	 
	 /**
	  * Adds the public stylesheet to the single post page.
	  */
	 function add_styles() {
	
		if( is_single() ) {
			
			wp_register_style( 'comment-images', plugins_url( '/comment-images/css/plugin.css' ) );
			wp_enqueue_style( 'comment-images' );
			
		} // end if
		
	} // end add_scripts
	 
	/**
	 * Adds the public JavaScript to the single post page.
	 */ 
	function add_scripts() {
	
		if( is_single() ) {
			
			wp_register_script( 'comment-images', plugins_url( '/comment-images/js/plugin.min.js' ), array( 'jquery' ) );
			wp_enqueue_script( 'comment-images' );
			
		} // end if
		
	} // end add_scripts

	/**
	 * Adds the comment image upload form to the comment form.
	 *
	 * @params	$post_id	The ID of the post on which the comment is being added.
	 */
 	function add_image_upload_form( $post_id ) {

	 	// Create the label and the input field for uploading an image
	 	$html = '<div id="comment-image-wrapper">';
		 	$html .= '<p id="comment-image-error">';
		 		$html .= __( '<strong>Heads up!</strong> You are attempting to upload an invalid image. If saved, this image will not display with your comment.', 'comment-image' );
		 	$html .= '</p>';
			 $html .= "<label for='comment_image_$post_id'>";
			 	$html .= __( 'Select an image for your comment (GIF, PNG, JPG, JPEG):', 'comment-images' );
			 $html .= "</label>";
			 $html .= "<input type='file' name='comment_image_$post_id' id='comment_image' />";
		 $html .= '</div><!-- #comment-image-wrapper -->';

		 echo $html;
		 
	} // end add_image_upload_form
	
	/**
	 * Adds the comment image upload form to the comment form.
	 *
	 * @params	$comment_id	The ID of the comment to which we're adding the image.
	 */
	function save_comment_image( $comment_id ) {

		// The ID of the post on which this comment is being made
		$post_id = $_POST['comment_post_ID'];
		
		// The key ID of the comment image
		$comment_image_id = "comment_image_$post_id";
		
		// If the nonce is valid and the user uploaded an image, let's upload it to the server
		if( isset( $_FILES[ $comment_image_id ] ) && ! empty( $_FILES[ $comment_image_id ] ) ) {
			
			// Store the parts of the file name into an array
			$file_name_parts = explode( '.', $_FILES[$comment_image_id]['name'] );
			
			// If the file is valid, upload the image, and store the path in the comment meta
			if( $this->is_valid_file_type( $file_name_parts[ count( $file_name_parts ) - 1 ] ) ) {;
			
				// Upload the comment image to the uploads directory
				$comment_image_file = wp_upload_bits( $_FILES[ $comment_image_id ]['name'], null, file_get_contents( $_FILES[ $comment_image_id ]['tmp_name'] ) );
				
				// Set post meta about this image. Need the comment ID and need the path.
				if( false == $comment_image_file['error'] ) {
					
					// Since we've already added the key for this, we'll just update it with the file.
					add_comment_meta( $comment_id, 'comment_image', $comment_image_file );
					
				} // end if/else
			
			} // end if
 		
		} // end if
		
	} // end save_comment_image
	
	/**
	 * Appends the image below the content of the comment.
	 *
	 * @params	$comment	The content of the comment.
	 */
	function display_comment_image( $comment ) {
		
		// If the comment image meta value exists, then render the comment image
		if( false != get_comment_meta( get_comment_ID(), 'comment_image' ) ) {
		
			// Get the comment image meta
			$comment_image = get_comment_meta( get_comment_ID(), 'comment_image', true );
			
			// Render it in a paragraph element appended to the comment
			$comment .= '<p class="comment-image">';
				$comment .= '<img src="' . $comment_image['url'] . '" alt="" />';
			$comment .= '</p><!-- /.comment-image -->';	
			
		} // end if
		
		return $comment;
		
	} // end display_comment_image
	
	/*--------------------------------------------*
	 * Utility Functions
	 *---------------------------------------------*/
	
	/**
	 * @params	$type	The file type attempting to be uploaded.
	 * @returns			Whether or not the specified file type is able to be uploaded.
	 */ 
	private function is_valid_file_type( $type ) { 
	
		$type = strtolower( trim ( $type ) );
		return $type == 'png' || $type == 'gif' || $type == 'jpg' || $type == 'jpeg';
		
	} // end is_valid_file_type
	
	/**
	 * @returns			Whether or not the hosting environment supports the ability to upload files.
	 */ 
	private function can_save_files() {
		return function_exists( 'file_get_contents' );
	} // end can_save_files
  
} // end class

new Comment_Image();
?>