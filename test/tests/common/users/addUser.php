<?php

include_once(get_template_directory() . '/common/users.php');

class Tests_addUser extends WP_UnitTestCase {

  function test_standard() 
  {
    $login = 'jdoe';
    $password = 'password';
    $email = 'jdoe@example.com';
    $firstName = 'John';
    $lastName = 'Doe';
    $role = 'student';

    $newUserId = GTCS_Users::addUser(
      $login, $password, $email, $firstName, $lastName, $role);

    $user = new WP_User($newUserId);

    $this->assertEquals($login, $user->user_login);
    //$this->assertEquals($password, $user->user_pass); // returns hash of password
    $this->assertEquals($email, $user->user_email);
    $this->assertEquals($firstName, $user->first_name);
    $this->assertEquals($lastName, $user->last_name);
    $this->assertContains($role, $user->roles);
  }

  function test_emptyLoginName() 
  {
    $login = '';
    $password = 'password';
    $email = 'jdoe@example.com';
    $firstName = 'John';
    $lastName = 'Doe';
    $role = 'student';

    $this->markTestIncomplete(
      'Fail conditions have not been set for this function.'
    );

    // todo replace with the correct error thrown 
    //$this->setExpectedException('invalid input'); 
    $addUserResult = GTCS_Users::addUser(
      $login, $password, $email, $firstName, $lastName, $role);

    $this->assertEquals('WP_Error', get_class($addUserResult));
  }

  function test_existingLoginName() 
  {
    $login = 'admin';
    $password = 'password';
    $email = 'jdoe@example.com';
    $firstName = 'John';
    $lastName = 'Doe';
    $role = 'student';

    $this->markTestIncomplete(
      'Fail conditions have not been set for this function.'
    );

    $addUserResult = GTCS_Users::addUser(
      $login, $password, $email, $firstName, $lastName, $role);

    $this->assertEquals('WP_Error', get_class($addUserResult));
  }
}
