<?php
/**
 * Plugin Name: Absolute Thumbnail Column
 * Plugin URI: https://absoluteplugins.com/plugins/absolute-thumbnail-column/
 * Description: Absolute Thumbnail column allows you to upload, select and change thumbnail on any post-types right from the post table.
 * Author: AbsolutePlugins
 * Author URI: https://absoluteplugins.com/
 * Text Domain: absp-thumbnail-column
 * Domain Path: /languages
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Version: 1.0.1
 *
 * [PHP]
 * Requires PHP: 5.6
 *
 * [WP]
 * Requires at least: 4.8
 * Tested up to: 5.9
 *
 * [WC]
 * WC requires at least: 4.5
 * WC tested up to: 6.2
 *
 * @package ABSP_ThumbnailColumn
 * @version 1.0.1
 */

/**
 * Copyright (c) 2021 AbsolutePlugins <https://absoluteplugins.com>
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Initialize.
 * Init Textdomain & List table columns.
 *
 * @return void
 */
function absp_thumbnail_column_init() {

	// Load Textdomain.
	load_plugin_textdomain( 'absp-thumbnail-column', false, basename( dirname( __FILE__ ) ) . '/languages/' );

	$supported_types = [];
	foreach ( get_post_types() as $post_type ) {

		/**
		 * Filters post thumbnail support status for post type.
		 *
		 * @param bool $supports
		 * @param string $post_type
		 */
		$supports = apply_filters( 'absp_post_type_supports_thumbnail', post_type_supports( $post_type, 'thumbnail' ), $post_type );

		if ( ! $supports ) {
			continue;
		}

		$supported_types[] = $post_type;

		// Hook into post-type's list table.
		add_filter( "manage_{$post_type}_posts_columns", 'add_absp_thumbnail_column', 100 );
		add_filter( "manage_{$post_type}_posts_custom_column", 'render_absp_thumbnail_column_content', 10, 2 );
	}

	if ( ! empty( $supported_types ) ) {
		add_action( 'admin_head', 'absp_thumbnail_column_styles', 99999 );
		add_action( 'admin_footer', 'absp_thumbnail_column_scripts', 99999 );
	}
}

/**
 * Adds the thumbnail column.
 *
 * @param array $columns List Table Columns
 *
 * @return array
 */
function add_absp_thumbnail_column( $columns ) {
	$screen = get_current_screen();

	if ( isset( $columns['thumb'] ) || 'product' === $screen->post_type ) {
		return $columns;
	}
	$offset = 2;
	return array_slice( $columns, 0, $offset, true )
	       +
	       [ 'absp_thumb_col' => __( 'Thumbnail', 'absp-thumbnail-column' ), ]
	       +
	       array_slice( $columns, $offset, null, true );
}

/**
 * Render post list table column data for thumbnail column.
 * @param string $column Column name.
 * @param int $id Post id.
 */
function render_absp_thumbnail_column_content( $column, $id ) {
	if ( in_array( $column, [ 'absp_thumb_col', 'thumb' ] ) ) {
		wp_enqueue_media();
		generate_absp_thumbnail( $id );
	}
}

/**
 * Checks if post type has thumbnail support.
 *
 * @return bool
 */
function absp_current_post_type_supports_thumbnail() {
	$screen   = get_current_screen();
	$supports = $screen && ( 'edit' === $screen->base && ! empty( $screen->post_type ) && post_type_supports( $screen->post_type, 'thumbnail') );

	/**
	 * Filters post thumbnail support status for current post type.
	 *
	 * @param bool $supports
	 * @param string $post_type
	 */
	return apply_filters( 'absp_current_post_type_supports_thumbnail', $supports, $screen ? $screen->post_type : '' );
}

/**
 * Generate the thumbnail html data.
 *
 * @param int $id Post id.
 * @param bool $echo Echo or return the content.
 *
 * @return false|string|void
 */
function generate_absp_thumbnail( $id, $echo = true ) {
	$thumb_id  = get_post_thumbnail_id( $id );
	$has_thumb = has_post_thumbnail( $id );
	if ( ! $thumb_id ) {
		$thumb_id = -1;
	}
	if ( ! $echo ) {
		ob_start();
	}
	?>
	<div id="<?php echo 'absp-thumb-' . $id; ?>" class="absp-thumbs-wrapper <?php echo $has_thumb ? 'has-thumb' : 'no-thumb'; ?>">
		<?php
		if ( $has_thumb ) {
			echo get_the_post_thumbnail( $id, 'thumbnail', [ 'alt' => get_the_title( $id ) ] );
			?>
			<div class="thumb-handler hide-if-no-js"
				 data-id="<?php echo esc_attr( $id ); ?>"
				 data-featured-image-id="<?php echo esc_attr( $thumb_id ); ?>"
				 data-nonce="<?php echo esc_attr( wp_create_nonce( 'update-post_' . $id ) ); ?>"
			>
				<a href="#" class="set-post-thumbnail" title="<?php esc_attr_e( 'Click the image to edit or update', 'absp-thumbnail-column' ); ?>">
					<span class="dashicons dashicons-controls-repeat" aria-hidden="true"></span>
					<span class="screen-reader-text"><?php _e( 'Click the image to edit or update', 'absp-thumbnail-column' ); ?></span>
				</a>
				<a href="#" class="remove-post-thumbnail" title="<?php esc_attr_e( 'Remove featured image', 'absp-thumbnail-column' ); ?>">
					<span class="dashicons dashicons-trash" aria-hidden="true"></span>
					<span class="screen-reader-text"><?php _e( 'Remove featured image', 'absp-thumbnail-column' ); ?></span>
				</a>
			</div>
			<?php
		} else {
			printf(
				'<img class="wp-post-image" src="%s" alt="%s">',
				plugins_url( 'assets/images/placeholder.png', plugin_basename( __FILE__ ) ),
				esc_attr__( 'No Thumb', 'absp-thumbnail-column' )
			);
			?>
			<div class="thumb-handler hide-if-no-js"
				 data-id="<?php echo esc_attr( $id ); ?>"
				 data-featured-image-id="<?php echo esc_attr( $thumb_id ); ?>"
				 data-nonce="<?php echo esc_attr( wp_create_nonce( 'update-post_' . $id ) ); ?>"
			>
				<a href="#" class="set-post-thumbnail" title="<?php esc_attr_e( 'Set featured image', 'absp-thumbnail-column' ); ?>">
					<span class="dashicons dashicons-plus" aria-hidden="true"></span>
					<span class="screen-reader-text"><?php _e( 'Set featured image', 'absp-thumbnail-column' ); ?></span>
				</a>
			</div>
			<?php
		}
		?>
	</div>
	<?php
	if ( ! $echo ) {
		return ob_get_clean();
	}
}

/**
 * CSS.
 *
 * @return void
 */
function absp_thumbnail_column_styles() {
	if ( ! absp_current_post_type_supports_thumbnail() ) {
		return;
	}
	?>
	<style>
		.absp-thumbs-wrapper {
			position: relative;
			display: block;
			width: 70px;
			height: auto;
			min-height: 50px;
			margin: 0;
			box-sizing: border-box;
			line-height: 0;
			box-sizing: border-box;
		}
		.absp-thumbs-wrapper * {
			box-sizing: border-box;
		}
		.type-product .column-thumb .absp-thumbs-wrapper {
			margin: 0 auto;
		}
		/* WC Compatibility */
		.type-product .column-thumb > a img,
		.type-product .column-thumb > a {
			display: none !important;
			visibility: hidden !important;
		}
		.wp-admin.woocommerce-page table.wp-list-table tr.type-product td.thumb .absp-thumbs-wrapper img,
		.absp-thumbs-wrapper img {
			width: 100% !important;
			max-width: 100% !important;
			height: auto !important;
			min-height: 50px;
			max-height: 100% !important;
			border: 2px solid #c3c4c7;
			padding: 2px;
			margin: 0 !important;
			overflow: hidden;
		}
		.absp-thumbs-wrapper .spinner {
			position: absolute;
			float: none;
			height: 17px;
			width: 17px;
			background-size: 17px 17px;
			top: 0;
			right: 0;
			bottom: 0;
			left: 0;
			margin: auto;
		}
		.absp-thumbs-wrapper .thumb-handler {
			position: absolute;
			display: flex;
			top: 0;
			right: 0;
			bottom: 0;
			left: 0;
			margin: auto;
			vertical-align: middle;
			height: 100%;
			width: 100%;
			text-align: center;
			align-items: center;
			justify-content: center;
			background: rgba(255, 255, 255, 0.65);
			visibility: hidden;
			transition: visibility 70ms linear;
		}
		.absp-thumbs-wrapper .thumb-handler a:not(:last-child) {
			margin-right: 5px;
		}
		.absp-thumbs-wrapper.loading img,
		.absp-thumbs-wrapper:hover img {
			filter: blur(2px);
		}
		.absp-thumbs-wrapper:not(.loading):hover .thumb-handler {
			visibility: visible;
		}
		.absp-thumbs-wrapper.no-thumb .set-post-thumbnail {
			height: 100%;
			width: 100%;
			display: flex;
			justify-content: center;
			align-items: center;
		}
	</style>
	<?php
}

/**
 * JS.
 *
 * @return void
 */
function absp_thumbnail_column_scripts() {
	if ( ! absp_current_post_type_supports_thumbnail() ) {
		return;
	}
	?>
	<script>
		(function( $ ){
			"use strict";
			$(document).on( 'ready', function () {
				/**
				 * @namespace wp.media.featuredImage
				 * @memberOf wp.media
				 */
				var featuredImage = {
					set_data: function( el ) {
						wp.media.view.settings.post = $(el).closest('.thumb-handler').data();
					},
					/**
					 * Get the featured image post ID
					 *
					 * @return {wp.media.view.settings.post.featuredImageId|number}
					 */
					get: function() {
						return wp.media.view.settings.post.featuredImageId;
					},
					/**
					 * Sets the featured image ID property and sets the HTML in the post meta box to the new featured image.
					 *
					 * @param {number} id The post ID of the featured image, or -1 to unset it.
					 */
					set: function( id ) {
						var settings = wp.media.view.settings,
							wrapper = $('#absp-thumb-' + settings.post.id );

						// Set the thumb id.
						settings.post.featuredImageId = id;

						// Show spinner.
						wrapper.addClass( 'loading' ).append( '<span class="spinner is-active"></span>' );

						wp.media.post( 'absp_thumbnail_column_ajax_add', {
							post_id:      settings.post.id,
							thumbnail_id: settings.post.featuredImageId,
							_wpnonce:     settings.post.nonce
						}).done( function( html ) {
							if ( '0' === html || '-1' === html ) {
								if ( id ) {
									window.alert( '<?php esc_html_e( 'Could not set that as the thumbnail image. Try a different attachment.', 'absp-thumbnail-column' ); ?>' );
								} else {
									window.alert( '<?php esc_html_e( 'Could not remove the post thumbnail image. Try a again later.', 'absp-thumbnail-column' ); ?>' );
								}
								return;
							}
							wrapper.replaceWith( html );
						});
					},
					/**
					 * Remove the featured image id, save the post thumbnail data and
					 * set the HTML in the post meta box to no featured image.
					 */
					remove: function() {
						featuredImage.set( -1 );
					},
					/**
					 * The Featured Image workflow
					 *
					 * @this wp.media.featuredImage
					 *
					 * @return {wp.media.view.MediaFrame.Select} A media workflow.
					 */
					frame: function() {
						if ( this._frame ) {
							wp.media.frame = this._frame;
							return this._frame;
						}

						this._frame = wp.media({
							state: 'featured-image',
							states: [ new wp.media.controller.FeaturedImage() , new wp.media.controller.EditImage() ]
						});

						this._frame.on( 'toolbar:create:featured-image', function( toolbar ) {
							/**
							 * @this wp.media.view.MediaFrame.Select
							 */
							this.createSelectToolbar( toolbar, {
								text: wp.media.view.l10n.setFeaturedImage
							});
						}, this._frame );

						this._frame.on( 'content:render:edit-image', function() {
							var selection = this.state('featured-image').get('selection'),
								view = new wp.media.view.EditImage( { model: selection.single(), controller: this } ).render();

							this.content.set( view );

							// After bringing in the frame, load the actual editor via an Ajax call.
							view.loadEditor();

						}, this._frame );

						this._frame.state('featured-image').on( 'select', this.select );

						return this._frame;
					},
					/**
					 * 'select' callback for Featured Image workflow, triggered when
					 *  the 'Set Featured Image' button is clicked in the media modal.
					 *
					 * @this wp.media.controller.FeaturedImage
					 */
					select: function() {
						var selection = this.get('selection').single();
						console.log( selection );

						if ( ! wp.media.view.settings.post.featuredImageId ) {
							return;
						}

						featuredImage.set( selection ? selection.id : -1 );
					},
					/**
					 * Open the content media manager to the 'featured image' tab when
					 * the post thumbnail is clicked.
					 *
					 * Update the featured image id when the 'remove' link is clicked.
					 */
					init: function() {
						$(document)
							.on( 'click', '.set-post-thumbnail', function( event ) {
								event.preventDefault();
								// Stop propagation to prevent thickbox from activating.
								event.stopPropagation();
								featuredImage.set_data( this );
								featuredImage.frame().open();
							})
							.on( 'click', '.remove-post-thumbnail', function() {
								featuredImage.set_data( this );
								featuredImage.remove();
								return false;
							});
					}
				};
				$(featuredImage.init);
			} );
		})(jQuery);
	</script>
	<?php
}

/**
 * Ajax request/response handler for set/remove thumbnail request.
 */
function absp_thumbnail_column_ajax_set_post_thumbnail() {
	$post_ID = (int) $_POST['post_id'];

	check_ajax_referer( "update-post_$post_ID" );

	if ( ! current_user_can( 'edit_post', $post_ID ) ) {
		wp_die( -1 );
	}

	$thumbnail_id = (int) $_POST['thumbnail_id'];

	// For backward compatibility, -1 refers to no featured image.
	if ( -1 === $thumbnail_id ) {
		$thumbnail_id = null;
	}

	// Keep the wp back compact above. if no thumb then return the place holder. and use it to remove the thumb.
	if ( $thumbnail_id ) {
		if (
			! set_post_thumbnail( $post_ID, $thumbnail_id )
			||
			! has_post_thumbnail( $post_ID )
		) {
			wp_die( -1 );
		}
	} else {
		if ( ! delete_post_meta( $post_ID, '_thumbnail_id' ) ) {
			wp_die( -1 );
		}
	}

	wp_send_json_success( generate_absp_thumbnail( $post_ID, false ) );
}

// hook things upl
add_action( 'wp_ajax_absp_thumbnail_column_ajax_add', 'absp_thumbnail_column_ajax_set_post_thumbnail' );
// Fire it up (make sure all post types are already registered).
add_action( 'init', 'absp_thumbnail_column_init', 9999 );
// End of file absolute-thumbnail-column.php.
