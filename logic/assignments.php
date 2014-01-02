<?php
$pageState = new stdClass();

initializePageState($pageState);
extract((array)$pageState);

function initializePageState(&$ps)
{
  $isProfessor = gtcs_user_has_role('professor');

  if (!$isProfessor) {
    echo "You do not have permission to view this page. <br />";
    return;
  }

  $action = ifsetor($_POST['action'], null);

  $actionList = array(
    'edit'   => 'setupEdit',
    'delete' => 'deleteAssignment',
    'create' => 'createAssignment',
    'update' => 'updateAssignment',
    'upload' => 'uploadFromXml'
  );

  setupDefaultValues($ps);
  setupAssignmentSubmissionForm($ps);
  if ($action != null) {
    if (array_key_exists($action, $actionList)) {
      $ps->userFeedback = call_user_func($actionList[$action], $ps);
    } else {
      trigger_error("An invalid action was provided.", E_USER_WARNING);
    }
  }

  setupCourseAndAssignments($ps);
}

function setupAssignmentSubmissionForm(&$ps)
{
  $assignmentId = ifsetor($_GET["id"], null);
  $courseId = getCourseId($ps);
  $jarClassList = array(
    'Main.class',
    'user.Main.class'
  );

  $formValues = array(
    'courseId' => $courseId
  );

  $ps->doShowUrl = true;
  $ps->formCallback = site_url("assignments?id={$courseId}");
  $ps->formAppletClassList = $jarClassList;

  $ps->formAction = 'create';
  $ps->formClassValue = '';
  $ps->formDescriptionValue = '';
  $ps->formHiddenValues = $formValues;
  $ps->formSubmitText = 'Create';
  $ps->formTitle = 'Create Assignment';
  $ps->formTitleValue = '';
  $ps->formUrlValue = '';
}

function setupCourseAndAssignments(&$ps)
{
  $courseId = getCourseId($ps);

  if ($courseId == null) {
    $assignmentList = array();
  } else {
    include_once(get_template_directory() . '/common/assignments.php');
    $assignmentList = GTCS_Assignments::getAllAssignments($courseId);
  }

  $hasAssignments = sizeof($assignmentList) != 0;

  include_once(get_template_directory() . '/common/courses.php');
  $professorId = get_current_user_id();
  $courseList = GTCS_Courses::getCourseByFacultyId($professorId);

  $ps->assignmentList = $assignmentList;
  $ps->courseList = $courseList;
  $ps->courseId = $courseId;
  $ps->hasAssignments = $hasAssignments;
}

function getCourseId(&$ps)
{
  if (isset($ps->courseId))
    return $ps->courseId;

  $professorId = wp_get_current_user()->ID;

  include_once(get_template_directory() . '/common/courses.php');
  $courseList = GTCS_Courses::getCourseByFacultyId($professorId);

  $courseId = ifsetor($_GET['id'], null);

  if($courseId == null) {
    $courseId = ifsetor($courseList[0]->Id, null);
  }

  return $courseId;
}

function setupEdit(&$ps)
{
  $assignmentId = ifsetor($_POST['assignmentId'], null);

  if ($assignmentId == null) {
    return "There was an error attempting to edit the assignment.";
  }

  $assignment = get_post($assignmentId);

  if ($assignment->post_author != get_current_user_id()) {
    return "You do not have permission to edit this assignment.";
  }

  $formHiddenValues = ifsetor($ps->formHiddenValues, null);
  $formHiddenValues['assignId'] = $assignmentId;

  $formClassValue = get_post_meta($assignmentId, 'entryClass', true);
  if ($formClassValue != '') { // strip off '.class'
    $formClassValue = substr($formClassValue, 0, -1 * strlen('.class'));
  }

  $ps->formAction = 'update';
  $ps->formClassValue = $formClassValue;
  $ps->formDescriptionValue = $assignment->post_content;
  $ps->formHiddenValues = $formHiddenValues;
  $ps->formSubmitText = 'Finish Updating';
  $ps->formTitle = "Update Assignment";
  $ps->formTitleValue = $assignment->post_title;
  $ps->formUrlValue = get_post_meta($assignmentId, 'link', true);

  return "Your are now editing the course.";
}

function setupDefaultValues(&$ps)
{
  $assignmentId = '';
  $isEditing = false;
  $userFeedback = '';

  $displayedAssignment = (object) array(
    'link' => '',
    'post_title' => '',
    'post_content' => ''
  );

  $ps->assignmentId = $assignmentId;
  $ps->isEditing = $isEditing;
  $ps->userFeedback = $userFeedback;

  $ps->displayedAssignment = $displayedAssignment;
}

function uploadFromXml()
{
  $professorId = wp_get_current_user()->ID;
  $courseId = ifsetor($_POST['id'], null);

  // if(gtcs_validate_not_null($professorId, $courseId))

  $file = $_FILES['xml'];
  $xmlString = file_get_contents($file['tmp_name']);

  require_once(get_template_directory() . '/common/assignments.php');

  GTCS_Assignments::createAssignmentsFromXml($xmlString, $professorId, $courseId);
  return "Assignments uploaded.";
}

function deleteAssignment()
{
  $professorId = wp_get_current_user()->ID;
  $assignmentId = ifsetor($_POST['assignmentId'], null);

  if (!gtcs_validate_not_null(__FUNCTION__, __FILE__, __LINE__,
    compact('professorId', 'assignmentId'))) {

    return "Invalid input when deleting assignment.";
  }

  $assignment = get_post($assignmentId);

  if(!$assignment) // assignment does not exist
    return "This assignment does not exist.";

  // user does not have permission to delete assignment
  if ($assignment->post_author != $professorId)
    return "You do not have permission to delete this assignment.";

  wp_delete_post($assignmentId);
  DeleteAttachments($assignmentId, 'jar');
  DeleteAttachments($assignmentId, 'image');

  return "{$assignment->post_title} has been deleted.";
}

function updateAssignment()
{
  $professorId = wp_get_current_user()->ID;
  $courseId = ifsetor($_POST['courseId'], null);
  $assignmentId = ifsetor($_POST['assignId'], null);
  $title = ifsetor($_POST['title'], null);
  $description = ifsetor($_POST['description'], null);

  if (!gtcs_validate_not_null(__FUNCTION__, __FILE__, __LINE__,
    compact('professorId', 'courseId', 'assignmentId', 'title',
    'description'))) {

    return "Invalid input when modifying assignment.";
  }

  $assignmentLink = ifsetor($_POST['class'], '');
  $assignmentLink = ifsetor($_POST['link'], '');
  $isEnabled = true;

  include_once(get_template_directory() . '/common/assignments.php');
  GTCS_Assignments::updateAssignment(
    $assignmentId,
    $professorId,
    $courseId,
    $title,
    $description,
    $assignmentLink,
    $isEnabled
  );

  if (isset($_FILES['image'])) {
    AttachFiles($assignmentId, 'image', 'image');
  }

  if (isset($_FILES['jar'])) {
    AttachFiles($assignmentId, 'jar', 'jar');
    update_post_meta($assignmentId, "entryClass", $entryClass);
  }

  return "{$title} has been updated.";
}

function createAssignment()
{
  $professorId = wp_get_current_user()->ID;
  $courseId = ifsetor($_POST['courseId'], null);
  $title = ifsetor($_POST['title'], null);
  $description = ifsetor($_POST['description'], null);
  if (!gtcs_validate_not_null(__FUNCTION__, __FILE__, __LINE__,
    compact('professorId', 'courseId', 'title',
    'description'))) {

    return "Invalid values when creating assignment.";
  }

  $entryClass = ifsetor($_POST['class'], null);
  if ($entryClass == 'other') {
    $entryClass = ifsetor($_POST['classInput'], null);
    if ($entryClass != null)
      $entryClass .= '.class';
  }

  $assignmentLink = ifsetor($_POST['link'], '');
  $isEnabled = true;

  include_once(get_template_directory() . '/common/assignments.php');
  $args = (object) array(
    'title' => $title,
    'description' => $description,
    'professorId' => $professorId,
    'courseId' => $courseId,
    'link' => $assignmentLink,
    'isEnabled' => $isEnabled
  );

  $assignmentId = GTCS_Assignments::CreateAssignment($args);

  AttachFiles($assignmentId, 'jar', 'jar');
  update_post_meta($assignmentId, "entryClass", $entryClass);

  AttachFiles($assignmentId, 'image', 'image');

  return "{$title} has been created.";
}

// Checks the $_FILES array for images and attaches them to the given assignment
// Removes any current attachments
//
// @param assignmentId    the id of the post holding the assignment information
// @param fileindex       the index of $_FILES where the file is located
// @param assignmentType  the type of attachment (ex. 'jar' or 'image')
function AttachFiles($assignmentId, $fileIndex, $attachmentType)
{
  include_once(get_template_directory() . '/common/attachments.php');
  if (   file_exists($_FILES[$fileIndex]['tmp_name'])
      && is_uploaded_file($_FILES[$fileIndex]['tmp_name'])) {

    DeleteAttachments($assignmentId, $attachmentType);

    $title = pathinfo($_FILES[$fileIndex]['name'], PATHINFO_FILENAME);
    $isImage = ($attachmentType == "image");

    $fileAttr = GTCS_Attachments::handleFileUpload($fileIndex);

    $attachmentArgs = (object) array(
      'postId' => $assignmentId,
      'fileAttr' => $fileAttr,
      'title' => $title,
      'type' => $attachmentType,
      'isFeaturedImage' => $isImage
    );

    GTCS_Attachments::attachFileToPost($attachmentArgs);
  }
}

// Removes attachments of the given type from the post
//
// @param assignmentId    the id of the post holding the assignment information
// @param fileindex       the index of $_FILES where the file is located
// @param assignmentType  the type of attachment (ex. 'jar' or 'image')
function DeleteAttachments($assignmentId, $attachmentType)
{
  include_once(get_template_directory() . '/common/attachments.php');
  $oldAttachments = GTCS_Attachments::GetAttachments($assignmentId, $attachmentType);
  foreach($oldAttachments as $attachment)
    wp_delete_attachment($attachment->ID, true);
}
?>
