<?php
  $current_user = wp_get_current_user();
  $courses = $gtcs12_db->GetCourseByFacultyId($current_user->ID);

  $courseId = $_GET ? $_GET['courseid'] : null;

  if($courseId == null && $courses != null)
    $courseId = $courses[0]->Id;

  $operation = $_POST ? $_POST['op'] : null;

  if(!$operation) // do nothing
  {}
  else if ($operation == 'create') // create new user
  {
    $newUserId = $gtcs12_db->AddUser($_POST['inptUserName'], 'password', $_POST['inptEmail'], $_POST['inptFirstName'], $_POST['inptLastName'], 'subscriber');
    $gtcs12_db->UpdateStudentEnrollment($courseId, $newUserId, true);
  }
  else if ($operation === 'delete') // unenroll and delete student
  {
    $oldUserId = $_POST['studentid'];

    if(!function_exists('wp_delete_user'))
    {
      include(ABSPATH . './wp-admin/includes/user.php');
    }

    wp_delete_user($oldUserId);
    $gtcs12_db->UpdateStudentEnrollment($courseId, $oldUserId, false);
  }
  else if($operation == 'file')
  {
    $courseid = $_POST['courseid'];
    $gtcs12_db->EnrollStudentsViaFile($courseid, 'studentdata');
  }
?>
