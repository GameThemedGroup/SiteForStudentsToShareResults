<?php
  $current_user = wp_get_current_user();

  include_once(get_template_directory() . '/common/users.php');
  include_once(get_template_directory() . '/common/courses.php');

  $courses = GTCS_Courses::getCourseByFacultyId($current_user->ID);

  $courseId = $_GET ? $_GET['courseid'] : null;

  if($courseId == null && $courses != null)
    $courseId = $courses[0]->Id;

  $operation = $_POST ? $_POST['op'] : null;

  if(!$operation) // do nothing
  {}
  else if ($operation == 'create') // create new user
  {
    $newUserId = GTCS_Users::addUser($_POST['inptUserName'], 'password', $_POST['inptEmail'], $_POST['inptFirstName'], $_POST['inptLastName'], 'student');
    GTCS_Users::updateStudentEnrollment($courseId, $newUserId, true);
  }
  else if ($operation === 'delete') // unenroll and delete student
  {
    $oldUserId = $_POST['studentid'];

    if(!function_exists('wp_delete_user'))
    {
      include(ABSPATH . './wp-admin/includes/user.php');
    }

    wp_delete_user($oldUserId);
    GTCS_Users::UpdateStudentEnrollment($courseId, $oldUserId, false);
  }
  else if($operation == 'file')
  {
    $courseid = $_POST['courseid'];
    GTCS_Users::EnrollStudentsViaFile($courseId, 'studentdata');
  }

  $studentIds = GTCS_Users::getStudents($courseId);
  $students = get_users(array('include' => $studentIds));
?>
