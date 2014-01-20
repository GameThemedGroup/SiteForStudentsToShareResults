<?php
  $pageState = array();

  initializePageState($pageState);

  extract($pageState);

  function initializePageState(&$pageState)
  {
    setupCourseSelector($pageState);
    setupPageView($pageState);
  }

  function setupCourseSelector(&$pageState)
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

    $courseId = ifsetor($pageState['courseId'],
      GTCS_Courses::getSelectedCourse());

    $pageState['courseId'] = $courseId;
    $pageState['courseList'] = $courseList;
    $pageState['isOwner'] = true; // TODO fix this
    $pageState['isUser'] = $isUser;
    $pageState['pageCallback'] = site_url('/class/');
  }

  function setupPageView(&$pageState)
  {
    include_once(get_template_directory() . '/common/courses.php');

    $courseId = ifsetor($pageState['courseId'],
      GTCS_Courses::getSelectedCourse());

    $course = GTCS_Courses::getCourseByCourseId($courseId);

    $isCourseSelected = !($course == null);
    if ($isCourseSelected) {
      setupCourseView($pageState, $course);
    } else {
      setupBlankPage($pageState);
    }

    $isStudent = gtcs_user_has_role('student');
    $isProfessor = gtcs_user_has_role('professor');

    $pageState = array_merge($pageState, compact(
      'course',
      'isCourseSelected'
    ));
  }

  function setupCourseView(&$pageState, $course)
  {
    $userId = wp_get_current_user()->ID;

    include_once(get_template_directory() . '/common/assignments.php');
    $professor = get_userdata($course->FacultyId);
    $assignmentList = GTCS_Assignments::getAllAssignments($course->ID);

    include_once(get_template_directory() . '/common/users.php');
    $studentList = GTCS_Users::getStudents($course->ID);

    $pageState = array_merge($pageState, compact(
      'assignmentList',
      'professor',
      'studentList'
    ));
  }

  function setupBlankPage(&$pageState)
  {
    $isCourseSelected = false;

    $studentList = array();
    $assignmentList = array();

    $pageState = array_merge($pageState, compact(
      'assignmentList',
      'studentList'
    ));
  }
?>
