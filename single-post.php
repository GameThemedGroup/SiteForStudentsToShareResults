<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

get_header();
?>

<?php
$doPlay = ifsetor($_GET['play'], false);

if(isset($_POST['comment']))
{
  $current_user = wp_get_current_user();

  $args = array(
    'comment_post_ID' => get_the_ID(),
    'comment_author' => $current_user->display_name,
    'comment_author_email' => $current_user->user_email,
    'comment_content' => $_POST['comment'],
    'user_id' => $current_user->ID,
    'comment_author_IP' => getenv("REMOTE_ADDR"),
    'comment_date' => $time,
    'comment_approved' => 1 );

  wp_insert_comment($args);
}

$attachment_query = array(
  'post_type'   => 'attachment',
  'meta_key'    => 'type',
  'meta_value'  => 'jar',
  'post_status' => 'any',
  'post_parent' => $post->ID,
);

$jarQuery = $attachment_query;
$jarQuery['meta_key']  = 'type';
$jarQuery['meta_value']  = 'jar';

$jarAttachments = get_posts($jarQuery);
$jarFile = $jarAttachments[0];

$imageQuery = $attachment_query;
$imageQuery['meta_key']  = 'type';
$imageQuery['meta_value']  = 'image';

$imageList = get_posts($imageQuery);

$comment_args = array('post_id' => get_the_ID());
$comments = get_comments($comment_args);
?>

<?php the_post(); ?>
<div class="projectfull">
  <div id="pagetitle"><?php echo $post->post_title; ?></div>
  <div id='header1'>by <?php the_author(); ?></div>

  <div id="meta">
    <div id="description"><?php echo $post->post_content; ?></div>
  </div>

  <?php the_ratings(); ?>
  <?php include "share-box.php"; ?>

<!-- Jar Container -->
  <div class="Jar-Container">
    <?php if($doPlay): ?>
      <object type="application/x-java-applet" height="350" width="500">
        <param name="code" value="rslj.school.hangman.HangmanApplet.class" />
        <param name="archive" value="<?php echo wp_get_attachment_url($jarFile->ID); ?>" />
        Java is not enabled on your computer!
      </object>
    <?php else: ?>
      <a class="Jar-Link" href=" <?php echo the_permalink() . '?play=true'; ?>">
        <div id="container_button">
          <div id="hole">
            <div id="button">
              <div id="triangle"></div>
              <div id="lighter_triangle"></div>
              <div id="darker_triangle"></div>
            </div>
          </div>
        </div>
      </a>
    <?php endif; ?>
  </div>
<!-- Jar Container -->

<!-- Comment List -->
  <div class="projectcomments">
    <div id="header1">All Comments</div>
    <?php foreach($comments as $comment) : ?>
      <div class="commentbox">
      <?php echo get_avatar($comment->user_id, 92) ?>
        <div id="commentcontent">
          <?php echo $comment->comment_content; ?>
        </div>
        <div id='commentmetabox'>
          <div id="commentmeta">
            <a href="<?php echo site_url("/profile/?user={$comment->user_id}"); ?>">
              <?php echo $comment->comment_author ?>
            </a>
          </div>
          <div id="commentmeta">
            <?php echo date('F d, Y', strtotime($comment->comment_date)) ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<!-- Comment List -->

<!-- Comment Submission Form -->
  <?php if($post->comment_status) ?>
  <div class="postcomment">
    <form method="post">
      <textarea cols="25" rows="4" autocomplete="off" name="comment" required></textarea>
      <input type="hidden" name="time_stamp" value=<?php $time ?>>
      <input type="Submit" name="Submit"/>
    </form>
  </div>
<!-- Comment Submission Form -->

</div><!-- projectfull -->

<!-- Sidebar -->
<div id="project-sidebar">
  <div id="project-sidebar-title"> Images </div>
  <?php foreach($imageList as $image): ?>
    <div id="project-sidebar-box">
      <div id="project-sidebar-image">
        <?php the_attachment_link($image->ID); ?>
      </div>
    </div>
  <?php endforeach; ?>
  <?php wp_reset_postdata(); ?>
</div>
<!-- Sidebar -->

<?php get_footer(); ?>
