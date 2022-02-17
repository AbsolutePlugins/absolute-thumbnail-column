=== Absolute Thumbnail Column ===
Contributors: absoluteplugins, niamul, mhamudul_hk
Tags: image, thumbnail, featured image, image
Requires at least: 4.8
Tested up to: 5.9
Requires PHP: 5.6
Stable tag: 1.0.1
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Absolute Thumbnail column allows you to upload, select and change thumbnail on any post-types right from the post table.

== Description ==

With **Absolute Thumbnail Column** you can upload, select or change post thumbnail (Aka Featured Image) with ease. No need to edit your page/post only just to change the thumbnail. It supports post types that have thumbnail supports.

**Absolute Thumbnail Column** is fully compatible with **WooCommerce**, integrates nicely with Image Column, and enabling uploading, selecting or changing product thumbnail from the list-table itself, saving a lot of time.

**Absolute Thumbnail Column** uses the native **WordPress** media popup, ensuring the security and integrity of core features of the media library.

## Features

1. Unlimited Usages.
2. Supports all post type that has `thumbnail` support.
3. Builtin WooCommerce support.
4. Uses the native WordPress media popup.

== Installation ==

= Automatic installation =

Automatic installation is the easiest option -- WordPress will handles the file transfer, and you won’t need to leave your web browser.
1. Log in to your WordPress dashboard
2. Navigate to the Plugins menu, and click “Add New.”
3. In the search field type “Absolute Thumbnail Column,” then click “Search Plugins.” Once you’ve found us,  you can view details about it such as the point release, rating, and description. Most importantly of course, you can install it by! Click “Install Now,” and WordPress will take it from there.

= Manual installation =

1. Download this plugin's .zip file and extract it.
2. Upload the extracted directory (`absolute-thumbnail-column`) to the `/wp-content/plugins/` directory on your web server with your favorite ftp/sftp client.
3. Activate the plugin through the 'Plugins' menu in WordPress

The WordPress codex contains more [instructions on how to do this here](https://wordpress.org/support/article/managing-plugins/#:~:text=Manual%20Plugin%20Installation,-%23).

== Upgrade Notice ==
Automatic updates should work smoothly, but we still recommend you back up your site.

== Frequently Asked Questions ==

= Does this support all post types? =

Yes and No. This support all of the post type that supports post thumbnail feature.

= Does it work with any WordPress theme? =

This plugin does it's things only in admin dashboard, and has nothing to do with themes.

= And WooCommerce? =

We already implemented support WooCommerce's product list table's image column too.

= Does this plugin deletes the actual image from the media library when remove button clicked? =

No. The remove button on the thumbnail column only remove the post's thumbnail (meta),
and doesn't deletes the actual image from media library.
However you can delete the image from the media popup as usual.

= Thumbnail Column Not Showing! =

Please check the **Screen Options** located on the top right corner on the screen and make sure `Thumbnail` checkbox is checked.
If not please click the checkbox and this apply to apply the changes.

= I'm a developer & My Post type has thumbnail support but the column not showing up!! =

Please make sure your post type is registered with `init` or any earlier hook with normal or higher (before 10) priority.
If the priority is too low (Eg. 9999) this plugin might not able to detect it.
You can also use the following 2 filters to add support for your post-type `absp_post_type_supports_thumbnail` && `absp_current_post_type_supports_thumbnail`.
It's require that you use both of these to make your post-type get recognized by the plugin.

E.g.
`add_filter( 'absp_post_type_supports_thumbnail', 'add_your_post_type_thumbnail_column_support', 10, 2 );
add_filter( 'absp_current_post_type_supports_thumbnail', 'add_your_post_type_thumbnail_column_support', 10, 2 );

function add_your_post_type_thumbnail_column_support( $supports, $post_type ) {
	if ( 'your-post-type' === $post_type ) {
		return true;
	}

	return $supports;
}`

== Screenshots ==

1. Thumbnail (Featured Image) Column
2. Set Thumbnail Button
3. Saving User's Selection
4. Change Or Remove Thumbnail From Post
5. Supports WooCommerce Too

== Changelog ==

= 1.0.1 – 2022-02-17 =
* Fixed WooCommerce product list thumbnail column width.
* Tested with the latest WordPress & WooCommerce available

= 1.0.0 =
* Initial release.
