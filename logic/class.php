<?php
  $pageState = new stdClass();

  initializePageState($pageState);

  extract((array)$pageState);

  function initializePageState(&$ps)
  {
    setupCourseSelector($ps);
    setupPageView($ps);
  }

  function setupCourseSelector(&$ps)
  {
    $userId = wp_get_current_user()->ID;

    $isStudent = gtcs_user_has_role('student');
    $isProfessor = gtcs_user_has_role('professor');
    $isUser = $isStudent || $isProfessor;

    include_once(get_template_directory() . '/common/courses.php');

    if ($isProfessor) {
      $courseList = GTCS_Courses::getCourseByFacultyId($userId);
    } else if ($isStudent) {
      $courseList = GTCS_Courses::getCourseByStudentId($userId);
    }

    $courseId = ifsetor($ps->courseId,
      GTCS_Courses::getSelectedCourse());

    $ps->courseId = $courseId;
    $ps->courseList = $courseList;
    $ps->isOwner = true; // TODO fix this
    $ps->isUser = $isUser;
    $ps->pageCallback = site_url('/class/');
  }

  function setupPageView(&$ps)
  {
    include_once(get_template_directory() . '/common/courses.php');

    $courseId = ifsetor($ps->courseId,
      GTCS_Courses::getSelectedCourse());

    $course = GTCS_Courses::getCourseByCourseId($courseId);

    $isCourseSelected = !($course == null);
    if ($isCourseSelected) {
      setupCourseView($ps, $course);
    } else {
      setupBlankPage($ps);
    }

    $isStudent = gtcs_user_has_role('student');
    $isProfessor = gtcs_user_has_role('professor');

    $ps->course = $course;
    $ps->isCourseSelected = $isCourseSelected;
  }

  function setupCourseView(&$ps, $course)
  {
    $userId = wp_get_current_user()->ID;

    include_once(get_template_directory() . '/common/assignments.php');
    $professor = get_userdata($course->FacultyId);
    $assignmentList = GTCS_Assignments::getAllAssignments($course->ID);

    include_once(get_template_directory() . '/common/users.php');
    $studentList = GTCS_Users::getStudents($course->ID);

    $ps->assignmentList = $assignmentList;
    $ps->professor = $professor;
    $ps->studentList = $studentList;
  }

  function setupBlankPage(&$ps)
  {
    $isCourseSelected = false;

    $studentList = array();
    $assignmentList = array();

    $ps->assignmentList = $assignmentList;
    $ps->studentList= $studentList;
  }
?>
