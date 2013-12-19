<?php
$pageState = (object) array();

initializePageState($pageState);

/*
$userFeedback = $pageState->userFeedback;
$isEditing = $pageState->isEditing;
$displayedAssignment = $pageState->displayedAssignment;
$assignmentList = $pageState->assignmentList;
$courseList = $pageState->courseList;
 */

function initializePageState(&$pageState)
{
  $professorId = wp_get_current_user()->ID;
  $isProfessor = gtcs_user_has_role('author');

  if (!$isProfessor) {
    echo "You do not have permission to view this page. <br />";
    return;
  }

  include_once(get_template_directory() . '/common/courses.php');
  $courseList = GTCS_Courses::getCourseByFacultyId($professorId);

  $courseId = ifsetor($_GET['id'], null);

  if($courseId == null) {
    $courseId = ifsetor($courseList[0]->Id, null);
  }

  $action = ifsetor($_POST['action'], null);

  $userFeedback = '';
  $isEditing = false;
  $displayedAssignment = (object) array('post_title' => '', 'post_content' => '');

  if ($action == 'edit') {
    $userFeedback = editAssignmentSetup($displayedAssignment, $isEditing);

  } else if ($action != null) {
    $actionList = array(
      'delete' => 'deleteAssignment',
      'create' => 'createAssignment',
      'update' => 'updateAssignment'
    );

    if (array_key_exists($actions, $actionList)) {
      $userFeedback = call_user_func($actionList[$action]);
    } else {
      trigger_error("An invalid action was provided.", E_USER_WARNING);
    }
  }

  include_once(get_template_directory() . '/common/assignments.php');
  $assignmentList = GTCS_Assignments::getAllAssignments($courseId);

  $pageState->userFeedback = $userFeedback;
  $pageState->displayedAssignment = $displayedAssignment;
  $pageState->assignmentList = $assignmentList;
  $pageState->courseList = $courseList;
  $pageState->isEditing = $isEditing;
  $pageState->courseId = $courseId;
}

function editAssignmentSetup(&$assignment, &$isEditing)
{
  $assignmentId = ifsetor($_POST['assignmentId'], null);
  $isEditing = $assignmentId != null;

  if ($assignmentId == null) {
    trigger_error(__FUNCTION__ . "
      - An invalid Assignment ID was provided.",
      E_USER_WARNING);

    return "There was an error attempting to edit the assignment.";
  }

  global $gtcs12_db;
  $assignment = $gtcs12_db->getAssignment($assignmentId);

  return "Your are now editing the course";
}

function deleteAssignment()
{
  $professorId = wp_get_current_user()->ID;
  $assignmentId = ifsetor($_POST['assignmentId'], null);

  $assignment = get_post($assignmentId);

  if(!$assignment) // assignment does not exist
    return "This assignment does not exist";

  // user does not have permission to delete assignment
  if ($assignment->post_author != $authorid)
    return "You do not have permission to delete this";

  wp_delete_post($assignmentId);
  DeleteAttachments($assignmentId, 'jar');
  DeleteAttachments($assignmentId, 'image');

  return "{$assignment_info->post_title} has been deleted";
}

// todo check for missing $_POST data
function updateAssignment($authorid, $courseId)
{
  $professorId = wp_get_current_user()->ID;
  $courseId = ifsetor($_POST['courseId'], null);
  $assignmentId = ifsetor($_POST['assignmentId'], null);
  $title = ifsetor($_POST['title'], null);
  $description = ifsetor($_POST['description'], null);

  $assignmentLink = '';
  $isEnabled = true;

  global $gtcs12_db;
  $gtcs12_db->UpdateAssignment(
    $assignmentId,
    $professorId,
    $courseId,
    $title,
    $description,
    $assignmentLink,
    $isEnabled
  );

  AttachFiles($assignmentId, 'jar', 'jar');
  AttachFiles($assignmentId, 'image', 'image');

  return "{$title} has been updated";
}

// todo check for missing $_POST data
function createAssignment()
{
  $professorId = wp_get_current_user()->ID;
  $courseId = ifsetor($_POST['courseId'], null);
  $title = ifsetor($_POST['title'], null);
  $description = ifsetor($_POST['description'], null);

  $assignmentLink = '';
  $isEnabled = true;

  global $gtcs12_db;
  $assignmentId = $gtcs12_db->CreateAssignment(
    $professorId,
    $courseId,
    $title,
    $description,
    $assignmentLink,
    $isEnabled
  );

  AttachFiles($assignmentId, 'jar', 'jar');
  AttachFiles($assignmentId, 'image', 'image');

  return "{$title} has been created";
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

  if (   file_exists($_FILES[$fileIndex]['tmp_name'])
      && is_uploaded_file($_FILES[$fileIndex]['tmp_name'])) {
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
