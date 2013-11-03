<?php
/**
 * The Template for displaying java applets 
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

get_header(); ?>

<?php 
	if($_POST['comment']) 
	{
		$current_user = wp_get_current_user();
		
		$args = array(
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
		wp_insert_comment($args);
	}
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

<object type="application/x-java-applet" height="350" width="500">
  <param name="code" value="rslj.school.hangman.HangmanApplet.class" />
  <param name="archive" value="<?php echo wp_get_attachment_url($post->ID); ?>" />
  Java is not enabled on your computer!
</object>
    
    <div id="meta">
      <br />
      <?php the_attachment_link($jar_file->ID, true); ?>
		</div><!-- meta -->
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
