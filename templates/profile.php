<?php
/**
 * Template Name: User Profile
 * Description: Shows the user's information, submitted assignments, and comment history.
 *
 * Author: Andrey Brushchenko
 * Date: 10/1/2013
 */
get_header(); ?>

<?php 
	$user_info = get_userdata($_GET['user']); 

	$args = array(
		'author_email' => '',
		'ID' => '',
		'karma' => '',
		'number' => 5,
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
		'user_id' => $_GET['user'],
		'search' => '',
		'count' => false,
		'meta_key' => '',
		'meta_value' => '',
		'meta_query' => '',
	); 

	$comments = get_comments($args);
?>

<html>
<div id="profile">
	<div id='pagetitle'>
		<?php echo $user_info->user_login ?>'s Profile
	</div>   
	<?php echo get_avatar($user_info->ID, 120) ?>
	<div id='profilemeta'>
 		<b>Name </b><?php echo $user_info->first_name . ' ' . $user_info->last_name; ?>
	</div>
	<div id='profilemeta'>
 		<b>Email </b><?php echo $user_info->user_email ?>
	</div>
	<div id='profilemeta'>
 		<b>Class </b>CSS 161
		<b>Quarter </b>Autumn
		<b>Year </b>2013
	</div>
	<div id='profilemeta'>
 		<b>Professor </b>Kelvin Sung
	</div>
</div>

<div id="assignmentbox">
	<table class="assignment-table">
		<thead class="assignment-table">
			<tr>
				<th class="assignment-table">Submitted Assignment</th>
				<th class="assignment-table">Date</th>
			</tr>
		</thead>
		<tbody class="assignment-table">
			<tr>
				<th class="assignment-table"><a>Assignment Name 134  3 235 2 3532 5 352 52 522 5 352 52 35235 afaf afa fa f adada dadd ad ada dada dad</a></th>
				<th class="assignment-table">7/5/2013</th>
			</tr>
			<tr>
				<th class="assignment-table"><a>Assignment Name 2</a></th>
				<th class="assignment-table">7/5/2013</th>
			</tr>
			<tr>
				<th class="assignment-table"><a>Assignment Name 3</a></th>
				<th class="assignment-table">7/5/2013</th>
			</tr>
			<tr>
				<th class="assignment-table"><a>Assignment Name 4</a></th>
				<th class="assignment-table">7/5/2013</th>
			</tr>
			<tr>
				<th class="assignment-table"><a>Assignment Name 5</a></th>
				<th class="assignment-table">7/5/2013asdasddsda</th>
			</tr>
		</tbody>
	</table>
</div><!-- assignmentbox -->

<div id='profile-recent-comments'>
	<div id='header1'>Recent Comments</div>
	<?php foreach($comments as $comment) {
		echo "<div id='profile-comment-box'>";
			echo "<div id='profile-comment-content'>" . $comment->comment_content . "</div>";
			echo "<div id='profile-comment-meta'><a href=\"" . site_url('/?p=') . $comment->comment_post_ID . "\">" . 
				get_the_title($comment->comment_post_ID) . "</a></div>";
			echo "<div id='profile-comment-meta'>" . date('F d, Y', strtotime($comment->comment_date))  . "</div>";
		echo "</div>";
		}
	?>
</div>
</html>

<?php get_footer(); ?>