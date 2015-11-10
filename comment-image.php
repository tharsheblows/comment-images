<?php
/**
 * MJJ Comment Images
 *
 * Allow your readers easily to attach an image to their comments on posts and pages.
 *
 * @author    JJ forked from https://github.com/wp-plugins/comment-images/
 * @license   GPL-2.0+
 * @copyright 2013 - 2015 Tom McFarlin
 *
 * 
 * Plugin Name: MJJ Comment Images 
 * Description: Allow your readers easily to attach an image to their comments on posts and pages.
 * Version:     2.0
 * Author:      JJ forked from https://github.com/wp-plugins/comment-images/
 * Text Domain: comment-image-locale
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
} // end if

require_once( plugin_dir_path( __FILE__ ) . 'class-comment-image.php' );
MJJ_Comment_Image::get_instance();
