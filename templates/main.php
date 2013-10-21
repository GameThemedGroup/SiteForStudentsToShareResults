<?php
/**
 * Template Name: Main Page
 * Description: Shows recent comments and top projects
 *
 * Author: Andrey Brushchenko
 * Date: 10/19/2013
 */
get_header(); 
?>

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

<!DOCTYPE html>
<html lang="en">
	<div class="activityfeed">
	<div id="activityfeed-title">Recent Activity</div>
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
				</div><!-- #meta -->
			</div><!-- .activitybox -->
		<?php endforeach; ?>
	</div><!-- .activityfeed -->

	<div id="project-sidebar">
		<div id="project-sidebar-title"> Top Projects </div>
		<?php query_posts('posts_per_page=5'); ?>
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<div id="project-sidebar-box">
				<div id="project-sidebar-image">
					<?php if(has_post_thumbnail()) {?>
						<?php the_post_thumbnail(array(100,100)); ?>
					<?php } else { ?> 
						<img src="<?php bloginfo('template_directory'); ?>/images/blank-project.png" width="100" height="100" />
					<?php } ?>
				</div>
				<div id="project-sidebar-name">
					<a href="<?php echo site_url('/?p=') . get_the_ID() ?>">
						<?php the_title() ?>
					</a>
				</div>
				<div id="project-sidebar-meta">
					by
					<a href="<?php echo site_url('/profile/?user='); echo the_author_meta('ID')?>"> 
						<?php the_author() ?> 
					</a>
				</div>
				<div id="project-sidebar-meta">
					<?php if(function_exists('the_ratings')) { the_ratings(); } ?>
				</div>
			</div>
		<?php endwhile; else: ?>
		<?php endif; ?>
	</div><!-- project-sidebar -->
</html>

<?php get_footer(); ?>