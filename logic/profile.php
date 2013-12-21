<?php
  $pageState = array();
  initializePageState($pageState);
  extract($pageState);

  $view = ifsetor($_GET['view'], 'submissions');

function initializePageState(&$pageState)
{
  $userId = ifsetor($_GET['user'], null);
  $user = get_userdata($userId);

  include_once(get_template_directory() . '/common/courses.php');
  $course = GTCS_Courses::getCourseByStudentId($userId);
  if ($course != null)
    $professor = get_user_by('id', $course[0]->FacultyId);

  if(gtcs_user_has_role('subscriber', $user->ID)) {
    $showSubmissions = true;
    $showStudentInfo = true;
    $showProfessorInfo = false;
  } else if(gtcs_user_has_role('author', $user->ID)) {
    $showSubmissions = false;
    $showProfessorInfo = true;
    $showStudentInfo = false;
  }
  $isOwner = (get_current_user_id() == $user->ID);

  $commentArgs = array(
    'number' => 5,
    'post_id' => 0,
    'status' => 'approve',
    'user_id' => $user->ID,
  );

  $comments = get_comments($commentArgs);
  $commentCount = count($comments);

  $comments_per_page = 8;
  $pageCount = ceil($commentCount / $comments_per_page);

  $currentPage = ifsetor($_GET['x'], 1);

  include_once(get_template_directory() . '/common/submissions.php');
  $submissions = GTCS_Submissions::GetSubmissions($user->ID);
  $submissionCount = count($submissions);

  $pageState = array_merge($pageState, compact(
    'submissions',
    'showSubmissions',
    'showStudentInfo',
    'showProfessorInfo',
    'currentPage',
    'pageCount',
    'commentCount',
    'comments',
    'isOwner',
    'course',
    'professor',
    'submissoins',
    'submissionCount',
    'user'
  ));
}
?>
