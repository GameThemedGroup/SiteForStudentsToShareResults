<?php
  global $url;
  $pageState = new stdClass();
  initializePageState($pageState);
  extract((array)$pageState);

function initializePageState(&$ps)
{
  $action = ifsetor($_POST['action'], null);
  $actionList = array(
    'close'  => 'toggleAssignmentStatus',
    'create' => 'createSubmission',
    'delete' => 'deleteSubmission',
    'edit'   => 'setupEdit',
    'open'   => 'toggleAssignmentStatus',
    'update' => 'updateSubmission'
  );

  setupSubmissionForm($ps);

  $ps->userFeedback = '';
  if ($action == null) {
  } else if (array_key_exists($action, $actionList)) {
    $ps->userFeedback = call_user_func($actionList[$action], $ps);
  } else {
    trigger_error("An invalid action was provided.", E_USER_WARNING);
  }

  setupAssignmentDisplay($ps);
  setupSubmissionList($ps);
}

function createSubmission(&$ps)
{
  $studentId = get_current_user_id();

  $description = ifsetor($_POST['description'], null);
  $entryClass = ifsetor($_POST['class'], null);
  $title = ifsetor($_POST['title'], null);
  $assignmentId = ifsetor($_POST['assignmentId'], null);
  $courseId = ifsetor($_POST['courseId'], null);

  if ($entryClass == 'other') {
    $entryClass = ifsetor($_POST['classInput'], null);
    if ($entryClass != null)
      $entryClass .= '.class';
  }

  if (!gtcs_validate_not_null(__FUNCTION__, __FILE__, __LINE__,
    compact('assignmentId', 'courseId', 'description', 'title',
    'entryClass'))) {

    return "Invalid values when creating assignment.";
  }


  include_once(get_template_directory() . '/common/submissions.php');
  $submissionId = GTCS_Submissions::createSubmission( (object) array(
    'assignmentId' => $assignmentId,
    'courseId' => $courseId,
    'description' => $description,
    'entryClass' => $entryClass,
    'studentId' => $studentId,
    'title' => $title
  ));

  $attachmentArgs = (object) array(
    'postId' => $submissionId,
    'title' => $title,
    'authorId' => $studentId
  );

  include_once(get_template_directory() . '/common/attachments.php');
  if(isset($_FILES['jar'])) {
    $jar = GTCS_Attachments::handleFileUpload('jar');
    $attachmentArgs->fileAttr = $jar;
    $attachmentArgs->type = 'jar';
    $attachmentArgs->isFeaturedImage = false;

    GTCS_Attachments::attachFileToPost($attachmentArgs);
  }

  if(isset($_FILES['image'])) {
    $image = GTCS_Attachments::handleFileUpload('image');
    $attachmentArgs->fileAttr = $image;
    $attachmentArgs->type = 'image';
    $attachmentArgs->isFeaturedImage = true;
    GTCS_Attachments::attachFileToPost($attachmentArgs);
  }

  return "Assignment successfully submitted.";
}

function setupAssignmentDisplay(&$ps)
{
  $assignmentId = ifsetor($_GET["id"], null);

  $doSubmit = isset($ps->doSubmit) || isset($_GET["doSubmit"]) ? true : false;

  if ($assignmentId == null) {
    trigger_error(__FUNCTION__ . " - Assignment ID not provided.",
      E_USER_WARNING);
    return false;
  }

  $userId = wp_get_current_user()->ID;
  $isProfessor = gtcs_user_has_role('professor');
  $isStudent = gtcs_user_has_role('student');

  $displayedAssignment = get_post($assignmentId);

  $isOwner = $isProfessor && $displayedAssignment->post_author == $userId;
  $isEnrolled = false;
  if($isStudent) {
    $isEnrolled = true; // TODO fix this
  }

  $canSubmit = get_post_meta($assignmentId, 'isEnabled', true);

  $ps->assignmentId = $assignmentId;
  $ps->canSubmit = $canSubmit;
  $ps->displayedAssignment = $displayedAssignment;
  $ps->doSubmit = $doSubmit && $isEnrolled && $canSubmit;
  $ps->isEditing = false;
  $ps->isEnrolled = $isEnrolled;
  $ps->isOwner = $isOwner;
}

function setupSubmissionForm(&$ps)
{
  $jarClassList = array(
    'Main.class',
    'user.Main.class'
  );

  $assignmentId = ifsetor($_GET["id"], null);
  $terms = wp_get_post_terms($assignmentId);
  $courseId = str_ireplace ('course:' ,'' , $terms[0]->name);

  $formHiddenValues = array(
    'assignmentId' => $assignmentId,
    'courseId' => $courseId
  );

  $ps->doShowUrl = false;
  $ps->formAction = 'create';
  $ps->formCallback = site_url("assignment?id={$assignmentId}");
  $ps->formClassValue = '';
  $ps->formDescriptionValue = '';
  $ps->formSubmitText = 'Submit';
  $ps->formTitle = 'Submit Assignment';
  $ps->formTitleValue = '';
  $ps->formUrlValue = '';
  $ps->formHiddenValues = $formHiddenValues;
  $ps->formAppletClassList = $jarClassList;
}

function setupSubmissionList(&$ps)
{
  $assignmentId = ifsetor($_GET["id"], null);

  $terms = wp_get_post_terms($assignmentId);
  $courseId = str_ireplace ('course:' ,'' , $terms[0]->name);

  include_once(get_template_directory() . '/common/courses.php');
  $displayedCourse = GTCS_Courses::getCourseByCourseId($courseId);

  // retrieve students and submissions for table
  include_once(get_template_directory() . '/common/users.php');
  $studentList = GTCS_Users::getStudents($courseId);

  include_once(get_template_directory() . '/common/submissions.php');
  $submissionList = GTCS_Submissions::getAllSubmissions($assignmentId);

  $userId = get_current_user_id();
  foreach ($submissionList as $submission) {
    $submission->canEdit = $submission->AuthorId == $userId;
  }

  // list of students who have not submitted any work for this assignment
  $nonSubmitters = getListOfNonSubmitters($submissionList, $studentList);

  $sort = ifsetor($_GET['sort'], 'date');

  // sort submission table entries
  if($sort == 'author') {
    usort($submissionList, "compareSubmissionAuthor");
    usort($studentList, "compareStudentName");
  } else {
    usort($submissionList, "compareDate");
  }

  $ps->courseId = $courseId;
  $ps->displayedCourse = $displayedCourse;
  $ps->isEditing = false;
  $ps->nonSubmitters = $nonSubmitters;
  $ps->studentList = $studentList;
  $ps->submissionList = $submissionList;
  $ps->view = ifsetor($_GET['view'], 'description');
}

function toggleAssignmentStatus()
{
  $action = ifsetor($_POST['action'], null);
  $assignmentId = ifsetor($_POST['id'], null);

  $assignment = get_post($assignmentId);
  $userId = wp_get_current_user()->ID;

  if ($assignmentId == null) {
    trigger_error(__FUNCTION__ . "
      - An invalid Assignment ID was provided.",
      E_USER_WARNING);
  }

  if (!gtcs_user_has_role('professor')
      || $userId != $assignment->post_author) {
    trigger_error("User does not have permission to perform this action");
    return "You do not have permission to perform this action.";
  }

  if($action == 'open') {
    update_post_meta($assignmentId, 'isEnabled', true);
    return "Assignment is now open for submissions.";
  }

  if($action == 'close') {
    update_post_meta($assignmentId, 'isEnabled', false);
    return "Assignment is now closed to submissions.";
  }
}

function updateSubmission()
{
  $courseId = ifsetor($_POST['courseId'], null);
  $description = ifsetor($_POST['description'], null);
  $studentId = get_current_user_id();
  $submissionId = ifsetor($_POST['submissionId'], null);
  $title = ifsetor($_POST['title'], null);

  if (!gtcs_validate_not_null(__FUNCTION__, __FILE__, __LINE__,
    compact('studentId', 'submissionId', 'title', 'description'))) {

    return "Invalid input when modifying submission.";
  }

  $entryClass = ifsetor($_POST['class'], '');
  if ($entryClass == 'other') {
    $entryClass = ifsetor($_POST['classInput'], null);
    if ($entryClass != null)
      $entryClass .= '.class';
  }

  // TODO create updateSubmission function
  // updateAssignment works because assignments and submissions are both posts
  // and submissions are not affected by the additional properties
  include_once(get_template_directory() . '/common/assignments.php');
  GTCS_Assignments::updateAssignment(
    $submissionId,
    $studentId,
    0,
    $title,
    $description,
    "",
    false
  );

  if (isset($_FILES['image'])) {
    attachFiles($submissionId, 'image', 'image');
  }

  if (isset($_FILES['jar'])) {
    attachFiles($submissionId, 'jar', 'jar');
    update_post_meta($submissionId, "entryClass", $entryClass);
  }

  return "{$title} has been updated.";
}

function setupEdit(&$ps)
{
  $submissionId = ifsetor($_POST['submissionId'], null);

  if ($submissionId == null) {
    return "There was an error attempting to edit the assignment.";
  }

  $submission = get_post($submissionId);

  if ($submission->post_author != get_current_user_id()) {
    return "You do not have permission to edit this assignment.";
  }

  $formHiddenValues = ifsetor($ps->formHiddenValues, null);
  $formHiddenValues['submissionId'] = $submissionId;

  $formClassValue = get_post_meta($submissionId, 'entryClass', true);
  if ($formClassValue != '') { // strip off '.class'
    $formClassValue = substr($formClassValue, 0, -1 * strlen('.class'));
  }

  $ps->doSubmit = true;
  $ps->formAction = 'update';
  $ps->formClassValue = $formClassValue;
  $ps->formDescriptionValue = $submission->post_content;
  $ps->formHiddenValues = $formHiddenValues;
  $ps->formSubmitText = 'Finish Updating';
  $ps->formTitle = "Update Assignment";
  $ps->formTitleValue = $submission->post_title;
  $ps->formUrlValue = get_post_meta($submissionId, 'link', true);

  return "Your are now editing the course.";
}

function deleteSubmission()
{
  $studentId = get_current_user_id();
  $submissionId = ifsetor($_POST['submissionId'], null);

  if (!gtcs_validate_not_null(__FUNCTION__, __FILE__, __LINE__,
    compact('studentId', 'submissionId'))) {

    return "Invalid input when deleting assignment.";
  }

  $submission = get_post($submissionId);

  if(!$submission) // assignment does not exist
    return "This assignment does not exist.";

  // user does not have permission to delete assignment
  if ($submission->post_author != $studentId)
    return "You do not have permission to delete this assignment.";

  wp_delete_post($submissionId);
  deleteAttachments($submissionId, 'jar');
  deleteAttachments($submissionId, 'image');

  return "{$submission->post_title} has been deleted.";
}

// Checks the $_FILES array for images and attaches them to the given assignment
// Removes any current attachments
//
// @param assignmentId    the id of the post holding the assignment information
// @param fileindex       the index of $_FILES where the file is located
// @param assignmentType  the type of attachment (ex. 'jar' or 'image')
function attachFiles($assignmentId, $fileIndex, $attachmentType)
{
  include_once(get_template_directory() . '/common/attachments.php');
  if (   file_exists($_FILES[$fileIndex]['tmp_name'])
      && is_uploaded_file($_FILES[$fileIndex]['tmp_name'])) {

    deleteAttachments($assignmentId, $attachmentType);

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
function deleteAttachments($assignmentId, $attachmentType)
{
  include_once(get_template_directory() . '/common/attachments.php');
  $oldAttachments = GTCS_Attachments::getAttachments($assignmentId, $attachmentType);
  foreach($oldAttachments as $attachment)
    wp_delete_attachment($attachment->ID, true);
}

function getListOfNonSubmitters($submissionList, $studentList)
{
  $submitters = array();
  foreach ($submissionList as $submission) {
    $submitters[] = $submission->AuthorId;
  }

  $studentIds = array();
  foreach($studentList as $student) {
    $studentIds[] = (int) $student->ID;
  }

  $submitters = array_unique($submitters);
  $nonSubmitterId = array_diff($studentIds, $submitters);

  $nonSubmitters = array();
  foreach ($nonSubmitterId as $studentId) {
    $nonSubmitters[] = get_user_by('id', $studentId);
  }

  return $nonSubmitters;
}

// helper functions needed for sorting
function compareSubmissionAuthor($a, $b)
{
  return strcmp(strtolower($a->AuthorName), strtolower($b->AuthorName));
}

function compareStudentName($a, $b)
{
  return strcmp(strtolower($a->display_name), strtolower($b->display_name));
}

function compareDate($a, $b)
{
  return strcmp($a->SubmissionDate, $b->SubmissionDate);
}
?>
