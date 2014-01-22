<?php

include_once(get_template_directory() . '/common/users.php');
include_once(get_template_directory() . '/common/courses.php');

class Tests_updateEnrollment extends WP_UnitTestCase {

  function test_standard() 
  {
    $studentId = $this->factory->user->create(array('role' => 'student'));
    $professorId = $this->factory->user->create(array('role' => 'professor'));

    $courseId = GTCS_Courses::addCourse((object)array(
      'title' => 'test course',
      'quarter' => 'Fall',
      'professorId' => $professorId
    ));

    GTCS_Users::updateStudentEnrollment($courseId, $studentId, true); 
    
    $course = GTCS_Courses::getCourseByStudentId($studentId);
    $this->assertCount(1, $course); // enrolled in one course
    $this->assertEquals($courseId, $course[0]->Id);

    GTCS_Users::updateStudentEnrollment(1, $studentId, false); 
    $course = GTCS_Courses::getCourseByStudentId($studentId);
    $this->assertCount(0, $course); // enrolled in no courses
  }

  function test_invalidCourseId() 
  {
    $studentId = $this->factory->user->create(array('role' => 'student'));
    $professorId = $this->factory->user->create(array('role' => 'professor'));

    $courseId = GTCS_Courses::addCourse((object)array(
      'title' => 'test course',
      'quarter' => 'Fall',
      'professorId' => $professorId
    ));

    ob_start(); // prevent errors from printing
    ini_set("error_log", "/dev/null"); // prevent errors from printing 
    $result = GTCS_Users::updateStudentEnrollment(-1, $studentId, true); 
    ob_end_clean();

    $this->assertSame(false, $result, 'Update should return failure.');
  }

  function test_professorIdNotProfessor() 
  {
    $studentId = $this->factory->user->create(array('role' => 'student'));

    // creating a user without a professor role 
    $professorId = $this->factory->user->create(array('role' => 'student'));

    $courseId = GTCS_Courses::addCourse((object)array(
      'title' => 'test course',
      'quarter' => 'Fall',
      'professorId' => $professorId
    ));

    $result = GTCS_Users::updateStudentEnrollment($courseId, $studentId, true); 
    $this->assertSame(false, $result, 'Update should return failure.');
  }
}
