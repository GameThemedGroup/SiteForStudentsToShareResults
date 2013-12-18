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
