<?php
$pageState = new stdClass();

initializePageState($pageState);
extract((array)$pageState);

function initializePageState(&$ps)
{
  $isProfessor = gtcs_user_has_role('professor');

  if (!$isProfessor) {
    echo "You do not have permission to view this page. <br />";
    return;
  }

  $action = ifsetor($_POST['action'], null);

  $actionList = array(
    'create'   => 'createStudent',
    'csvUpload' => 'uploadFromCsv',
    'delete' => 'deleteStudent',
    'emailPassword' => 'emailStudentPassword'
  );

  setupDefaultValues($ps);
  getSelectedCourseId($ps);
  if ($action != null) {
    if (array_key_exists($action, $actionList)) {
      $ps->userFeedback = call_user_func($actionList[$action], $ps);
    } else {
      trigger_error("An invalid action was provided.", E_USER_WARNING);
    }
  }

  setupStudentList($ps);
}

function setupDefaultValues(&$ps)
{
  $ps->userFeedback = '';
}

function createStudent(&$ps)
{
  $courseId = $ps->courseId;

  include_once(get_template_directory() . '/common/users.php');
  $newUserId = GTCS_Users::addUser($_POST['inptUserName'], 'password', $_POST['inptEmail'], $_POST['inptFirstName'], $_POST['inptLastName'], 'student');
  GTCS_Users::updateStudentEnrollment($courseId, $newUserId, true);

  return "Student created.";
}

function deleteStudent(&$ps)
{
  $courseId = $ps->courseId;

  $oldUserId = $_POST['studentid'];

  if (!function_exists('wp_delete_user')) {
    include(ABSPATH . './wp-admin/includes/user.php');
  }

  include_once(get_template_directory() . '/common/users.php');
  wp_delete_user($oldUserId);
  GTCS_Users::UpdateStudentEnrollment($courseId, $oldUserId, false);

  return "Student deleted.";
}

function uploadFromCsv(&$ps)
{
  include_once(get_template_directory() . '/common/users.php');
  $courseId = $_POST['courseid'];
  GTCS_Users::EnrollStudentsViaFile($courseId, 'studentdata');

  return "Students created."
}

// Returns the id of the course if one selected, otherwise returns the id of
// the default course
function getSelectedCourseId(&$ps)
{
  $userId = get_current_user_id();

  include_once(get_template_directory() . '/common/courses.php');
  $courseList = GTCS_Courses::getCourseByFacultyId($userId);

  $courseId = ifsetor($_GET['courseid'], null);

  if($courseId == null && $courseList != null)
    $courseId = $courseList[0]->Id;

  $ps->courseId = $courseId;
  $ps->courseList = $courseList;
}

function setupStudentList(&$ps)
{
  include_once(get_template_directory() . '/common/users.php');
  $courseId = $ps->courseId;

  $studentIds = GTCS_Users::getStudents($courseId);
  $studentList = get_users(array('include' => $studentIds));

  $ps->hasStudents = sizeof($studentList) != 0;
  $ps->studentList = $studentList;
}

?>
