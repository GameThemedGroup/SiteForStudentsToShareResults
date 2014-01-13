<?php
  $pageState = new stdClass();
  initializePageState($pageState);
  extract((array)$pageState);


function initializePageState(&$ps)
{
  $tab = ifsetor($_GET['tab'], 'submissions');

  $tabList = array(
    'comments' => 'setupCommentTab',
    'submissions' => 'setupSubmissionsTab'
  );

  $tabChoiceList = array_keys($tabList);

  if ($tab != null) {
    if (array_key_exists($tab, $tabList)) {
      $userFeedback = call_user_func($tabList[$tab], $ps);
    } else {
      trigger_error("An invalid tab was selected.", E_USER_WARNING);
    }
  }

  $userId = ifsetor($_GET['user'], null);
  $user = get_userdata($userId);
  $isOwner = (get_current_user_id() == $user->ID);

  $ps->isOwner = $isOwner;
  $ps->tab = $tab;
  $ps->tabChoiceList = $tabChoiceList;
  $ps->user = $user;
}

function setupSubmissionsTab(&$ps)
{
  $userId = ifsetor($_GET['user'], null);

  include_once(get_template_directory() . '/common/submissions.php');
  $submissionList = GTCS_Submissions::getSubmissions($userId);
  foreach ($submissionList as $submission) {
    if (has_post_thumbnail($submission->SubmissionId)) {

      $thumbnail = wp_get_attachment_image_src(
        get_post_thumbnail_id($submission->SubmissionId),
        array(50, 50)
      );

      $submission->thumbnail = $thumbnail['0'];
    } else {
      $blankImage = get_stylesheet_directory_uri() . '/images/blank-project.png';
      $submission->thumbnail = $blankImage;
    }
  }

  $ps->submissionList = $submissionList;
  $ps->userId = $userId;
}

function setupCommentTab(&$ps)
{
  $userId = ifsetor($_GET['user'], null);

  $commentArgs = array(
    'number' => 10,
    'post_id' => 0,
    'status' => 'approve',
    'user_id' => $userId
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
      $blankImage = get_stylesheet_directory_uri() . '/images/blank-project.png';
      $comment->thumbnail = $blankImage;
    }

    $comment->parentTitle = $parentPost->post_title;
  }

  $ps->commentList = $commentList;
}
?>
