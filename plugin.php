<?php
/*
Plugin Name: Comment Images
Donate URI: http://tommcfarlin.com/donate/
Plugin URI: http://tommcfarlin.com/comment-images/
Description: Allow your readers easily to attach an image to their comment.
Version: 1.6.2
Author: Tom McFarlin
Author URI: http://tommcfarlin.com/
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
// - JetPack compatibility

class Comment_Image {

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	
	/**
	 * Initializes the plugin by setting localization, admin styles, and content filters.
	 */
	function __construct() {
	
		// Load plugin textdomain
		add_action( 'init', array( $this, 'plugin_textdomain' ) );
		
		/* Setup the activation hook specifically for checking for the custom.css file
		 * I'm calling the same function using the activation hook - which is when the user activates the plugin,
		 * and during upgrade plugin event. This ensures that the custom.css file can also be managed
		 * when the plugin is updated.
		 *
		 * TODO: Restore this plugin when I've resolved the transient functionality properly.
		 */
		//register_activation_hook( __FILE__, array( $this, 'activate' ) );
		//add_action( 'pre_set_site_transient_update_plugins', array( $this, 'activate' ) );
	
		// Determine if the hosting environment can save files.
		if( $this->can_save_files() ) {
	
			// We need to update all of the comments thus far
			if( false == get_option( 'update_comment_images' ) || null == get_option( 'update_comment_images' ) ) {
				$this->update_old_comments();
			} // end if
	
			// Add comment related stylesheets, scripts, form manipulation, and image serialization
			add_action( 'wp_enqueue_scripts', array( $this, 'add_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) );
			add_action( 'comment_form' , array( $this, 'add_image_upload_form' ) );
			add_filter( 'wp_insert_comment', array( $this, 'save_comment_image' ) );
			add_filter( 'comments_array', array( $this, 'display_comment_image' ) );
			
		// If not, display a notice.	
		} else {
		
			add_action( 'admin_notices', array( $this, 'save_error_notice' ) );
			
		} // end if/else

	} // end constructor
	
	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/
	 
	 /**
	  * Loads the plugin text domain for translation
	  */
	 function plugin_textdomain() {
		 load_plugin_textdomain( 'comment-images', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	 } // end plugin_textdomain
	 
	 /**
	  * In previous versions of the plugin, the image were written out after the comments. Now,
	  * they are actually part of the comment content so we need to update all old options.
	  *
	  * Note that this option is not removed on deactivation because it will run *again* if the
	  * user ever re-activates it this duplicating the image.
	  */
	 private function update_old_comments() {
		 
		// Update the option that this has not run
		update_option( 'update_comment_images', false );
		
		// Iterate through each of the comments...
 		foreach( get_comments() as $comment ) {
 		
			// If the comment image meta value exists...
			if( true == get_comment_meta( $comment->comment_ID, 'comment_image' ) ) {
			
				// Get the associated comment image
				$comment_image = get_comment_meta( $comment->comment_ID, 'comment_image', true );
				
				// Append the image to the comment content
				$comment->comment_content .= '<p class="comment-image">';
					$comment->comment_content .= '<img src="' . $comment_image['url'] . '" alt="" />';
				$comment->comment_content .= '</p><!-- /.comment-image -->';
				
				// Now we need to actually update the comment
				wp_update_comment( (array)$comment );
				
			} // end if
 		
		} // end if
		
		// Update the fact that this has run so we don't run it again
		update_option( 'update_comment_images', true );
		 
	 } // end update_old_comments
	 
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
	
		if( is_single() || is_page() ) {
			
			wp_register_style( 'comment-images', plugins_url( '/comment-images/css/plugin.css' ) );
			wp_enqueue_style( 'comment-images' );
			
		} // end if
		
	} // end add_scripts
	 
	/**
	 * Adds the public JavaScript to the single post page.
	 */ 
	function add_scripts() {
	
		if( is_single() || is_page() ) {
			
			wp_register_script( 'comment-images', plugins_url( '/comment-images/js/plugin.min.js' ), array( 'jquery' ) );
			wp_enqueue_script( 'comment-images' );
			
		} // end if
		
	} // end add_scripts

	/**
	 * Adds the comment image upload form to the comment form.
	 *
	 * @param	$post_id	The ID of the post on which the comment is being added.
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
	 * @param	$comment_id	The ID of the comment to which we're adding the image.
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
	 * @param	$comment	The content of the comment.
	 */
	function display_comment_image( $comments ) {

		// Make sure that there are comments
		if( count( $comments ) > 0 ) {
		
			// Loop through each comment...
			foreach( $comments as $comment ) {
			
				// ...and if the comment has a comment image...
				if( true == get_comment_meta( $comment->comment_ID, 'comment_image' ) ) {
			
					// ...get the comment image meta
					$comment_image = get_comment_meta( $comment->comment_ID, 'comment_image', true );
					
					// ...and render it in a paragraph element appended to the comment
					$comment->comment_content .= '<p class="comment-image">';
						$comment->comment_content .= '<img src="' . $comment_image['url'] . '" alt="" />';
					$comment->comment_content .= '</p><!-- /.comment-image -->';	
				
				} // end if
				
			} // end foreach
			
		} // end if
		
		return $comments;

	} // end display_comment_image
	
	/*--------------------------------------------*
	 * Utility Functions
	 *---------------------------------------------*/
	
	/**
	 * Determines if the specified type if a valid file type to be uploaded.
	 *
	 * @param	$type	The file type attempting to be uploaded.
	 * @return			Whether or not the specified file type is able to be uploaded.
	 */ 
	private function is_valid_file_type( $type ) { 
	
		$type = strtolower( trim ( $type ) );
		return $type == 'png' || $type == 'gif' || $type == 'jpg' || $type == 'jpeg';
		
	} // end is_valid_file_type
	
	/**
	 * Determines if the hosting environment allows the users to upload files.
	 *
	 * @return			Whether or not the hosting environment supports the ability to upload files.
	 */ 
	private function can_save_files() {
		return function_exists( 'file_get_contents' );
	} // end can_save_files
  
} // end class

new Comment_Image();
?>