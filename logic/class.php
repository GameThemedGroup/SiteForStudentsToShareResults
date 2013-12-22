<?php
  $pageState = array();

  initializePageState($pageState);

  extract($pageState);

  function initializePageState(&$pageState)
  {
    $isStudent = gtcs_user_has_role('student');
    $isProfessor = gtcs_user_has_role('professor');

    if ($isStudent || $isProfessor) {
      $isUser = true;
    } else {
      $isUser =  false;
    }

    setupPageView($pageState);
    $pageState['isUser'] = $isUser;
  }

  function setupPageView(&$pageState)
  {
    $userId = wp_get_current_user()->ID;
    $courseId = ifsetor($_GET['id'], null);

    if ($courseId == null) {
      $courseId = selectDefaultCourse($userId);
    }

    include_once(get_template_directory() . '/common/courses.php');
    $course = ($courseId != null)
      ? GTCS_Courses::getCourseByCourseId($courseId)
      : null; // no course could be selected

    if ($course == null)
      setupBlankPage($pageState);
    else
      setupCourseView($pageState, $course);

    $isStudent = gtcs_user_has_role('student');
    $isProfessor = gtcs_user_has_role('professor');

    if ($isProfessor)
      $courseList = GTCS_Courses::getCourseByFacultyId($userId);
    else if ($isStudent)
      $courseList = GTCS_Courses::getCourseByStudentId($userId);

    $pageState = array_merge($pageState, compact(
      'course',
      'courseId',
      'courseList'
    ));
  }

  function setupCourseView(&$pageState, $course)
  {
    $userId = wp_get_current_user()->ID;

    $hasCourse = true;

    include_once(get_template_directory() . '/common/assignments.php');
    $professor = get_userdata($course->FacultyId);
    $assignmentList = GTCS_Assignments::getAllAssignments($course->ID);

    include_once(get_template_directory() . '/common/users.php');
    $studentIds = GTCS_Users::GetStudents($course->ID);
    $studentList = get_users(array('include' => $studentIds));

    $isOwner = ($course->FacultyId == $userId);

    $pageState = array_merge($pageState, compact(
      'assignmentList',
      'hasCourse',
      'isOwner',
      'professor',
      'studentList'
    ));
  }

  function setupBlankPage(&$pageState)
  {
    $hasCourse = false;

    $studentList = array();
    $assignmentList = array();

    $isOwner = gtcs_user_has_role('professor');

    $pageState = array_merge($pageState, compact(
      'assignmentList',
      'hasCourse',
      'isOwner',
      'studentList'
    ));
  }

  function selectDefaultCourse($userId)
  {
    $isStudent = gtcs_user_has_role('student');
    $isProfessor = gtcs_user_has_role('professor');

    include_once(get_template_directory() . '/common/courses.php');
    if ($isProfessor)
      $courseList = GTCS_Courses::getCourseByFacultyId($userId);
    else if ($isStudent)
      $courseList = GTCS_Courses::getCourseByStudentId($userId);

    return ifsetor($courseList[0]->Id, null);
  }
?>
