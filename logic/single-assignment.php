<?php
  $currentUser = wp_get_current_user();
  $isTeacher = gtcs_user_has_role('author');
  $isStudent = gtcs_user_has_role('subscriber');

  if($_GET["assignid"])
  {
    $assignmentId = $_GET["assignid"];
    $assignment = get_post($assignmentId);
  }

  // get sort mode, default is by date
  if($_GET['sort'])
  {
    $sort = $_GET['sort'];
  }
  else
  {
    $sort = 'date';
  }

  // get view mode, default is description
  if($_GET['view'])
  {
    $view = $_GET['view'];
  }
  else
  {
    $view = 'description';
  }

  $terms = wp_get_post_terms($assignmentId);
  $courseId = str_ireplace ('course:' ,'' , $terms[0]->name);
  $course = $gtcs12_db->GetCourseByCourseId($courseId);

  // check if logged in user is a teacher and owner of assignment
  if($isTeacher)
  {
    if($assignment->post_author == $currentUser->ID)
    {
      $isOwner = true;
    }
  }

  // check if logged in user is student and enrolled in this course
  if($isStudent)
  {
    $isEnrolled = true; // needs to be changed, currently no function to check if student enrolled
  }

  // retrieve students and submissions for table
  $students = $gtcs12_db->GetStudents($courseId);
  $submissions = $gtcs12_db->GetAllSubmissions($assignmentId);

  // toggle opening/closing assignment
  if($_GET['op'] == 'open')
    update_post_meta($assignmentId, 'isEnabled', 1, 0);
  else if($_GET['op'] == 'close')
    update_post_meta($assignmentId, 'isEnabled', 0, 1);

  $status = get_post_meta($assignmentId, 'isEnabled', true);

  // sort submission table entries
  if($sort == 'author')
  {
    usort($submissions, "cmpAuthorA");
    usort($students, "cmpAuthorB");
  }
  else
  {
    // sort by date
    usort($submissions, "cmpDate");
  }

  // helper functions needed for sorting
  function cmpAuthorA($a, $b)
  {
    return strcmp(strtolower($a->AuthorName), strtolower($b->AuthorName));
  }

  function cmpAuthorB($a, $b)
  {
    return strcmp(strtolower($a->Name), strtolower($b->Name));
  }

  function cmpDate($a, $b)
  {
    return strcmp($a->SubmissionDate, $b->SubmissionDate);
  }
?>
