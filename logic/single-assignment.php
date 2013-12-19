<?php
  global $url;
  $pageState = (object) array();
  initializePageState($pageState);


function initializePageState(&$pageState)
{
  global $gtcs12_db;

  $userId = wp_get_current_user()->ID;
  $isProfessor = gtcs_user_has_role('author');
  $isStudent = gtcs_user_has_role('subscriber');

  $assignmentId = ifsetor($_GET["id"], null);

  if ($assignmentId != null)
    $displayedAssignment = get_post($assignmentId);

  $terms = wp_get_post_terms($assignmentId);
  $courseId = str_ireplace ('course:' ,'' , $terms[0]->name);

  $displayedCourse = $gtcs12_db->GetCourseByCourseId($courseId);

  $isOwner = false;
  // check if logged in user is a teacher and owner of assignment
  if($isProfessor) {
    if($displayedAssignment->post_author == $userId) {
      $isOwner = true;
    }
  }

  $isEnrolled = false;
  if($isStudent) {
    $isEnrolled = true; // needs to be changed, currently no function to check if student enrolled
  }

  // retrieve students and submissions for table
  $studentIds = $gtcs12_db->GetStudents($courseId);
  $studentList = get_users(array('include' => $studentIds));

  $submissionList = $gtcs12_db->GetAllSubmissions($assignmentId);

  // toggle opening/closing assignment
  $action = ifsetor($_POST['action'], null);

  if ($action != null) {
    $id = ifsetor($_POST['id'], null);

    if (!$isOwner || $id == null)
      break;

    if($action == 'open')
      update_post_meta($id, 'isEnabled', true);

    if($action == 'close')
      update_post_meta($id, 'isEnabled', false);
  }

  $nonSubmitters = getListOfNonSubmitters($submissionList, $studentIds);
  $status = get_post_meta($assignmentId, 'isEnabled', true);

  $sort = ifsetor($_GET['sort'], 'date');
  // sort submission table entries
  if($sort == 'author') {
    usort($submissionList, "compareSubmissionAuthor");
    usort($studentList, "compareStudentName");
  } else {
    usort($submissionList, "compareDate");
  }

  $view = ifsetor($_GET['view'], 'description');

  $pageState->studentList = $studentList;
  $pageState->submissionList = $submissionList;
  $pageState->isOwner = $isOwner;
  $pageState->isEnrolled = $isEnrolled;
  $pageState->canSubmit = $status;
  $pageState->displayedAssignment = $displayedAssignment;
  $pageState->displayedCourse = $displayedCourse;
  $pageState->view = $view;
  $pageState->nonSubmitters = $nonSubmitters;
  $pageState->assignmentId = $assignmentId;
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
