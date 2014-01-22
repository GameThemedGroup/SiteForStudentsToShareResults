<?php

include_once(get_template_directory() . '/common/users.php');
include_once(get_template_directory() . '/common/courses.php');

class Tests_getStudents extends WP_UnitTestCase {

  function test_standard() 
  {
    $professorId = $this->factory->user->create(array('role' => 'professor'));

    for ($i = 0; $i < 5; ++$i) {
      $studentIdList[] = $this->factory->user->create(array('role' => 'student'));
    }

    $courseId = GTCS_Courses::addCourse((object)array(
      'title' => 'test course',
      'quarter' => 'Fall',
      'professorId' => $professorId
    ));

    // add students to course
    foreach ($studentIdList as $studentId) {
      GTCS_Users::updateStudentEnrollment($courseId, $studentId, true); 
    } 

    $studentList = GTCS_Users::getStudents($courseId);

    $this->assertCount(sizeof($studentIdList), $studentList);
    for ($i = 0; $i < 5; ++$i) {
      $this->assertContains($studentList[$i]->ID, $studentIdList);
    }
    
    // remove students from course
    foreach ($studentIdList as $studentId) {
      GTCS_Users::updateStudentEnrollment($courseId, $studentId, false); 
    }

    $studentList = GTCS_Users::getStudents($courseId);
    $this->assertCount(0, $studentList); 
  }

  function test_invalidCourseId() 
  {
    $studentList = GTCS_Users::getStudents(-1);
    $this->assertCount(0, $studentList); 
  }
}
