<?php
/**
 * Template Name: User Profile
 * Description: Shows the user's information, submitted assignments, and comment history.
 *
 * Author: Andrey Brushchenko
 * Date: 11/11/2013
 */
get_header(); ?>

<?php include_once(get_template_directory() . '/logic/profile.php'); ?>

<div id='profile-title'>
  <?php echo $user->user_login; ?>'s Profile
</div>

<!-- Options Menu -->
<?php if ($isOwner): ?>
  <div id="sidebar-menu">
    <div id="sidebar-menu-title">Options</div>
    <ul class="sidebar-menu">
      <li class="sidebar-menu">
        <a href="<?php echo site_url('/manage-profile/?user=' . $user->ID) ?>">
          <p class="sidebar-menu-top">Edit Profile</p>
        </a>
      </li>
    </ul>
  </div>
<?php endif; ?>
<!-- Options Menu -->

<!-- Profile Display -->
<div id="profile">
  <?php echo get_avatar($user->ID, 120); ?>
  <div id='profilemeta'>
      <b>Name </b><?php echo "{$user->first_name} {$user->last_name}"; ?>
  </div>
  <div id='profilemeta'>
      <b>Email </b><?php echo $user->user_email; ?>
  </div>

  <?php if($showStudentInfo): ?>
    <div id='profilemeta'>
        <b>Class </b><?php echo $course[0]->Name ?>
        <b>Quarter </b><?php echo $course[0]->Quarter ?>
        <b>Year </b><?php echo $course[0]->Year ?>
    </div>
    <div id='profilemeta'>
        <b>Professor </b><?php echo $professor->first_name . ', ' . $professor->last_name ?>
    </div>
  <?php elseif($showProfessorInfo) : ?>
    <div id='profilemeta'>
            <b>Courses</b><br>
      <?php
        include_once(get_template_directory() . '/common/courses.php');
        $courses = GTCS_Courses::GetCourseByFacultyId($user->ID) ?>
      <?php foreach($courses as $course) : ?>
      <?php echo $course->Name ?>
      <?php endforeach ?>
    </div>
  <?php endif ?>
</div>
<!-- Profile Display -->

<!-- Profile Menu -->
<div id="profile-menu">

  <?php if ($view == 'submissions'): ?>
    <a class="profile-menu-tab-selected"
      href="<?php echo site_url("/profile/?user={$user->ID}&view=submissions"); ?>">
      Submissions
    </a>
  <?php else: ?>
    <a class="profile-menu-tab"
      href="<?php echo site_url("/profile/?user={$user->ID}&view=submissions"); ?>">
      Submissions
    </a>
  <?php endif; ?>

  <?php if ($view == 'comments'): ?>
    <a class="profile-menu-tab-selected"
      href="<?php echo site_url("/profile/?user={$user->ID}&view=comments"); ?>">
      Comments
    </a>
  <?php else: ?>
    <a class="profile-menu-tab"
      href="<?php echo site_url("/profile/?user={$user->ID}&view=comments"); ?>">
      Comments
    </a>
  <?php endif; ?>

</div>
<!-- Profile Menu -->


<?php if ($view == 'comments' || $view = ''): ?>
  <?php if (sizeof($commentList) == 0): ?>
    <div id="empty-comment">This user has no comments</div>
  <?php else: ?>
  <!-- Comments -->
  <div id="profile-comments">

    <?php foreach ($commentList as $comment): ?>
      <!-- Comment Box -->
      <div class="commentbox">
        <div id="comment-image">
            <img src ="<?php echo $comment->thumbnail; ?>"
              width="100" height="100" />
        </div>

        <div id="commentcontent">
            <?php echo $comment->comment_content; ?></div>

        <!-- Comment Metabox -->
        <div id="commentmetabox">

          <div id="commentmeta">
            <a href="<?php echo site_url("/?p={$comment->comment_post_ID}"); ?>">
              <?php echo $comment->parentTitle; ?></a>
          </div>

          <div id="commentmeta">
            <?php echo date('F d, Y', strtotime($comment->comment_date)); ?>
          </div>

          <div id="commentmeta">
            <a href="<?php echo site_url("/profile/?user={$comment->user_id}"); ?>">
              <?php echo $comments->comment_author ?>
            </a>
          </div>

          <?php if(current_user_can('moderate_comments')): ?>
            <div id="commentmeta">Delete</div>
          <?php endif ?>

        </div>
        <!-- Comment Metabox -->

      </div>
      <!-- Comment Box -->
    <?php endforeach; ?>

  </div>
  <!-- Comments -->

  <?php endif; ?>
<?php endif; ?>

<?php if ($view == 'submissions' || $view == ''): ?>

  <?php if (!$submissions) : ?>
    <div id="empty-comment">This user has no submissions</div>
  <?php else: ?>
    <?php foreach ($submissions as $submission): ?>

      <!-- Profile Submission Box -->
      <div class="profile-submission-box">

        <div class="profile-submission-tab-left">
          <?php if(has_post_thumbnail($submission->SubmissionId)): ?>
            <?php echo get_the_post_thumbnail($submission->SubmissionId, array(50,50)); ?>
          <?php else: ?>
          <img src="<?php echo bloginfo('template_directory') . "/images/blank-project.png"; ?>"
            width="50" height="50" />
          <?php endif; ?>
        </div>

        <div class="profile-submission-tab-center">
          <a href="<?php echo site_url("/?p=$submission->SubmissionId"); ?>">
            <?php echo $submission->AssignmentName ?>
          </a>
        </div>

        <div class="profile-submission-tab-right">
          Submitted on <b><?php echo date('F d, Y', strtotime($submission->Date)); ?></b>
        </div>

      </div>
      <!-- Profile Submission Box -->

    <?php endforeach; ?>
  <?php endif; ?>
<?php endif; ?>

<?php get_footer(); ?>
