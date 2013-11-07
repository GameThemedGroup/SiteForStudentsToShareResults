<?php
/**
 * Template Name: Manage Assignments
 * Description: Allows for the creation/deletion of assignments.
 *
 * Author: Andrey Brushchenko
 * Date: 10/6/2013
 */
get_header(); ?>

<?php
global $gtcs12_db;

$current_user = wp_get_current_user();

if($_GET['id'] != null)
{
  $courseId = $_GET['id'];
  $courses = $gtcs12_db->GetCourseByFacultyId($current_user->ID); 
}
else
{
  $courses = $gtcs12_db->GetCourseByFacultyId($current_user->ID); 
  $courseId = $courses[0]->Id;
}

if ($_GET['del']) 
{
  $assignment_ID = $_GET['del'];
  $assignment_info = get_post($assignment_ID);
  if($assignment_info) 
  {
    if($assignment_info->post_author == $current_user->ID)
    {
      wp_delete_post($assignment_ID);
      $action = 'deleted';
    }
    else 
    {
      $action = "owner error";
    }
  }
  else
  {
    $action = "found error";
  }
}

if ($_POST) 
{
  $assignmentId = $gtcs12_db->CreateAssignment($current_user->ID, $courseId, $_POST['title'], $_POST['txtDescription']);

  $authorId = $current_user->ID;

  $gtcs12_db->UploadFile($authorId, $courseId, $assignmentId, 'filImage', '.png');
  $gtcs12_db->UploadFile($authorId, $courseId, $assignmentId, 'filJar', '.jar');

  // Use AttachFileToPost here
}
?>

<!DOCTYPE html>
<html lang="en">

<?php if($action == "owner error") : ?>
  <div id="error-box">Error:You don't have ownership of that assignment</div>
<?php elseif($action == "found error") : ?>
  <div id="error-box">Error:Assignment not found</div>
<?php elseif($action == "assign error") : ?>
  <div id="error-box">Error:Assignment failed to be created</div>
<?php elseif($action == "image error") : ?>
  <div id="error-box">Error:File failed to upload</div>
<?php elseif($action == "created") : ?>
  <div id="action-box">Assignment created</div>
<?php elseif($action == "deleted") : ?>
  <div id="action-box">Assignment deleted</div>
<?php endif ?>

  <form action="" method="post" enctype="multipart/form-data">
    <div id='create-assignment-box-left'>
      <div id='pagetitle'>Create Assignment</div>
      <div id='create-assignment-field'>
        Title
        <input class='create-assignment' type="text" name="title" required><br>
      </div>
      <div id='create-assignment-field'>
        Description
        <textarea cols="25" rows="10" autocomplete="off" name="txtDescription" required></textarea>
      </div>
      <div id='create-assignment-field'>
        Sample File(.jar)
        <input class='create-assignment' type="file" name="filJar">
      </div>
      <div id='create-assignment-field'>
        Preview Image(.png)
        <input class='create-assignment' type="file" name="filImage" accept="image/png">
      </div>
      <input type="submit" value="Submit"/>
    </div>
  </form>

  <div id="options-box">
    <div id="options-title">Courses</div>
    <ul class="options">
<?php foreach($courses as $course) : ?>
<?php if($courseId  == $course->Id) : ?>
      <li class="options-selected">
        <?php echo '[' . $course->Quarter . ']' ?>
        <?php echo $course->Name ?>
      </li>
<?php else : ?> 
      <li class="options">
        <a href="<?php echo site_url('/manage-assignments/?id=' . $course->Id) ?>">
          <?php echo '[' . $course->Quarter . ']' ?>
          <?php echo $course->Name ?>
        </a>
      </li>
<?php endif ?>
<?php endforeach ?>
    </ul>
  </div> 

  <div id='table'>  
    <table class='manage-courses'>
    <div id='pagetitle'>Manage Assignments</div>
      <thead class='manage-courses'>
        <tr>
          <th>Title</th>
          <th>Date Posted</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
<?php $assignments = get_posts('&orderby=date&tag=course:' . $courseId); ?>
<?php if($assignments) : ?>
<?php foreach($assignments as $assignments) : ?> 
        <tr>
          <th><?php echo $assignments->post_title; ?></th>
          <th><?php echo date('F d, Y', strtotime($assignments->post_date)); ?></th>
          <th><a href='<?php echo site_url('/manage-assignments/?id=' . $courseId . '&del=' . $assignments->ID) ?>'>Delete</a></th></th>
        </tr>
<?php endforeach; ?>
<?php else : ?>
        <tr>
          <th>N/A</th>
          <th>N/A</th>
          <th>N/A</th>
        </tr>
<?php endif ?>
      </tbody>
    </table>
  </div>
</html>

<?php get_footer() ?>
