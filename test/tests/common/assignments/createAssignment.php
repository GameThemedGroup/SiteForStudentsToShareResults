<?php

include_once(get_template_directory() . '/common/assignments.php');
include_once(get_template_directory() . '/common/courses.php');

class Tests_createAssignment extends WP_UnitTestCase {
  function test_standard() {
    $professorId = $this->factory->user->create(array('role' => 'professor'));
    $courseId = GTCS_Courses::addCourse((object)array(
      'title' => 'test course',
      'quarter' => 'Fall',
      'professorId' => $professorId
    ));

    $args = (object) array(
      'title' => 'test',
      'courseId' => $courseId,
      'description' => 'test description',
      'professorId' => $professorId,
      'link' => 'http://www.example.com',
      'isEnabled' => true
    );
   
    $assignmentId = GTCS_Assignments::createAssignment($args);
    
    $this->assertEquals($courseId, get_post_meta($assignmentId, 'course', true));
    $this->assertEquals(true, get_post_meta($assignmentId, 'isEnabled', true));
    $this->assertEquals('http://www.example.com', get_post_meta($assignmentId, 'link', true));
  }
}
