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

<!DOCTYPE html>
<html lang="en">
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
	
	<?php if( $_GET['user'] == $user_info->ID) : ?>
		<div id="profile-options">
			<div id="profile-options-title">Options</div>
			<ul class="profile-options">
				<li class="profile-options">
					<a href="<?php echo site_url('/manage-profile/?user=' . $_GET['user']) ?>">Edit Profile</a>
				</li>
			</ul>
		</div>
	<?php endif ?>

	<div id="profile-menu">
		<?php if($_GET['view'] == '' || $_GET['view'] == 'comments') : ?>
			<div id="profile-menu-tab-selected">
		<?php else : ?>
			<div id="profile-menu-tab">
		<?php endif ?>
			<a href="<?php echo site_url('/profile/?user=' . $_GET['user'] . '&view=comments') ?>">Comments</a>
		</div>
		<?php if($_GET['view'] == 'submissions') : ?>
			<div id="profile-menu-tab-selected">
		<?php else : ?>
			<div id="profile-menu-tab">
		<?php endif ?>
			<a href="<?php echo site_url('/profile/?user=' . $_GET['user'] . '&view=submissions') ?>">Submissions</a>
		</div>
	</div>
	
	<?php if($_GET['view'] == '' || $_GET['view'] == 'comments'): ?>
		<div class="activityfeed">
			<?php foreach($comments as $comment) : ?>	
				<div class="commentbox">
				<?php echo get_avatar($comment->user_id, 92) ?>
					<div id="commentcontent">
						<?php echo $comment->comment_content; ?>
					</div>
					<div id="commentmetabox">
						<div id="commentmeta">
						<a href="<?php echo site_url('/profile/?user=') . $comment->user_id ?>"> 
							<?php echo $comment->comment_author ?> 
						</a> 
						</div>
						<div id="commentmeta">
							<?php echo date('F d, Y', strtotime($comment->comment_date)) ?> 
						</div>
						<div id="commentmeta">
						<a href="<?php echo site_url('/?p=') . $comment->comment_post_ID ?>">
							<?php echo get_post($comment->comment_post_ID)->post_title; ?>
						</a> 	
						</div>
						<?php if(current_user_can('moderate_comments')): ?>
							<div id="commentmeta">
								Delete
							</div>
						<?php endif ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php elseif($_GET['view'] == 'submissions' ) : ?>
		<div id="table">
			<table>
				<thead>
					<tr>
						<th>Submitted Assignment</th>
						<th>Date</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th><a>Assignment Name 134  3 235 2 3532 5 352 52 522 5 352 52 35235 afaf afa fa f adada dadd ad ada dada dad</a></th>
						<th>7/5/2013</th>
					</tr>
					<tr>
						<th><a>Assignment Name 2</a></th>
						<th>7/5/2013</th>
					</tr>
					<tr>
						<th><a>Assignment Name 3</a></th>
						<th>7/5/2013</th>
					</tr>
					<tr>
						<th><a>Assignment Name 4</a></th>
						<th>7/5/2013</th>
					</tr>
					<tr>
						<th><a>Assignment Name 5</a></th>
						<th>7/5/2013asdasddsda</th>
					</tr>
				</tbody>
			</table>
		</div>
	<?php endif ?>
</html>

<?php get_footer(); ?>