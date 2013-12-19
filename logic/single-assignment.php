<?php
  global $url;
  $pageState = (object) array();
  initializePageState($pageState);

function initializePageState(&$pageState)
{

  $action = ifsetor($_POST['action'], null);

  $actionList = array(
    'open'   => 'toggleAssignmentStatus',
    'close'  => 'toggleAssignmentStatus',
    'edit'   => 'setupSubmissionEdit',
    //'update' => 'updateAssignment'
  );

  if ($action == null) {

  } else if (array_key_exists($action, $actionList)) {
    $userFeedback = call_user_func($actionList[$action], $pageState);
  } else {
    trigger_error("An invalid action was provided.", E_USER_WARNING);
  }

  if (!setupPageForDisplay($pageState)) {
    // TODO generalize this error message
    echo "This page could not be displayed <br />";
    exit();
  }
}

function setupPageForDisplay(&$pageState)
{
  global $gtcs12_db;
  $userId = wp_get_current_user()->ID;
  $isProfessor = gtcs_user_has_role('author');
  $isStudent = gtcs_user_has_role('subscriber');

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

  $displayedCourse = $gtcs12_db->GetCourseByCourseId($courseId);

  $isOwner = $isProfessor && $displayedAssignment->post_author == $userId;

  $isEnrolled = false;
  if($isStudent) {
    $isEnrolled = true; // TODO fix this
  }

  // retrieve students and submissions for table
  $studentIds = $gtcs12_db->GetStudents($courseId);
  $studentList = get_users(array('include' => $studentIds));

  $submissionList = $gtcs12_db->GetAllSubmissions($assignmentId);

  $nonSubmitters = getListOfNonSubmitters($submissionList, $studentIds);

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

  $pageState->studentList = $studentList;
  $pageState->submissionList = $submissionList;
  $pageState->isOwner = $isOwner;
  $pageState->isEnrolled = $isEnrolled;
  $pageState->canSubmit = $canSubmit;
  $pageState->displayedAssignment = $displayedAssignment;
  $pageState->displayedCourse = $displayedCourse;
  $pageState->view = $view;
  $pageState->nonSubmitters = $nonSubmitters;
  $pageState->assignmentId = $assignmentId;

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

  if (!gtcs_user_has_role('author')
      || $userId != $assignment->post_author ) {
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

function getListOfNonSubmitters($submissionList, $studentIds)
{
  foreach ($submissionList as $submission) {
    $submitters[] = $submission->AuthorId;
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
