<?php
  $userId = wp_get_current_user()->ID;
  $isStudent = gtcs_user_has_role('subscriber');
  $isProfessor = gtcs_user_has_role('author');

  $courseId = ifsetor($_GET['id'], null);

  $courseList = array();

  include_once(get_template_directory() . '/common/courses.php');

  if ($courseId != null) {
    $courseList = GTCS_Courses::getCourseByFacultyId($userId);

  } else {

    if ($isStudent) {
      $courseList = GTCS_Courses::getCourseByStudentId($userId);
    } elseif ($isProfessor) {
      $courseList = GTCS_Courses::getCourseByFacultyId($userId);
    }

    if ($courseList) { // set default selection to first course
      $courseId = $courseList[0]->Id;
    }
  }

  if ($courseId != null)
    $course = GTCS_Courses::getCourseByCourseId($courseId);

  $isOwner = false;
  if (isset($course))
  {
    include_once(get_template_directory() . '/common/assignments.php');
    $professor = get_userdata($course->FacultyId);
    $professorLink = site_url('/profile/?user=') . $course->FacultyId;
    $assignmentList = GTCS_Assignments::getAllAssignments($courseId);

    if($isProfessor) {
      $isOwner = ($course->FacultyId == $userId);
    }
  }
?>
