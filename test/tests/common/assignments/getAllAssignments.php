<?php

include_once(get_template_directory() . '/common/assignments.php');
include_once(get_template_directory() . '/common/courses.php');

class Tests_updateAssignment extends WP_UnitTestCase {
  
  function test_standard() {
    $professorId = $this->factory->user->create(array('role' => 'professor'));
    $courseId = GTCS_Courses::addCourse((object)array(
      'title' => 'test course',
      'quarter' => 'Fall',
      'professorId' => $professorId
    ));

    $numElements = range(1, 3);
    $argList = array();
    $idList = array();
    foreach ($numElements as $i) {
      $argList[] = (object) array(
        'title' => 'title',
        'courseId' => $courseId,
        'description' => 'description',
        'professorId' => $professorId,
        'link' => 'link', 
        'isEnabled' => true
      );

      $idList[] = GTCS_Assignments::createAssignment(end($argList));

      $assignmentId = end($idList);
      $this->assertEquals($courseId, get_post_meta($assignmentId, 'course', true));
      $this->assertEquals(true, get_post_meta($assignmentId, 'isEnabled', true));
      $this->assertEquals('link', get_post_meta($assignmentId, 'link', true));
    }

    foreach ($idList as $id) {
      GTCS_Assignments::updateAssignment(
        $id, 0, 0, 
        "new title{$i}", 
        "new description{$i}",
        "new link{$i}",
        false
      );

      $post = get_post($id);

      $this->assertEquals("new title{$i}", $post->post_title);
      $this->assertEquals("new description{$i}", $post->post_content);
      $this->assertEquals("new link{$i}", get_post_meta($id, 'link', true));
      $this->assertEquals(false, get_post_meta($id, 'isEnabled', true));
    }
  }
}
