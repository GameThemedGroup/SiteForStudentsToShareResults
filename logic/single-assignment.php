<?php
  global $gtcs12_db;

  $userId = wp_get_current_user()->ID;
  $isProfessor = gtcs_user_has_role('author');
  $isStudent = gtcs_user_has_role('subscriber');

  $assignmentId = ifsetor($_GET["id"], null);

  if ($assignmentId != null)
    $displayedAssignment = get_post($assignmentId);

  $sort = ifsetor($_GET['sort'], 'date');
  $view = ifsetor($_GET['view'], 'description');

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
  $studentList = $gtcs12_db->GetStudents($courseId);
  $submissionList = $gtcs12_db->GetAllSubmissions($assignmentId);

  // toggle opening/closing assignment
  /*
  if($_GET['op'] == 'open')
    update_post_meta($assignmentId, 'isEnabled', 1, 0);
  else if($_GET['op'] == 'close')
    update_post_meta($assignmentId, 'isEnabled', 0, 1);
   */

  $status = get_post_meta($assignmentId, 'isEnabled', true);

  // sort submission table entries
  if($sort == 'author') {
    usort($submissionList, "compareSubmissionAuthor");
    usort($studentList, "compareStudentName");
  } else {
    usort($submissionList, "compareDate");
  }

  global $url;

  // helper functions needed for sorting
  function compareSubmissionAuthor($a, $b)
  {
    return strcmp(strtolower($a->AuthorName), strtolower($b->AuthorName));
  }

  function compareStudentName($a, $b)
  {
    return strcmp(strtolower($a->Name), strtolower($b->Name));
  }

  function compareDate($a, $b)
  {
    return strcmp($a->SubmissionDate, $b->SubmissionDate);
  }
?>
