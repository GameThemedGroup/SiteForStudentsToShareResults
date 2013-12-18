<?php
  $currentUser = wp_get_current_user();

  if (isset($_GET)) {
    $courseId = isset($_GET['courseid']) ? $_GET['courseid'] : NULL;
    $operation = isset($_GET['op']) ? $_GET['op'] : NULL;
  } elseif (isset($_POST)) {
    $operation = isset($_POST['op']) ? $_POST['op'] : NULL;
  } else {
    $courseId = NULL;
    $operation = NULL;
  }

  $action = "";
  $course = (object) array('Name' => '', 'Description' => '', 'Quarter' => '');

  $courseList = $gtcs12_db->GetCourseByFacultyId($currentUser->ID);

  if ($operation == 'delete' && $courseId != NULL) {
    $action = deleteCourse($courseId);
  } elseif($operation == 'edit' && $courseId != NULL) {
    $course = $gtcs12_db->GetCourseByCourseId($courseId);
  }

  if ($operation == 'update') {
    $action = UpdateCourse($currentUser->ID);
  } else if(gtcs_user_has_role('author')) { // is this user a professor
    $action = AddCourse();
  } else {
    $action = "invalid role";
  }

  $quarter = isset($course) ? $course->Quarter : "selected";


function AddCourse()
{
  $title       = isset($_POST['title']) ? $_POST['title'] : NULL;
  $quarter     = isset($_POST['slctQuarter']) ? $_POST['slctQuarter'] : NULL;
  $year        = isset($_POST['slctYear']) ? $_POST['slctYear'] : NULL;
  $description = isset($_POST['inptDescription']) ? $_POST['inptDescription'] : NULL;

  if ($title == NULL ||
      $quarter == NULL ||
      $year == NULL ||
      $description == NULL) {
    return "Invalid input when creating course";
  }

  $gtcs12_db->AddCourse($courseId, $title, $quarter, $year, $userId, $description);
  return "course created";

}

function UpdateCourse($userId)
{
  $courseId    = isset($_POST['courseid']) ? $_POST['courseid'] : NULL;
  $title       = isset($_POST['title']) ? $_POST['title'] : NULL;
  $quarter     = isset($_POST['slctQuarter']) ? $_POST['slctQuarter'] : NULL;
  $year        = isset($_POST['slctYear']) ? $_POST['slctYear'] : NULL;
  $description = isset($_POST['inptDescription']) ? $_POST['inptDescription'] : NULL;

  if ($courseId == NULL ||
      $title == NULL ||
      $quarter == NULL ||
      $year == NULL ||
      $description == NULL) {
    return "Invalid input when editing course";
  }

  $gtcs12_db->UpdateCourse($courseId, $title, $quarter, $year, $userId, $description);
  return "course edited";
}

function deleteCourse($courseId)
{
  global $gtcs12_db;
  $course = $gtcs12_db->GetCourseByCourseId($courseId);

  if ($course) { // course exists
    if ($course->FacultyId == $currentUser->ID) { // user owns course
      $gtcs12_db->DeleteCourse($courseId);
      return "course deleted";
    } else {
      return "not owner";
    }
  } else {
    return "course not found";
  }
}
?>
