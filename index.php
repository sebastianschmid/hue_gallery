<?php
/*
Plugin Name: 100und1 Gallery
Plugin URI: http://process.studio
Description: Adds a gallery metabox to your post types. Add images using the Wordpress uploader and use drag & drop to reorder.
Version: 02/2016
Author: Martin Grödl
Author URI: mailto:martin@process.studio
License: GPL2
*/

define( 'HUE_GALLERY_PREFIX', 'hue_gallery' );
hue_gallery_define( 'PUGIN_NAME', '100und1 Gallery' );
hue_gallery_define( 'PLUGIN_DIRECTORY', 'hue-gallery');
hue_gallery_define( 'CURRENT_VERSION', '12/2013');

hue_gallery_define( 'DEBUG', false);		# never use debug mode on productive systems

function hue_gallery_define($key, $value) {
	define( strtoupper(HUE_GALLERY_PREFIX) . '_' . $key, $value );
}

function hue_gallery_prefix($str) {
	return strtolower(HUE_GALLERY_PREFIX) . '_' . $str;
}


// create custom plugin settings menu
add_action( 'admin_menu', 'hue_gallery_create_menu' );

// call register settings function
add_action( 'admin_init', 'hue_gallery_register_settings' );

register_activation_hook(__FILE__, 'hue_gallery_activate');
register_deactivation_hook(__FILE__, 'hue_gallery_deactivate');
register_uninstall_hook(__FILE__, 'hue_gallery_uninstall');

// activating the default values
function hue_gallery_activate() {
	// add_option does nothing, if it already exists
	add_option( hue_gallery_prefix('title'), 'Gallery' );
	add_option( hue_gallery_prefix('post_types'), 'post, page' );
}

// deactivating
function hue_gallery_deactivate() {
	// don't delete options here
}

// uninstalling
function hue_gallery_uninstall() {
	# delete all data stored
	delete_option( hue_gallery_prefix('title') );
	delete_option( hue_gallery_prefix('post_types') );
}

function hue_gallery_create_menu() {
	 //or create settings menu page
	add_options_page(__('100und1 Gallery', HUE_GALLERY_PREFIX), __("100und1 Gallery", HUE_GALLERY_PREFIX), 9,  'gallery-settings', 'hue_gallery_settings_page');

}

function hue_gallery_settings_page() {
?>
<div class="wrap">
<h2><?php print HUE_GALLERY_PUGIN_NAME . " (" . HUE_GALLERY_CURRENT_VERSION . ")" ?></h2>

<form method="post" action="options.php">
	<p>Adds a gallery metabox to your post types. Add images using the nativ Wordpress uploader/media library and use drag &amp; drop to reorder.</p>
	<p>Use the template tag <code>hue_gallery_ids($post_id)</code> to retrieve an array of image attachment ids for a specific gallery. <code>$post_id</code> is the id of the post the gallery is attached to. Uses the current post, if omitted.</p>
    <?php
		settings_fields( 'hue_gallery_settings-group' );
	?>
	<h3>Options</h3>
    <table class="form-table">
	    <tr valign="top">
	    <th scope="row">Title</th>
	    <td><input type="text" name="hue_gallery_title" value="<?php echo get_option('hue_gallery_title'); ?>" /></td>
	    </tr>

        <tr valign="top">
        <th scope="row">Post Types</th>
        <td><input type="text" name="hue_gallery_post_types" value="<?php echo get_option('hue_gallery_post_types'); ?>" /></td>
        </tr>
    </table>

    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
</form>
</div>
<?php
}

function hue_gallery_register_settings() {
	//register settings
	register_setting( hue_gallery_prefix('settings-group'), hue_gallery_prefix('title') );
	register_setting( hue_gallery_prefix('settings-group'), hue_gallery_prefix('post_types') );
}



/* ========================================================================================================================
	Custom Gallery Interface
======================================================================================================================== */

add_image_size('hue_gallery_thumbnail', 150, 150, false);


add_action( 'admin_enqueue_scripts', 'hue_gallery_admin_scripts' );
function hue_gallery_admin_scripts() {
	wp_enqueue_script( hue_gallery_prefix('admin'), plugins_url('/_/js/admin.js', __FILE__), array( 'jquery' ) );
	wp_enqueue_style( hue_gallery_prefix('admin'), plugins_url('/_/css/admin.css', __FILE__) );
	wp_enqueue_media(); // add wp.media scripts
}


/* Define the custom box */
add_action( 'add_meta_boxes', 'hue_gallery_metabox' );
/* Do something with the data entered */
add_action( 'save_post', 'hue_gallery_save' );

/* Adds a box to the main column on the Post and Page edit screens */
function hue_gallery_metabox() {
	$option = get_option( hue_gallery_prefix('post_types') );
	$post_types = explode(',', $option);
    //$post_types = array( 'photos' );
    foreach ($post_types as $post_type) {
        add_meta_box(
            'hue_gallery', // id
            get_option( hue_gallery_prefix('title') ) , // title
            'hue_gallery_content', // callback
            trim($post_type)
        );
    }
}

/* get html for in image attachment */
function hue_render_gallery_item($id) {
	$att = get_post($id);
	$out = '';
	$out .= '<div class="thumbnail" data-id="'.$id.'" title="'.$att->post_title.'">';
	$out .= wp_get_attachment_image($id, 'hue_gallery_thumbnail');
	$out .= '<div class="remove" title="Remove Image"></div>';
	$out .= '</div>';
	return $out;
}

/* Render all gallery items of current post; optional parameter: array of attachment ids */
function hue_render_gallery_items() {
	if ( func_num_args() == 0) {
		$ids = hue_gallery_ids();
	} else {
		$ids = func_get_arg(0);
	}
	$out = '';
	foreach ($ids as $id) {
		$out .= hue_render_gallery_item($id);
	}
	return $out;
}

/* Prints the box content */
function hue_gallery_content( $post ) {
	// Use nonce for verification

	wp_nonce_field( plugin_basename( __FILE__ ), 'hue_gallerynonce' );

	// get att id's from post meta
	$att_ids_json = get_post_meta( $post->ID, '_hue_gallery', true ); // it's a json string
	//$att_ids_json = '[4,5,6,49]';
	if (empty($att_ids_json)) $att_ids_json = '[]'; // default to empty list
	$att_ids = json_decode($att_ids_json);

	echo '<div class="thumbnails">';
	echo hue_render_gallery_items($att_ids);

	echo '</div>';
	echo '<input type="hidden" class="att_ids" name="gallery_att_ids" value="'.$att_ids_json.'"></input>'; // this holds a JSON string of image attachment ids
	echo '<input type="button" class="button" value="Add Images"></input>';
}

/* When the post is saved, saves our custom data */
function hue_gallery_save( $post_id ) {
	// First we need to check if the current user is authorised to do this action.
	if ( 'page' == $_POST['post_type'] ) {
	if ( ! current_user_can( 'edit_page', $post_id ) )
	    return;
	} else {
	if ( ! current_user_can( 'edit_post', $post_id ) )
	    return;
	}

	// Secondly we need to check if the user intended to change this value.
	if ( ! isset( $_POST['hue_gallerynonce'] ) || ! wp_verify_nonce( $_POST['hue_gallerynonce'], plugin_basename( __FILE__ ) ) )
	  return;

	// save data
	$data = $_POST['gallery_att_ids'];
	update_post_meta($post_id, '_hue_gallery', $data);
}

/* ajax action to retrieve gallery thumbnail html for backend */
add_action('wp_ajax_get_thumbnails', 'hue_ajax_get_thumbnails');
function hue_ajax_get_thumbnails() {
	// check nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], plugin_basename( __FILE__ ) ) )
	  return;

	$att_ids_json = $_POST['gallery_att_ids']; // json array of att ids
	if ( empty($att_ids_json) ) die();

	$att_ids = json_decode($att_ids_json);
	//response
	header("Content-type: text/html");
	echo hue_render_gallery_items($att_ids);
	die(); // this is required to return a proper result
}


/* get an array of gallery image ids for current post. optional parameter: specific post ID */
function hue_gallery_ids() {
	if ( func_num_args() == 0) {
		$post_id = get_the_ID();
	} else {
		$post_id = func_get_arg(0);
	}
	$att_ids_json = get_post_meta( $post_id, '_hue_gallery', true ); // it's a json string
	if (empty($att_ids_json)) $att_ids_json = '[]'; // default to empty list
	$att_ids = json_decode($att_ids_json);
	return $att_ids; // returns an array
}


?>
