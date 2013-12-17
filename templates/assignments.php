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
$isProfessor = gtcs_user_has_role('author');
$isStudent = gtcs_user_has_role('subscriber');

if(isset($_GET['assignid']))
{
  $assignmentId = $_GET['assignid'];
}
elseif(isset($_POST['assignid']))
{
  $assignmentId = $_POST['assignid'];
}

if(isset($_GET['courseid']))
{
  $courseId = $_GET['courseid'];
  $courses = $gtcs12_db->GetCourseByFacultyId($current_user->ID); 
}
elseif($isProfessor)
{
  $courses = $gtcs12_db->GetCourseByFacultyId($current_user->ID); 

  if($courses) 
  {
    $courseId = $courses[0]->Id;
  }
}

if($courseId == null && $courses != null)
  $courseId = $courses[0]->Id; // default to first course

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
  if(isset($assignmentId))
  {
    $assignment = $gtcs12_db->GetAssignment($assignmentId);
  }
}

$postOperation = $_POST ? $_POST['op'] : null;

if($postOperation == 'update')
  $action = UpdateAssignment($current_user->ID, $courseId);
else if($postOperation == 'create')
{
  if($isProfessor)
  {
    $action = CreateAssignment($current_user->ID, $courseId);
  }
  elseif($isStudent)
  {
    $title = $_POST['inptTitle'];
    $description = $_POST['txtDescription'];
    
    $gtcs12_db->CreateSubmission($title, $current_user->ID, $courseId, $assignmentId, $description);
    $action =  "<b>" . $title . "</b> has been submitted";
  }
}

function DeleteAssignment($authorid)
{
  if(empty($_GET['assignid'])) // assignment id not give
    return "No assignment ID provided";

  $assignmentId = $_GET['assignid'];
  $assignment_info = get_post($assignmentId);

  if(!$assignment_info) // assignment does not exist
    return "This assignment does not exist";

  if($assignment_info->post_author != $authorid) // user does not have permission to delete assignment
    return "You do not have permission to delete this";

  wp_delete_post($assignmentId);
  DeleteAttachments($assignmentId, 'jar');
  DeleteAttachments($assignmentId, 'image');

  return "<b>" . $assignment_info->post_title . "</b> has been deleted";
}

// todo check for missing $_POST data
function UpdateAssignment($authorid, $courseId)
{
  $assignmentId = $_POST['assignid'];
  $title = $_POST['inptTitle'];
  $description = $_POST['txtDescription'];
  $assignmentLink = '';
  $isEnabled = true;

  global $gtcs12_db;
  $gtcs12_db->UpdateAssignment($assignmentId, $authorid, $courseId, $title, $description, $assignmentLink, $isEnabled);
  AttachFiles($assignmentId, 'jar', 'jar');
  AttachFiles($assignmentId, 'image', 'image');

  return "<b>" . $title . "</b> has been updated";
}

// todo check for missing $_POST data
function CreateAssignment($authorid, $courseId)
{
  $title = $_POST['inptTitle'];
  $description = $_POST['txtDescription'];
  $assignmentLink = '';
  $isEnabled = true;

  global $gtcs12_db;
  $assignmentId = $gtcs12_db->CreateAssignment($authorid, $courseId, $title, $description, $assignmentLink, $isEnabled);
  AttachFiles($assignmentId, 'jar', 'jar');
  AttachFiles($assignmentId, 'image', 'image');

  return "<b>" . $title . "</b> has been created";
}

// Checks the $_FILES array for images and attaches them to the given assignment
// Removes any current attachments
//
// @param assignmentId    the id of the post holding the assignment information
// @param fileindex       the index of $_FILES where the file is located
// @param assignmentType  the type of attachment (ex. 'jar' or 'image')
function AttachFiles($assignmentId, $fileIndex, $attachmentType)
{
  global $gtcs12_db;

  if(file_exists($_FILES[$fileIndex]['tmp_name']) && is_uploaded_file($_FILES[$fileIndex]['tmp_name'])) {
    DeleteAttachments($assignmentId, $attachmentType);
    $title = pathinfo($_FILES[$fileIndex]['name'], PATHINFO_FILENAME);
    $isImage = ($attachmentType == "image");
    $gtcs12_db->AttachFileToPost($assignmentId, $fileIndex, $title, $attachmentType, $isImage);
  }
}

// Removes attachments of the given type from the post
//
// @param assignmentId    the id of the post holding the assignment information
// @param fileindex       the index of $_FILES where the file is located
// @param assignmentType  the type of attachment (ex. 'jar' or 'image')
function DeleteAttachments($assignmentId, $attachmentType)
{
  global $gtcs12_db;
  $oldAttachments = $gtcs12_db->GetAttachments($assignmentId, $attachmentType);
  foreach($oldAttachments as $attachment)
    wp_delete_attachment($attachment->ID, true);
}
?>

<!DOCTYPE html>
<html lang="en">
<?php if($action != null) : ?>
  <div id="action-box"><?php echo $action ?></div>
<?php endif ?>

<form action="<?php echo get_permalink() . "?courseid={$courseId}"; ?>" method="post" enctype="multipart/form-data">
  <div id='create-assignment-box'>
    <div id='create-assignment-title'>
      <?php echo ($getOperation == 'edit') ? "Edit Assignment" : "Create Assignment"; ?>
    </div>
    <div id='create-assignment-field'>
      <p class="create-assignment">Title</p>
      <input class='create-assignment' type="text" name="inptTitle"
        value="<?php echo $assignment ? $assignment->post_title : ''; ?>" required>
    </div>
    <div id='create-assignment-field'>
      <p class="create-assignment">Description</p>
      <?php $descriptionValue = $assignment ? $assignment->post_content : ''; ?>
      <textarea cols="25" rows="10" autocomplete="off" name="txtDescription" required><?php echo $descriptionValue; ?></textarea>
    </div>
    <div id='create-assignment-field'>
      <p class="create-assignment">Sample File</p>
      <input class='create-assignment' type="file" name="jar">
    </div>
    <div id='create-assignment-field'>
      <p class="create-assignment">Preview Image</p>
      <input class='create-assignment' type="file" name="image" accept="image/*">
    </div>
    <div id="create-assignment-buttons">
  <?php if($getOperation == 'create' || $getOperation == 'delete' || $getOperation == '') : ?>
      <input type="hidden" name="op" value="create">
      <input type="submit" value="Create"/>
  <?php elseif($getOperation == 'edit') : ?>
      <input type="hidden" name="op" value="update">
      <input type="submit" value="Finish Editing"/>
  <?php endif ?>
      <input type="hidden" name="assignid" value="<?php echo $assignmentId ?>">
      <input type="hidden" name="courseid" value="<?php echo $courseId ?>">
      <a href="<?php echo site_url('/my-class/?id=' . $courseId) ?>"><button type="button">Cancel</button></a>
    </div>
  </div> <!-- Create-Assignment-Box -->
</form>

<?php if($isProfessor) : ?>
  <div id="sidebar-menu">
    <div id="sidebar-menu-title">Courses</div>
    <ul class="sidebar-menu">
<?php if($courses) : ?>
<?php   foreach($courses as $course) : ?>
<?php   if($courseId == $course->Id) : ?>
          <li class="sidebar-menu-selected">
<?php   else : ?> 
          <li class="sidebar-menu">
<?php   endif ?>
        <a class="sidebar-menu" href="<?php echo site_url('/manage-assignments/?courseid=' . $course->Id) ?>">
          <p class="sidebar-menu-top"><?php echo $course->Name ?></p>
          <p class="sidebar-menu-bottom"><?php echo $course->Quarter . ', ' . $course->Year ?></p>
        </a>
      </li>
<?php   endforeach ?>
<?php else : ?>
      <li class="sidebar-menu-center">You have no courses</li>
<?php endif ?>
    </ul>
  </div>

<div id='table'>
  <div id='table-title'>Manage Assignments</div>
  <table>
    <thead>
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
          <?php $assignLink = site_url('/assignment/?assignid=' . $assignment->AssignmentId); ?>
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
      <?php else: ?>
            <tr>
              <th class="center" colspan="3">You have no assignments for this course</th>
            </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<?php endif ?>
</html>

<?php get_footer(); ?>
