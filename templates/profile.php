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

<!DOCTYPE html>
<html lang="en">

<?php if(is_user_logged_in()) : ?>
  <div id='profile-title'>
    <?php echo $userInfo->user_login ?>'s Profile
  </div>

<?php if($isOwner) : ?>
  <div id="sidebar-menu">
    <div id="sidebar-menu-title">Options</div>
    <ul class="sidebar-menu">
      <li class="sidebar-menu">
        <a href="<?php echo site_url('/manage-profile/?user=' . $currentUser->ID) ?>">
          <p class="sidebar-menu-top">Edit Profile</p>
        </a>
      </li>
    </ul>
  </div>
<?php endif ?>

    <div id="profile">
<?php echo get_avatar($userInfo->ID, 120) ?>
        <div id='profilemeta'>
            <b>Name </b><?php echo $userInfo->first_name . ' ' . $userInfo->last_name; ?>
        </div>
        <div id='profilemeta'>
            <b>Email </b><?php echo $userInfo->user_email ?>
        </div>
<?php if($showStudentInfo) : ?>
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
        $courses = GTCS_Courses::GetCourseByFacultyId($currentUser->ID) ?>
      <?php foreach($courses as $course) : ?>
      <?php echo $course->Name ?>
      <?php endforeach ?>
    </div>
<?php endif ?>
    </div>

    <div id="profile-menu">
<?php if($showSubmissions) : ?>
<?php   if($_GET['view'] == '' || $_GET['view'] == 'submissions') : ?>
      <a class="profile-menu-tab-selected" href="<?php echo site_url('/profile/?user=' . $userInfo->ID . '&view=submissions') ?>">Submissions (<?php echo $submissionCount ?>)</a>
<?php   else : ?>
      <a class="profile-menu-tab" href="<?php echo site_url('/profile/?user=' . $userInfo->ID . '&view=submissions') ?>">Submissions (<?php echo $submissionCount ?>)</a>
<?php   endif ?>
<?php endif ?>

<?php if($_GET['view'] == 'comments' || $showSubmissions == false) : ?>
      <a class="profile-menu-tab-selected" href="<?php echo site_url('/profile/?user=' . $userInfo->ID . '&view=comments') ?>">Comments (<?php echo $commentCount ?>)</a>
<?php   else : ?>
      <a class="profile-menu-tab" href="<?php echo site_url('/profile/?user=' . $userInfo->ID . '&view=comments') ?>">Comments (<?php echo $commentCount ?>)</a>
<?php endif ?>
    </div>


<?php if($_GET['view'] == 'comments' || ($_GET['view'] == '' && $showSubmissions == false)) : ?>
<?php   if($comments) : ?>
  <div id="profile-comments">
<?php $results_start = ($current_page - 1) * $comments_per_page;
      $results_end = $results_start + $comments_per_page;
      if($results_end > $commentCount)
        $results_end = $commentCount;
?>
<?php     for ($i = $results_start; $i < $results_end; $i++) : ?>
                <div class="commentbox">
                  <div id="comment-image">
        <?php if(has_post_thumbnail($comments[$i]->comment_post_ID)) : ?>
           <?php echo get_the_post_thumbnail($comments[$i]->comment_post_ID, array(100,100)) ?>
        <?php else : ?>
          <img src="<?php bloginfo('template_directory'); ?>/images/blank-project.png" width="100" height="100" />
        <?php endif ?>
                  </div>
                  <div id="commentcontent">
                      <?php echo $comments[$i]->comment_content; ?>
                  </div>
                  <div id="commentmetabox">
                    <div id="commentmeta">
                      <a href="<?php echo site_url('/?p=') . $comments[$i]->comment_post_ID ?>">
                        <?php echo get_post($comments[$i]->comment_post_ID)->post_title; ?>
                      </a>
                    </div>
                    <div id="commentmeta">
                      <?php echo date('F d, Y', strtotime($comments[$i]->comment_date)) ?>
                    </div>
                    <div id="commentmeta">
                      <a href="<?php echo site_url('/profile/?user=') . $comments[$i]->user_id ?>">
                        <?php echo $comments[$i]->comment_author ?>
                      </a>
                    </div>
                    <?php if(current_user_can('moderate_comments')): ?>
                      <div id="commentmeta">Delete</div>
                    <?php endif ?>
                  </div>
                </div>
<?php     endfor; ?>
  </div>


  <div id="resultspages">
  Page
<?php
$count = 1;
while($count <= $page_count) :
  if($count == $current_page)
    echo "<u>" . $count . "</u>";
  else
    echo "<a href=" . site_url('/profile/?user=' . $userInfo->ID . "&x=" . $count) . ">" . " " .  $count . "</a>";
$count++;
endwhile;
?>
      </div>
<?php   else : ?>
        <div id="empty-comment">This user has no comments</div>
<?php   endif ?>
<?php endif ?>


<?php if($showSubmissions) : ?>
<?php   if($_GET['view'] == 'submissions' || $_GET['view'] == '') : ?>
<?php     if($submissions) : ?>
<?php       foreach($submissions as $submission) : ?>
  <div class="profile-submission-box">
    <div class="profile-submission-tab-left">
      <?php if(has_post_thumbnail($submission->SubmissionId)) : ?>
      <?php echo get_the_post_thumbnail($submission->SubmissionId, array(50,50)) ?>
      <?php else :?>
      <img src="<?php bloginfo('template_directory'); ?>/images/blank-project.png" width="50" height="50" />
      <?php endif ?>
    </div>
    <div class="profile-submission-tab-center">
      <a href="<?php echo site_url('/?p=') . $submission->SubmissionId ?>"><?php echo $submission->AssignmentName ?></a>
    </div>
    <div class="profile-submission-tab-right">
      Submitted on <b><?php echo date('F d, Y', strtotime($submission->Date)) ?></b>
    </div>
  </div>
<?php       endforeach ?>
<?php       else : ?>
    <div id="empty-comment">This user has no submissions</div>
<?php       endif ?>
<?php     endif ?>
<?php   endif ?>
<?php   else : ?>
  <div id="error-box">  You must be logged in to view this page</div>
<?php endif ?>
</html>

<?php get_footer(); ?>
