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
    'deleteAll' => 'deleteAllStudents',
    'resetPassword' => 'resetStudentPassword',
    'resetAllPasswords' => 'resetAllStudentPasswords'
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
  GTCS_Users::updateStudentEnrollment($courseId, $oldUserId, false);

  return "Student deleted.";
}

function deleteAllStudents(&$ps)
{
  $courseId = ifsetor($_POST['courseid'], null);

  include_once(get_template_directory() . '/common/users.php');
  $studentList = GTCS_Users::getStudents($courseId);

  if (!function_exists('wp_delete_user')) {
    include(ABSPATH . './wp-admin/includes/user.php');
  }

  foreach ($studentList as $student) {
    $studentId = $student->ID;
    wp_delete_user($studentId);
    GTCS_Users::updateStudentEnrollment($courseId, $studentId, false);
  }

  return "All students deleted.";
}

function resetStudentPassword(&$ps)
{
  $studentId = $_POST['studentid'];
  $passwordLength = 15;
  $useSpecialChars = false;
  $newPassword = wp_generate_password($passwordLength, $useSpecialChars);

  $result = wp_update_user(array(
    'ID' => $studentId,
    'user_pass' => $newPassword)
  );

  if (is_wp_error($result))
    return "The password was not changed.";

  emailPassword($studentId, $newPassword);
  return "The password was reset and emailed to the student.";
}

function resetAllStudentPasswords(&$ps)
{
  $courseId = $_POST['courseid'];

  $passwordLength = 15;
  $useSpecialChars = false;

  include_once(get_template_directory() . '/common/users.php');
  $studentList = GTCS_Users::getStudents($courseId);

  foreach ($studentList as $student) {
    $studentId = $student->ID;
    $newPassword = wp_generate_password($passwordLength, $useSpecialChars);

    $updateResult = wp_update_user(array(
      'ID' => $studentId,
      'user_pass' => $newPassword)
    );

    // TODO log this error
    if (is_wp_error($updateResult)) { // password not updated
      echo "Error updating password for {$user->display_name} <br />";
      continue;
    }

    $emailResult = emailPassword($studentId, $newPassword);

    // TODO log this error
    if (!$emailResult) { // email not successfully sent
      echo "Error emailing password for {$user->display_name} <br />";
    }
  }

  return "All passwords were reset and emailed to the students.";
}

function emailPassword($userId, $password)
{
  $user = get_user_by('id', $userId);
  $subject = "[GTCS12] Your username and password";
  $siteUrl = site_url();

  $message = "<p>Usename: {$user->user_login}</p>";
  $message.= "<p>Password: {$password}</p>";
  $message.= "<p><a href=\"{$siteUrl}\">{$siteUrl}</a></p>";

  add_filter( 'wp_mail_content_type', 'set_html_content_type' );
  return wp_mail($user->user_email, $subject, $message);
  remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
}

function set_html_content_type() {
  return 'text/html';
}

function uploadFromCsv(&$ps)
{
  include_once(get_template_directory() . '/common/users.php');
  $courseId = $_POST['courseid'];
  GTCS_Users::enrollStudentsViaFile($courseId, 'studentdata');

  return "Students created.";
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

  $studentList = GTCS_Users::getStudents($courseId);
  foreach ($studentList as $student) {
    $lastName = get_user_meta($student->ID, 'last_name', true);
    $firstName = get_user_meta($student->ID, 'first_name', true);
    $student->real_name = "{$lastName}, {$firstName}";
  }

  $ps->hasStudents = sizeof($studentList) != 0;
  $ps->studentList = $studentList;
}

?>
