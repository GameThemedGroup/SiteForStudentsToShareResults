<?php

include_once(get_template_directory() . '/common/global_functions.php');

class Tests_CreateAssignment extends WP_UnitTestCase {
  /*
  function setUp() {
    parent::setUp(); 
  }
*/

  function test_sunny_day() {
    /*
    switch_theme(wp_get_theme('GTCS12'));
    include_once(get_template_directory() . '/common/users.php');
    do_action('after_switch_theme', 'GTCS12');

    //$id = GTCS_Users::addUser('james', 'abc', 'bb@example.com', 'James', 'Dean', 'student');

    $user = new WP_User($id);
    var_dump($user->roles);
    //echo "isStudent: " . ($isAuthor ? "false" : "true");

    */
    global $wp_roles;
    var_dump($wp_roles->roles);
    $id = $this->factory->user->create(array('role'=>'student'));
    $isStudent = gtcs_user_has_role('student', $id);
    $this->assertEquals(true, $isStudent);
    $this->assertEquals("pass", "pass");
  }
}
