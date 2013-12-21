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


  include_once(get_template_directory() . '/common/submissions.php');
  $submissions = GTCS_Submissions::GetSubmissions($user->ID);
  $submissionCount = count($submissions);

  $commentList = buildCommentList($user->ID);
  $pageState = array_merge($pageState, compact(
    'submissions',
    'showSubmissions',
    'showStudentInfo',
    'showProfessorInfo',
    'commentList',
    'isOwner',
    'course',
    'professor',
    'submissoins',
    'submissionCount',
    'user'
  ));
}


function buildCommentList($userId)
{
  $commentArgs = array(
    'number' => 10,
    'post_id' => 0,
    'status' => 'approve',
    'user_id' => $userId,
  );

  $commentList = get_comments($commentArgs);

  foreach ($commentList as $comment) {
    $parentPost = get_post($comment->comment_post_ID);

    if (has_post_thumbnail($parentPost->ID)) {

      $thumbnail = wp_get_attachment_image_src(
        get_post_thumbnail_id($parentPost->ID),
        array(100, 100)
      );

      $comment->thumbnail = $thumbnail['0'];
    } else {
      $blankImage = get_template_directory() . '/images/blank-project.png';
      $comment->thumbnail = $blankImage;
    }

    $comment->parentTitle = $parentPost->post_title;
  }

  return $commentList;
}
?>
