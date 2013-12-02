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
$isProfessor = gtcs_user_has_role('author');

if(!$isProfessor) {
  echo "You do not have permission to view this page. </br />";
  return;
}

$current_user = wp_get_current_user();
$courses = $gtcs12_db->GetCourseByFacultyId($current_user->ID);

$courseid = $_GET ? $_GET['courseid'] : null;

if($courseid == null && $courses != null)
  $courseid = $courses[0]->Id; // default to first course

if(!empty($_GET['op']))
  $getOperation = $_GET['op'];
else
  $getOperation = null;

$action = null;
$assignment = null;

if($getOperation == 'delete') { // delete assignment
  $action = DeleteAssignment($current_user->ID);
}
else if($getOperation == 'edit') // edit assignment(page loads with values from existing assignment)
{
  if($_GET['assignid'])
  {
    $assignmentId = $_GET['assignid'];
    $assignment = $gtcs12_db->GetAssignment($assignmentId);
  }
}

$postOperation = $_POST ? $_POST['op'] : null;

if($postOperation == 'update')
  $action = UpdateAssignment($current_user->ID, $courseid);
else if($postOperation == 'create')
  $action = CreateAssignment($current_user->ID, $courseid);

function DeleteAssignment($authorid)
{
  if(empty($_GET['assignid'])) // assignment id not give
    return "assign error";

  $assignmentid = $_GET['assignid'];
  $assignment_info = get_post($assignmentid);

  if(!$assignment_info) // assignment does not exist
    return "found error";

  if($assignment_info->post_author != $authorid) // user does not have permission to delete assignment
    return "owner error";

  wp_delete_post($assignmentid);
  DeleteAttachments($assignmentid, 'jar');
  DeleteAttachments($assignmentid, 'image');

  return "deleted";
}

// todo check for missing $_POST data
function UpdateAssignment($authorid, $courseid)
{
  $assignmentid = $_POST['assignid'];
  $title = $_POST['inptTitle'];
  $description = $_POST['txtDescription'];
  $assignmentLink = '';
  $isEnabled = true;

  global $gtcs12_db;
  $gtcs12_db->UpdateAssignment($assignmentid, $authorid, $courseid, $title, $description, $assignmentLink, $isEnabled);
  AttachFiles($assignmentid, 'jar', 'jar');
  AttachFiles($assignmentid, 'image', 'image');

  return "edited";
}

// todo check for missing $_POST data
function CreateAssignment($authorid, $courseid)
{
  $title = $_POST['inptTitle'];
  $description = $_POST['txtDescription'];
  $assignmentLink = '';
  $isEnabled = true;

  global $gtcs12_db;
  $assignmentid = $gtcs12_db->CreateAssignment($authorid, $courseid, $title, $description, $assignmentLink, $isEnabled);
  AttachFiles($assignmentid, 'jar', 'jar');
  AttachFiles($assignmentid, 'image', 'image');

  return "created";
}

// Checks the $_FILES array for images and attaches them to the given assignment
// Removes any current attachments
//
// @param assignmentid    the id of the post holding the assignment information
// @param fileindex       the index of $_FILES where the file is located
// @param assignmentType  the type of attachment (ex. 'jar' or 'image')
function AttachFiles($assignmentid, $fileIndex, $attachmentType)
{
  global $gtcs12_db;

  if(file_exists($_FILES[$fileIndex]['tmp_name']) && is_uploaded_file($_FILES[$fileIndex]['tmp_name'])) {
    DeleteAttachments($assignmentid, $attachmentType);
    $title = pathinfo($_FILES[$fileIndex]['name'], PATHINFO_FILENAME);
    $isImage = ($attachmentType == "image");
    $gtcs12_db->AttachFileToPost($assignmentid, $fileIndex, $title, $attachmentType, $isImage);
  }
}

// Removes attachments of the given type from the post
//
// @param assignmentid    the id of the post holding the assignment information
// @param fileindex       the index of $_FILES where the file is located
// @param assignmentType  the type of attachment (ex. 'jar' or 'image')
function DeleteAttachments($assignmentid, $attachmentType)
{
  global $gtcs12_db;
  $oldAttachments = $gtcs12_db->GetAttachments($assignmentid, $attachmentType);
  foreach($oldAttachments as $attachment)
    wp_delete_attachment($attachment->ID, true);
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

<form action="<?php echo get_permalink() . "?courseid={$courseid}"; ?>" method="post" enctype="multipart/form-data">
  <div id='create-assignment-box-left'>
    <div id='pagetitle'>
      <?php echo ($getOperation == 'edit' ? "Edit Assignment" : "Create Assignment"); ?>
    </div>
    <div id='create-assignment-field'>Title
      <input class='create-assignment' type="text" name="inptTitle"
        value="<?php echo $assignment ? $assignment->post_title : ''; ?>" required>
    </div>
    <div id='create-assignment-field'>Description
      <?php $descriptionValue = $assignment ? $assignment->post_content : ''; ?>
      <textarea cols="25" rows="10" autocomplete="off" name="txtDescription" required><?php echo $descriptionValue; ?></textarea>
    </div>
    <div id='create-assignment-field'>Sample File
      <input class='create-assignment' type="file" name="jar">
    </div>
    <div id='create-assignment-field'>Preview Image
      <input class='create-assignment' type="file" name="image" accept="image/*">
    </div>

  <?php if($getOperation == 'create' || $getOperation == 'delete' || $getOperation == '') : ?>
    <input type="hidden" name="op" value="create">
    <input type="submit" value="Create"/>
  <?php elseif($getOperation == 'edit') : ?>
    <input type="hidden" name="op" value="update">
    <input type="submit" value="Finish Editing"/>
  <?php endif ?>

    <input type="hidden" name="assignid" value="<?php echo $assignmentId ?>">
    <input type="hidden" name="courseid" value="<?php echo $courseid ?>">
    <a href="<?php echo site_url('/my-class/') ?>"><button type="button">Cancel</button></a>
  </div> <!-- Create-Assignment-Box-Left -->
</form>

<div id="sidebar-menu">
  <div id="sidebar-menu-title">Courses</div>
  <ul class="sidebar-menu">
  <?php if($courses) : ?>
    <?php foreach($courses as $course) : ?>
      <?php if($courseid == $course->Id) : ?>
        <li class="sidebar-menu-selected">
          <?php echo $course->Name ?>
          <?php echo '[' . $course->Quarter . ' ' . $course->Year . ']'?>
        </li>
      <?php else : ?>
        <li class="sidebar-menu">
          <a href="<?php echo site_url('/manage-assignments/?courseid=' . $course->Id) ?>">
            <?php echo $course->Name ?>
            <?php echo '[' . $course->Quarter . ' ' . $course->Year . ']' ?>
          </a>
        </li>
      <?php endif ?>
    <?php endforeach ?>
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
      <?php $assignments = $gtcs12_db->GetAllAssignments($courseid) ?>
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
                  <input type="hidden" name="courseid" value="<?php echo $courseid ?>">
                  <input type="submit" value="Confirm"/>
                </form>
              </th>
            </tr>
        <?php endforeach; ?>
      <?php else: ?>
            <tr>
              <th class="center" colspan="3">You have no assignments</th>
            </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</html>

<?php get_footer(); ?>
