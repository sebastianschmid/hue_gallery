<div class="wrap"><h2><?php print HUE_GALLERY_PUGIN_NAME . " (" . HUE_GALLERY_CURRENT_VERSION . ")" ?></h2><form method="post" action="options.php">
	<p>Adds a gallery metabox to your post types. Add images using the nativ Wordpress uploader/media library and use drag &amp; drop to reorder.</p>
	<p>Use the template tag <code>hue_gallery_ids($post_id)</code> to retrieve an array of image attachment ids for a specific gallery (<code>$post_id</code> is the id of the post the gallery is attached to).</p>    <?php		settings_fields( 'hue_gallery_settings-group' );	?>
	<h3>Options</h3>    <table class="form-table">
	    <tr valign="top">
	    <th scope="row">Title</th>
	    <td><input type="text" name="hue_gallery_title" value="<?php echo get_option('hue_gallery_title'); ?>" /></td>
	    </tr>
	            <tr valign="top">        <th scope="row">Post Types</th>        <td><input type="text" name="hue_gallery_post_types" value="<?php echo get_option('hue_gallery_post_types'); ?>" /></td>        </tr>    </table>
        <p class="submit">    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />    </p></form></div>