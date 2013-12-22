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

<?php include_once(get_template_directory() . '/logic/index.php'); ?>

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
<?php
  global $gtcs_Categories;
  $args = array(
    'posts_per_page' => 5,
    'category' => $gtcs_Categories['submission']
  );
?>
    <?php $recentPosts = get_posts($args); ?>
    <?php foreach ($recentPosts as $post) : setup_postdata($post); ?>
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
		<?php endforeach; ?>
	</div><!-- project-sidebar -->
</html>

<?php get_footer(); ?>
