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
$userInfo = get_userdata($_GET['user']); 
$comments_per_page = 8;
$course = $gtcs12_db->GetCourseByStudentId($userInfo->ID);
$professor = get_user_by('id', $course[0]->FacultyId);
$currentUser = wp_get_current_user();

if($currentUser->ID == $userInfo->ID)
  $isOwner = true;


if($_GET['view'] == '' || $_GET['comments']) 
{
  $args = array(
    'number' => 5,
    'order' => 'DESC',
    'parent' => '',
    'post_id' => 0,
    'status' => 'approve',
    'user_id' => $userInfo->ID,
    'count' => false,
  ); 

  $comments = get_comments($args);
  $count_comments = count($comments);
  $page_count = ceil($count_comments / $comments_per_page);

  if($_GET['x'])
    $current_page = $_GET['x'];
  else
    $current_page = 1;
}
?>

<!DOCTYPE html>
<html lang="en">
  <div id="profile">
    <div id='pagetitle'>
      <?php echo $userInfo->user_login ?>'s Profile
    </div>   
    <?php echo get_avatar($userInfo->ID, 120) ?>
    <div id='profilemeta'>
      <b>Name </b><?php echo $userInfo->first_name . ' ' . $userInfo->last_name; ?>
    </div>
    <div id='profilemeta'>
      <b>Email </b><?php echo $userInfo->user_email ?>
    </div>
    <div id='profilemeta'>
      <b>Class </b><?php echo $course[0]->Name ?>
      <b>Quarter </b><?php echo $course[0]->Quarter ?>
      <b>Year </b><?php echo $course[0]->Year ?>
    </div>
    <div id='profilemeta'>
      <b>Professor </b><?php echo $professor->first_name . ', ' . $professor->last_name ?>
    </div>
  </div>

<?php if($isOwner) : ?>
  <div id="profile-options">
    <div id="profile-options-title">Options</div>
    <ul class="profile-options">
      <li class="profile-options">
        <a href="<?php echo site_url('/manage-profile/?user=' . $currentUser->ID) ?>">Edit Profile Information</a>
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
      <a href="<?php echo site_url('/profile/?user=' . $userInfo->ID) ?>">Comments</a>
    </div>
    <?php if($_GET['view'] == 'submissions') : ?>
      <div id="profile-menu-tab-selected">
    <?php else : ?>
      <div id="profile-menu-tab">
    <?php endif ?>
      <a href="<?php echo site_url('/profile/?user=' . $userInfo->ID . '&view=submissions') ?>">Submissions</a>
    </div>
  </div>

<?php if($_GET['view'] == '' || $_GET['view'] == 'comments'): ?>
<?php if($comments) : ?>
    <div class="activityfeed">
<?php $results_start = ($current_page - 1) * $comments_per_page;
$results_end = $results_start + $comments_per_page; 
if($results_end > $count_comments)
  $results_end = $count_comments;
?>
<?php for ($i = $results_start; $i < $results_end; $i++) : ?> 
        <div class="commentbox">
        <?php echo get_avatar($comments[$i]->user_id, 92) ?>
          <div id="commentcontent">
            <?php echo $comments[$i]->comment_content; ?>
          </div>
          <div id="commentmetabox">
            <div id="commentmeta">
            <a href="<?php echo site_url('/profile/?user=') . $comments[$i]->user_id ?>"> 
              <?php echo $comments[$i]->comment_author ?> 
            </a> 
            </div>
            <div id="commentmeta">
              <?php echo date('F d, Y', strtotime($comments[$i]->comment_date)) ?> 
            </div>
            <div id="commentmeta">
            <a href="<?php echo site_url('/?p=') . $comments[$i]->comment_post_ID ?>">
              <?php echo get_post($comments[$i]->comment_post_ID)->post_title; ?>
            </a>  
            </div>
            <?php if(current_user_can('moderate_comments')): ?>
              <div id="commentmeta">
                Delete
              </div>
            <?php endif ?>
          </div>
        </div>
<?php endfor; ?>
      </div>
<?php else : ?>
    <div id="empty-comment">This user has no comments</div>
<?php endif ?>


<?php if($comments) : ?>
    <div id="resultspages">
      Page
<?php
$count = 1;
while($count <= $page_count) :
  if($count == $current_page)
  {
    echo "[";
  }
echo "<a href=" . site_url('/profile/?user=' . $userInfo->ID . "&x=" . $count) . ">" . $count. "</a>";
if($count == $current_page)
{
  echo "]";
}
$count++;
endwhile;
?>
    </div>
<?php endif ?>

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
<?php $submissions = $gtcs12_db->GetSubmissions($userInfo->ID) ?>
<?php if($submissions) : ?>
<?php foreach($submissions as $submission) : ?>
          <tr>
            <th>
              <a href="<?php echo site_url('/?p=') . $submission->SubmissionId ?>"><?php echo $submission->AssignmentName ?></a>
            </th>
            <th><?php echo date('F d, Y', strtotime($submission->Date)) ?></th>
          </tr>
<?php endforeach ?>
<?php else : ?>
          <tr>
            <th>N/A</th>
            <th>N/A</th>
          </tr>
<?php endif ?>
        </tbody>
      </table>
    </div>
<?php endif ?>
</html>

<?php get_footer(); ?>
