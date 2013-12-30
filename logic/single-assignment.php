<?php
  global $url;
  $pageState = (object) array();
  initializePageState($pageState);

function initializePageState(&$ps)
{
  $action = ifsetor($_POST['action'], null);
  $actionList = array(
    'open'   => 'toggleAssignmentStatus',
    'close'  => 'toggleAssignmentStatus',
    //'edit'   => 'setupSubmissionEdit',
    'create' => 'createSubmission'
  );

  $ps->userFeedback = '';
  if ($action == null) {
  } else if (array_key_exists($action, $actionList)) {
    $ps->userFeedback = call_user_func($actionList[$action], $ps);
  } else {
    trigger_error("An invalid action was provided.", E_USER_WARNING);
  }

  if (!setupPageForDisplay($ps)) {
    // TODO generalize this error message
    echo "This page could not be displayed <br />";
    exit();
  }
}

function createSubmission(&$ps)
{
  $studentId = get_current_user_id();

  $description = ifsetor($_POST['description'], null);
  $entryClass = ifsetor($_POST['class'], null);
  $title = ifsetor($_POST['title'], null);
  $assignmentId = ifsetor($_POST['assignmentId'], 123);
  $courseId = ifsetor($_POST['courseId'], null);

  if ($entryClass == 'other') {
    $entryClass = ifsetor($_POST['classInput'], null);
  }

  if (!gtcs_validate_not_null(__FUNCTION__, __FILE__, __LINE__,
    compact('assignmentId', 'courseId', 'description', 'title',
    'entryClass'))) {

    return "Invalid values when creating assignment.";
  }


  include_once(get_template_directory() . '/common/submissions.php');
  $submissionId = GTCS_Submissions::CreateSubmission( (object) array(
    'assignmentId' => $assignmentId,
    'courseId' => $courseId,
    'description' => $description,
    'entryClass' => $entryClass,
    'studentId' => $studentId,
    'title' => $title
  ));

  include_once(get_template_directory() . '/common/attachments.php');
  if(isset($_FILES['jar'])) {
    $jarLocation = GTCS_Attachments::handleFileUpload('jar');
    GTCS_Attachments::AttachFileToPost($submissionId, $jarLocation, $title, 'jar', false, $studentId);
  }

  if(isset($_FILES['image'])) {
    $imageLocation = GTCS_Attachments::handleFileUpload('image');
    GTCS_Attachments::AttachFileToPost($submissionId, $imageLocation, $title, 'image', true, $studentId);
  }

  return "Assignment successfully submitted.";
}

function setupPageForDisplay(&$ps)
{
  $userId = wp_get_current_user()->ID;
  $isProfessor = gtcs_user_has_role('professor');
  $isStudent = gtcs_user_has_role('student');

  $assignmentId = ifsetor($_GET["id"], null);

  if ($assignmentId == null) {
    trigger_error(__FUNCTION__ . "
      - Assignment ID not provided.",
      E_USER_WARNING);
    return false;
  }

  $displayedAssignment = get_post($assignmentId);

  $terms = wp_get_post_terms($assignmentId);
  $courseId = str_ireplace ('course:' ,'' , $terms[0]->name);

  include_once(get_template_directory() . '/common/courses.php');
  $displayedCourse = GTCS_Courses::GetCourseByCourseId($courseId);

  $isOwner = $isProfessor && $displayedAssignment->post_author == $userId;
  $isEnrolled = false;
  if($isStudent) {
    $isEnrolled = true; // TODO fix this
  }

  // retrieve students and submissions for table
  include_once(get_template_directory() . '/common/users.php');
  $studentList = GTCS_Users::GetStudents($courseId);

  include_once(get_template_directory() . '/common/submissions.php');
  $submissionList = GTCS_Submissions::GetAllSubmissions($assignmentId);

  $nonSubmitters = getListOfNonSubmitters($submissionList, $studentList);

  $sort = ifsetor($_GET['sort'], 'date');
  // sort submission table entries
  if($sort == 'author') {
    usort($submissionList, "compareSubmissionAuthor");
    usort($studentList, "compareStudentName");
  } else {
    usort($submissionList, "compareDate");
  }

  $canSubmit = get_post_meta($assignmentId, 'isEnabled', true);
  $view = ifsetor($_GET['view'], 'description');

  $ps->assignmentId = $assignmentId;
  $ps->canSubmit = $canSubmit;
  $ps->courseId = $courseId;
  $ps->displayedAssignment = $displayedAssignment;
  $ps->displayedCourse = $displayedCourse;
  $ps->isEditing = false;
  $ps->isEnrolled = $isEnrolled;
  $ps->isOwner = $isOwner;
  $ps->nonSubmitters = $nonSubmitters;
  $ps->studentList = $studentList;
  $ps->submissionDescription = '';
  $ps->submissionList = $submissionList;
  $ps->submissionTitle = '';
  $ps->view = $view;

  return true;
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
      || $userId != $assignment->post_professor ) {
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
