<div class="wrap">
	<p>Adds a gallery metabox to your post types. Add images using the nativ Wordpress uploader/media library and use drag &amp; drop to reorder.</p>
	<p>Use the template tag <code>hue_gallery_ids($post_id)</code> to retrieve an array of image attachment ids for a specific gallery (<code>$post_id</code> is the id of the post the gallery is attached to).</p>
	<h3>Options</h3>
	    <tr valign="top">
	    <th scope="row">Title</th>
	    <td><input type="text" name="hue_gallery_title" value="<?php echo get_option('hue_gallery_title'); ?>" /></td>
	    </tr>
	    
    