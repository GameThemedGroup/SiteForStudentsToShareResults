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
$current_user = wp_get_current_user(); 
$courses = $gtcs12_db->GetCourseByFacultyId($current_user->ID); 

if($_GET['courseid'] != null)
  $courseId = $_GET['courseid'];
else
  $courseId = $courses[0]->Id;

if($_GET['op'] == 'delete') // delete assignment
{
  if($_GET['assignid'])
  {
    $assignmentId = $_GET['assignid'];
    $assignment_info = get_post($assignmentId);
    if($assignment_info)
    {
      if($assignment_info->post_author == $current_user->ID)
      { 
        wp_delete_post($assignmentId);
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
}
else if($_GET['op'] == 'edit') // edit assignment(page loads with values from existing assignment)
{
  if($_GET['assignid'])
  {
    $assignmentId = $_GET['assignid'];
    $assignment = $gtcs12_db->GetAssignment($assignmentId);
  }
}

if($_POST['op'] == 'update') // update assignment
{
  echo
    $gtcs12_db->UpdateAssignment($_POST['assignid'], $current_user->ID, $courseId, $_POST['inptTitle'], $_POST['txtDescription']);
  $action = "assignment edited";
}
else if($_POST['op'] == 'create') // create assignment
{
  $assignmentId = $gtcs12_db->CreateAssignment($current_user->ID, $courseId, $_POST['inptTitle'], $_POST['txtDescription']);
  $authorId = $current_user->ID;

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

    <form action="<?php echo site_url('/manage-assignments/') ?>" method="post" enctype="multipart/form-data">
        <div id='create-assignment-box-left'>
            <div id='pagetitle'>
        <?php echo ($_GET['op'] == 'edit' ? "Edit Assignment" : "Create Assignment"); ?>   
      </div>
            <div id='create-assignment-field'>
                Title
                <input class='create-assignment' type="text" name="inptTitle" value="<?php echo $assignment->post_title ?>" required>
            </div>
            <div id='create-assignment-field'>
                Description
                <textarea cols="25" rows="10" autocomplete="off" name="txtDescription" required><?php echo $assignment->post_content ?></textarea>
            </div>
            <div id='create-assignment-field'>
                Sample File
                <input class='create-assignment' type="file" name="filJar">
            </div>
            <div id='create-assignment-field'>
                Preview Image
                <input class='create-assignment' type="file" name="filImage" accept="image/png">
            </div>
<?php if($_GET['op'] == 'create' || $_GET['op'] == '') : ?>
      <input type="hidden" name="op" value="create">
      <input type="submit" value="Create"/>   
<?php elseif($_GET['op'] == 'edit') : ?> 
      <input type="hidden" name="op" value="update">  
      <input type="submit" value="Finish Editing"/>
<?php endif ?>
      <input type="hidden" name="assignid" value="<?php echo $assignmentId ?>">
      <input type="hidden" name="courseid" value="<?php echo $courseId ?>">
      <a href="<?php echo site_url('/my-class/') ?>"><button type="button">Cancel</button></a>
        </div>
    </form>

    <div id="sidebar-menu">
        <div id="sidebar-menu-title">Courses</div>
      <ul class="sidebar-menu">
<?php if($courses) : ?>
<?php   foreach($courses as $course) : ?>
<?php     if($courseId == $course->Id) : ?>
            <li class="sidebar-menu-selected">
              <?php echo $course->Name ?>
              <?php echo '[' . $course->Quarter . ' ' . $course->Year . ']'?>
            </li>
<?php     else : ?> 
            <li class="sidebar-menu">
              <a href="<?php echo site_url('/manage-assignments/?courseid=' . $course->Id) ?>">
                <?php echo $course->Name ?>
                <?php echo '[' . $course->Quarter . ' ' . $course->Year . ']' ?>
              </a>
            </li>
<?php     endif ?>
<?php   endforeach ?>
<?php else : ?>
        <li class="sidebar-menu-center">You have no courses</li>
<?php endif ?>
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
<?php $assignments = $gtcs12_db->GetAllAssignments($courseId) ?>
<?php if($assignments) : ?>
<?php foreach($assignments as $assignment) : ?> 
<?php $assignLink = site_url('/assignment/?id=' . $assignment->AssignmentId); ?>
                <tr>
                    <th><a href="<?php echo $assignLink ?>"><?php echo $assignment->Title ?></a></th>
                    <th><?php echo date('F d, Y', strtotime($assignment->Date)); ?></th>
                    <th>
            <form action="<?php echo site_url('/manage-assignments/') ?>" method="get">
              <select name="op">
                <option disabled="disabled" selected>Choose an action</option>
                <option value="edit">Edit</option>
                <option value="delete">Delete</option>
              </select>   
              <input type="hidden" name="assignid" value="<?php echo $assignment->AssignmentId ?>">
              <input type="hidden" name="courseid" value="<?php echo $courseId ?>">
              <input type="submit" value="Confirm"/>  
            </form>
          </th>
                </tr>
<?php endforeach; ?>
<?php else : ?>
                <tr>
          <th class="center" colspan="3">You have no assignments</th>
        </tr>
<?php endif ?>
            </tbody>
        </table>
    </div>
</html>

<?php get_footer() ?>
