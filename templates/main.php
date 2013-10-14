<?php
/**
 * Template Name: Main Page
 * Description: Shows recent comments and top projects
 *
 * Author: Andrey Brushchenko
 * Date: 10/6/2013
 */
get_header(); 
?>

<html>
<div class="activityfeed">
	<div id="pagetitle">Activity Feed</div>
	<?php $comments = get_comments('number=8'); ?>
	<?php foreach($comments as $comment) : ?>	
		<div class="commentbox">
		<?php echo get_avatar($comment->user_id, 92) ?>
			<div id="commentcontent">
				<?php echo $comment->comment_content; ?>
			</div>
			<div id="commentmeta">
				<a href="<?php echo site_url('/profile/?user=') . $comment->user_id ?>"> 
					<?php echo $comment->comment_author ?> 
				</a> |
				<?php echo date('F d, Y', strtotime($comment->comment_date)) ?> |
				<a href="<?php echo site_url('/?p=') . $comment->comment_post_ID ?>">
					<?php echo get_post($comment->comment_post_ID)->post_title; ?>
				</a> 			
			</div><!-- #meta -->
		</div><!-- .activitybox -->
	<?php endforeach; ?>
</div><!-- .activityfeed -->

<div class="projects">
	<div id="pagetitle">Top Projects</div>
	<?php query_posts('posts_per_page=5'); ?>
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<div class="projectbox">
			<div id="image"> 
				<?php if(has_post_thumbnail()) {?>
					<?php the_post_thumbnail(array(140,140)); ?>
				<?php } else { ?> 
					<img src="<?php bloginfo('template_directory'); ?>/images/blank-project.png" width="140" height="140" />
				<?php } ?>
			</div>
			<div id="name">
				<a href="<?php echo site_url('/?p=') . get_the_ID() ?>">
					<?php the_title() ?>
				</a>
			</div>	
			<div id="meta">
				<a href="<?php echo site_url('/profile/?user='); echo the_author_meta('ID')?>"> 
					<?php the_author() ?> 
				</a> | 
				<?php the_time('m/d/y') ?> | 
				<?php if(function_exists('the_ratings')) { the_ratings(); } ?>
			</div>		
			<div id="description">
				<?php the_content() ?>
			</div>
		</div><!-- projectbox -->	
	<?php endwhile; else: ?>
	<?php endif; ?>
</div><!-- projects -->
</html>

<?php get_footer(); ?>