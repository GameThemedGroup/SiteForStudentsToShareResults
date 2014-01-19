<?php
/**
 * Shows recent and top projects
 */
get_header();
?>

<?php //include_once(get_template_directory() . '/logic/index.php'); ?>

<?php
  global $post;
  global $gtcs_Categories;
  $postArgs = array(
    'category' => $gtcs_Categories['submission'],
    'order' => 'DESC',
    'orderby' => 'date',
    'status' => 'approve',
    'posts_per_page' => 10
  );

  $recentSubmissions = get_posts($postArgs);
?>

<div id="recent-submission-feed">
  <div id="recent-submission-feed-title">Recent Submissions</div>
  <?php foreach ($recentSubmissions as $post) : setup_postdata($post); ?>
    <div id="project-sidebar-box">
      <div id="project-sidebar-image">
        <?php if(has_post_thumbnail()): ?>
          <?php the_post_thumbnail(array(100,100)); ?>
        <?php else: ?>
          <img src="<?php bloginfo('template_directory'); ?>/images/blank-project.png"
            width="100" height="100" />
        <?php endif; ?>
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
</div>

<?php //wp_reset_postdata(); ?>

<?php
  //query_posts(array(
  $args = array(
    'cat' => $gtcs_Categories['submission'],
    'meta_key' => 'ratings_average',
    'orderby' => 'meta_value_num',
    'order' => 'DESC',
    'posts_per_page' => 5
  );
  $topPosts = get_posts($args);
?>

<div id="project-sidebar">
  <div id="project-sidebar-title"> Top Projects </div>
  <?php //while (have_posts()): the_post(); ?>
  <?php foreach ($topPosts as $post) : setup_postdata($post); ?>
    <div id="project-sidebar-box">
      <div id="project-sidebar-image">
        <?php if(has_post_thumbnail()) {?>
          <?php the_post_thumbnail(array(100,100)); ?>
        <?php } else { ?>
          <img src="<?php bloginfo('template_directory'); ?>/images/blank-project.png"
            width="100" height="100" />
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
        <div><?php echo the_ratings_results(get_the_ID()); ?></div>
<?php //if(function_exists('the_ratings')) { the_ratings(); } ?>
      </div>
    </div>
  <?php //endwhile; ?>
  <?php endforeach; ?>
</div><!-- project-sidebar -->

<?php get_footer(); ?>
