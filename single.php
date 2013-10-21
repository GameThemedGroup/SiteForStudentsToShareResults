<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

get_header(); ?>

<?php 
	$time = current_time('mysql');
	echo $_POST['time_stamp'];
	if($_POST['comment']) {
		$current_user = wp_get_current_user();
		
		$data = array(
    		'comment_post_ID' => get_the_ID(),
    		'comment_author' => $current_user->display_name,
    		'comment_author_email' => $current_user->user_email,
    		'comment_author_url' => '',
    		'comment_content' => $_POST['comment'],
    		'comment_type' => '',
    		'comment_parent' => 0,
   		 	'user_id' => $current_user->ID,
    		'comment_author_IP' => getenv("REMOTE_ADDR"),
    		'comment_agent' => '',
    		'comment_date' => $time,
    		'comment_approved' => 1 );

		//print_r(array_values($data));
		wp_insert_comment($data);
	}

	//echo 'x=' . $_POST['comment'];
?>

<!DOCTYPE html>
<html lang="en">
	<?php the_post(); ?>
	<div class="projectfull">	
		<div id="pagetitle">
			<?php echo $post->post_title; ?>
		</div>
		<div id='header1'>
			by <?php the_author(); ?> 
		</div>
		
		<div id="meta">
			<?php the_post_thumbnail( array(500,400) );?>
			<?php include "share-box.php" ?>
		</div>
		<?php if(function_exists('the_ratings')) { the_ratings(); } ?>
		<div id="description">
			<?php echo $post->post_content;  ?>
		</div>
		<div class="projectcomments">
			<div id="header1">All Comments</div>
			<?php 
				$args = array('post_id' => $_GET['p']);
				$comments = get_comments($args); 
			?>
			<?php foreach($comments as $comment) : ?>		
				<div class="commentbox">
				<?php echo get_avatar($comment->user_id, 92) ?>
					<div id="commentcontent">
						<?php echo $comment->comment_content; ?>
					</div>
					<div id='commentmetabox'>
						<div id="commentmeta">
							<a href="<?php echo site_url('/profile/?user=') . $comment->user_id ?>">
								<?php echo $comment->comment_author ?> 
							</a> 
						</div>
						<div id="commentmeta">
							<?php echo date('F d, Y', strtotime($comment->comment_date)) ?> 	
						</div>
					</div>
				</div><!-- .activitybox -->
			<?php endforeach; ?>
		</div><!-- projectcomments -->

		<?php if($post->comment_status) ?>
		<div class="postcomment">
			<form method="post">
				<textarea cols="25" rows="4" autocomplete="off" name="comment" required></textarea>
				<input type="hidden" name="time_stamp" value=<?php $time ?>>
				<input type="Submit" name="Submit"/>
			</form>   
		</div>
	</div><!-- projectfull -->

	<div id="project-sidebar">
		<div id="project-sidebar-title"> Related Projects </div>
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