<?php
if(is_user_logged_in())
{
  $userInfo = get_userdata($_GET['user']);
  $comments_per_page = 8;
  $course = $gtcs12_db->GetCourseByStudentId($userInfo->ID);
  $professor = get_user_by('id', $course[0]->FacultyId);
  $currentUser = wp_get_current_user();

  if(gtcs_user_has_role('subscriber', $userInfo->ID))
  {
    $showSubmissions = true;
    $showStudentInfo = true;
  }
  else if(gtcs_user_has_role('author', $userInfo->ID))
  {
    $showProfessorInfo = true;
  }

  if($currentUser->ID == $userInfo->ID)
    $isOwner = true;

  $args = array(
    'author_email' => '',
    'ID' => '',
    'karma' => '',
    'number' => 5,
    'offset' => '',
    'orderby' => '',
    'order' => 'DESC',
    'parent' => '',
    'post_id' => 0,
    'post_author' => '',
    'post_name' => '',
    'post_parent' => '',
    'post_status' => '',
    'post_type' => '',
    'status' => 'approve',
    'type' => '',
    'user_id' => $userInfo->ID,
    'search' => '',
    'count' => false,
    'meta_key' => '',
    'meta_value' => '',
    'meta_query' => '',
  );

  $comments = get_comments($args);
  $commentCount = count($comments);
  $page_count = ceil($commentCount / $comments_per_page);

  if($_GET['x'])
    $current_page = $_GET['x'];
  else
    $current_page = 1;

  $submissions = $gtcs12_db->GetSubmissions($userInfo->ID);
  $submissionCount = count($submissions);
}
?>
