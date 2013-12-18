<?php
	$comment_args = array(
		'author_email' => '',
		'ID' => '',
		'karma' => '',
		'number' => 8,
		'offset' => '',
		'orderby' => '',
		'order' => 'DESC',
		'parent' => '',
		'post_id' => 0,
		'post_author' => '',
		'post_name' => '',
		'post_parent' => '',
		'post_status' => '',
		'post_type' => '',
		'status' => 'approve',
		'type' => '',
		'user_id' => '',
		'search' => '',
		'count' => false,
		'meta_key' => '',
		'meta_value' => '',
		'meta_query' => '',
	);

	$comments = get_comments($comment_args);
?>
