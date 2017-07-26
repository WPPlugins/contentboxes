<?php
/*
Plugin Name: Contentboxes
Plugin URI: http://www.horttcore.de/#
Description: This plugin will add some awesome cms functions to your site. Add posts to static pages on the fly.
Version: 1.0
Author: Ralf Hortt
Author URI: http://www.horttcore.de/
*/

//======================================
// Description: Displaying the Contentbox tab in editor view
Function cb_add_box(){
	add_meta_box('contentboxen', __('Contentboxes'), 'cb_meta_box', 'post');	
	add_meta_box('contentboxen', __('Contentboxes'), 'cb_meta_box', 'page');
}

//======================================
// Description: Template Tag for displaying the contentboxes
Function wp_get_contentboxes($before="", $after="", $link = FALSE, $content = TRUE){
global $post, $wpdb;

	$boxes = array();
	$sql = "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = '$post->ID' AND meta_key = 'contentbox'";
	$col = $wpdb->get_col($sql);	
	foreach($col as $col) {
		$pos = explode(" ", $col);
		$boxes[$pos[1]] = $pos[0];
	}

	foreach($boxes as $key => $value) {
		$ids = get_contentbox_ids($post->ID);
		$sql = "SELECT guid, ID, post_title, post_content, post_excerpt FROM $wpdb->posts WHERE id = '$value'";
		$row = $wpdb->get_row($sql);
		$boxes[$key] = $row;
	}
	
	ksort($boxes);
	
	echo $before;
	foreach($boxes as $row) {?>
		<li class="contentbox" id="contentbox-<?php echo $row->ID; ?>">
			<h2><?php if($link) {echo "<a href='$row->guid'>";} echo $row->post_title; if($link) {echo "</a>";}?></h2>
			<?php
			if ($content) {
				if ($row->post_excerpt) {echo $row->post_excerpt;}
				else{echo $row->post_content;}
			}
			?>
		</li>
		<?php
	}
	echo $after;
}


//======================================
// Description: Content of the meta box
Function cb_meta_box(){
global $wpdb;
	$sql = "SELECT * FROM $wpdb->posts INNER JOIN $wpdb->term_relationships ON object_id = ID WHERE term_taxonomy_id = '".cb_category()."' AND post_status = 'publish' ORDER BY post_title";
	$row = $wpdb->get_results($sql);
		
	if ($_GET['post']) {$cb_ids = get_contentbox_ids($_GET['post']);}
		
	foreach($row as $row) {	?>
		
		<p><input style="float: right; height: 12px; padding: 0px; font-size: 10px;" type="text" id="order_<?php echo $row->ID ?>" name="order_<?php echo $row->ID ?>" value="<?php echo get_cb_order($_GET['post'],$row->ID) ?>" />
		<label style="float: right" for="order_<?php echo $row->ID ?>"><?php _e('Sort')?>:</label>
		<input <?php if (in_array($row->ID,get_contentbox_ids($_GET['post']))) {echo 'checked="checked"';} ?> type="checkbox" name="contentbox[]" id="cb_<?php echo $row->ID ?>" value="<?php echo $row->ID ?>" /> 
		<label for="cb_<?php echo $row->ID ?>"><?php echo $row->post_title ?></label></p><?php
	}	
}

//======================================
// Description: Saving the relation between contentbox and post
// Require: Post ID
Function cb_meta_save(){
global $wpdb;
	if ($_POST['ID']) { $id = $_POST['ID'];}
	else{$id = get_next_post_id();}
	$sql = "DELETE FROM $wpdb->postmeta WHERE post_id = '$id' AND meta_key = 'contentbox'";
	$wpdb->query($sql);
	if ($_POST['contentbox']) {
		foreach($_POST['contentbox'] as $contentbox) {
			$sql = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES ('$id', 'contentbox', '$contentbox ".$_POST['order_'.$contentbox]."')";
			$wpdb->query($sql);
		}
	}
}


//======================================
// Description: Returns an array with the contentbox relations
Function get_contentbox_ids($post_id){
global $wpdb;
	$cb_ids = array();
	$sql = "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = 'contentbox' AND post_id = '$post_id'";
	$row = $wpdb->get_col($sql);
		
	foreach($row as $row) {
		$id = explode(' ',$row);
		array_push($cb_ids,$id[0]);
	}
	return $cb_ids;
}



//======================================
// Description: Returns the Contentboxes category ID
Function cb_category(){
	$id = is_term('Contentbox', 'category');
	return $id['term_id'];
}


//======================================
// Description: Remove contentboxes from the loop
// @Require: query object
Function cb_remove_from_loop($query){
	$query->query_vars['category__not_in'] = cb_category();
	return $query;
}

//======================================
// Description: Removes Contentbox category from the category list
Function cb_remove_from_category($subject){
	$pattern = '&<li class="cat-item cat-item-'.cb_category().'">.*Contentbox.*li>&isU';
	$subject = preg_replace($pattern, '',$subject);
	return $subject;
}

//======================================
// Description: Removes Contentbox category from the category dropdownbox
Function cb_remove_from_dropdown($select){
	$select = str_replace('<option value="'.cb_category().'">Contentbox</option>','',$select);
	return $select;
}

//======================================
// Description: Removes Contentbox posts from adjacent
Function cb_remove_from_adjacent_join($join){
global $wpdb;
	$join = "INNER JOIN $wpdb->term_relationships AS r ON r.object_id = p.ID";
	return $join;
}

//======================================
// Description: Removes Contentbox posts from adjacent
Function cb_remove_from_adjacent_where($where){
global $wpdb;
	$where.= "and r.term_taxonomy_id != '".cb_category()."'"; 
	return $where;
}

//======================================
// Description: This function runs when plugin is activated
Function cb_install() {
global $wpdb;
	wp_create_category('Contentbox');
}

//======================================
// Description: This function run when the plugin is deactivated
Function cb_deinstall(){
global $wpdb;
	wp_delete_category(cb_category());
	
	$sql = "DELETE FROM $wpdb->postmeta WHERE meta_key = 'contentbox'";
	$wpdb->query($sql);
}

//======================================
// Description: Returns the next post ID
Function get_next_post_id(){
global $wpdb;
	$sql = "SHOW TABLE STATUS LIKE '$wpdb->posts'";
	$row = $wpdb->get_row($sql);
	return $row->Auto_increment;
}

//======================================
// Description: Returning the order number of a contentbox
// Require: Post ID
// Require: Contentbox ID
Function get_cb_order($post,$contentbox){
global $wpdb;
	$sql = "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = 'contentbox' AND post_id = '$post'";
	$col = $wpdb->get_col($sql);
	
	foreach($col as $col) {
		$meta_value = explode(" ",$col);
		
		if ($meta_value[0] == $contentbox) {
			return $meta_value[1];
		}
	}
}


//====================================== WP HOOKS
register_activation_hook(__FILE__, 'cb_install');
register_deactivation_hook(__FILE__, 'cb_deinstall');
add_action('save_post', 'cb_meta_save');
add_filter('wp_list_categories', 'cb_remove_from_category');
add_filter('wp_dropdown_cats', 'cb_remove_from_dropdown');
add_filter('get_next_post_where', 'cb_remove_from_adjacent_where');
add_filter('get_previous_post_where', 'cb_remove_from_adjacent_where');
add_filter('get_next_post_join', 'cb_remove_from_adjacent_join');
add_filter('get_previous_post_join', 'cb_remove_from_adjacent_join');
if (is_admin()) add_action('admin_menu', 'cb_add_box');
if ($_SERVER['PHP_SELF'] == '/wordpress/index.php' && empty($_GET['p']) && empty($_GET['s'])) add_filter('pre_get_posts','cb_remove_from_loop');


?>