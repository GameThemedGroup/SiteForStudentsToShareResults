<?php
session_start();
$pageState = new stdClass();

initializePageState($pageState);
extract((array)$pageState);

function initializePageState(&$ps)
{
  $action = ifsetor($_POST['action'], null);

  // default values
  $ps->course = (object) array('Name' => '', 'Description' => '', 'Quarter' => '');

  $callAndRedirect = array(
    'delete' => 'deleteCourse',
    'update' => 'updateCourse',
    'create' => 'addCourse',
  );

  $callAndPersist = array(
    'edit' => 'editCourseSetup',
  );

  if ($action != null) {

    if (array_key_exists($action, $callAndRedirect)) {
      $_SESSION['userFeedback'] = call_user_func($callAndRedirect[$action], $ps);
      wp_redirect($_SERVER["REQUEST_URI"]);

    } else if (array_key_exists($action, $callAndPersist)) {
      $ps->userFeedback = call_user_func($callAndPersist[$action], $ps);

    } else {
      trigger_error("An invalid action was provided.", E_USER_WARNING);
    }

  } else {
    $ps->userFeedback = ifsetor($_SESSION['userFeedback'], "");
    $_SESSION['userFeedback'] = "";
  }

  $quarterList = array(
    'Fall' => true,
    'Winter' => false,
    'Spring' => false,
    'Summer' => false
  );

  $year = date('Y');
  $yearList[$year - 1] = false; // one year back
  $yearList[$year] = true;
  $yearList[$year + 1] = false; // one year forward

  if (ifsetor($ps->isEditing, false)) { // reset default selections
    $quarterList['Fall'] = true;
    $quarterList[$ps->course->Quarter] = true;

    $yearList[$year] = false;
    $yearList[$ps->course->Year] = true;
  }

  include_once(get_template_directory() . '/common/courses.php');
  $professorId = get_current_user_id();
  $courseList = GTCS_Courses::getCourseByFacultyId($professorId);

  $ps->courseList= $courseList;
  $ps->quarterList = $quarterList;
  $ps->yearList = $yearList;
}

function editCourseSetup(&$ps)
{
  $ps->courseId = ifsetor($_POST['courseid'], null);
  $ps->isEditing = $ps->courseId != null;

  if ($ps->courseId == null) {
    trigger_error(__FUNCTION__ . "
      - An invalid Course ID was provided.",
      E_USER_WARNING);

    return "There was an error attempting to edit the course.";
  }

  include_once(get_template_directory() . '/common/courses.php');
  $ps->course = GTCS_Courses::getCourseByCourseId($ps->courseId);
  return "Your are now editing the course";
}

function addCourse(&$ps)
{
  $professorId = get_current_user_id();
  $title       = ifsetor($_POST['title'], null);
  $quarter     = ifsetor($_POST['quarter'], null);

  // passing in 'year' to $_POST causes the page to not be found
  $year        = ifsetor($_POST['courseYear'], null);
  $description = ifsetor($_POST['description'], null);

  if (   $title == null
      || $quarter == null
      || $year == null
      || $description == null) {
    return "Invalid input when creating course";
  }

  $courseArgs = (object) array(
    'title' => $title,
    'quarter' => $quarter,
    'year' => $year,
    'professorId' => $professorId,
    'description' => $description
  );

  include_once(get_template_directory() . '/common/courses.php');
  GTCS_Courses::addCourse($courseArgs);
  return "course created";
}

function updateCourse(&$ps)
{
  $userId = get_current_user_id();
  $title       = ifsetor($_POST['title'], null);
  $quarter     = ifsetor($_POST['quarter'], null);
  $year        = ifsetor($_POST['courseYear'], null);
  $description = ifsetor($_POST['description'], null);
  $courseId    = ifsetor($_POST['courseid'], null);

  if (   $courseId == null
      || $title == null
      || $quarter == null
      || $year == null
      || $description == null) {
    return "Invalid input when editing course";
  }

  include_once(get_template_directory() . '/common/courses.php');
  GTCS_Courses::updateCourse($courseId, $title, $quarter, $year, $userId, $description);
  return "course edited";
}

function deleteCourse(&$ps)
{
  $professorId = get_current_user_id();
  $courseId = ifsetor($_POST['courseid'], null);

  include_once(get_template_directory() . '/common/courses.php');
  $course = GTCS_Courses::getCourseByCourseId($courseId);

  if ($course) { // course exists
    if ($course->FacultyId == $professorId) { // user owns course
      GTCS_Courses::deleteCourse($courseId);
      return "course deleted";
    } else {
      return "not owner";
    }
  } else {
    return "course not found";
  }
}
?>
