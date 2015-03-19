<?php
/**
 * Comment Images
 *
 * Allow your readers easily to attach an image to their comments on posts and pages.
 *
 * @package   Comment_Image
 * @author    Tom McFarlin <tom@tommcfarlin.com>
 * @license   GPL-2.0+
 * @link      http://tommcfarlin.com
 * @copyright 2013 - 2015 Tom McFarlin
 *
 * @wordpress-plugin
 * Plugin Name: Comment Images
 * Plugin URI:  http://tommcfarlin.com/comment-images/
 * Description: Allow your readers easily to attach an image to their comments on posts and pages.
 * Version:     1.23.1
 * Author:      Tom McFarlin
 * Author URI:  http://tommcfarlin.com
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
Comment_Image::get_instance();