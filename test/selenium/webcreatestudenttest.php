<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class WebCreateStudentTest extends PHPUnit_Extensions_Selenium2TestCase
{
  private $userLogin = 'teststudent';
  private $userFirstName = 'testFirstName';
  private $userLastName = 'testLastName';
  private $userEmail = 'testEmail@example.com';

  protected function setUp()
  {
    $this->setBrowser('firefox');
    $this->setBrowserUrl('http://students.washington.edu/rladia/gtcs12/');
  }

  public function testLogin()
  {
    $this->url('http://students.washington.edu/rladia/gtcs12/');
    $this->loginAsProfessor();

    // TODO replace with 'click on Manage Students link'
    $this->url('http://students.washington.edu/rladia/gtcs12/students/');
    $this->assertEquals('Create student', $this->byId('create-student-title')->text());

    $this->submitStudentInformation();

    $this->assertEquals(true, $this->checkStudentInformation(),
      "User information not displayed after creation.");

    $this->deleteStudent();
    $this->assertEquals(false, $this->checkStudentInformation(),
      "User information displayed after deletion.");

  }

  private function deleteStudent()
  {
    // selection box identified by action_{login}
    $selectionBox = $this->byId("action_{$this->userLogin}");

    // select the delete option
    $this->select($selectionBox)->selectOptionByValue("delete");

    // click to confirm
    $this->byId("confirm_{$this->userLogin}")->click();
  }

  private function checkStudentInformation()
  {
    $isNameDisplayed = false; // set to true if the table contains the student's information

    // returns the rows from the table element
    $rowList = $this->byCssSelector('table')->elements($this->using('css selector')->value('tr'));

    // Linear search through the table looking for the newly created student
    for ($i = 0; $i < count($rowList) - 1; ++$i) { // ignore the 'All Students' table entry
      $row = $rowList[$i];

      // gets the th elements contained inside the table row
      // TODO th should be replaced with td
      // TODO should the table elements have names?
      $data = $row->elements($this->using('css selector')->value('th'));

      $login = $data[0]->text();
      $name = $data[1]->text();
      $displayName = $data[2]->text();


      if ($login === $this->userLogin &&
          $name === "$this->userLastName, $this->userFirstName" &&
          $displayName === "$this->userFirstName $this->userLastName") {
        return true;//$isNameDisplayed = true;
      }
    }

    return false;
  }

  private function submitStudentInformation()
  {
    $this->fillFormField('inptUserName', $this->userLogin);
    $this->fillFormField('inptFirstName', $this->userFirstName);
    $this->fillFormField('inptLastName', $this->userLastName);
    $this->fillFormField('inptEmail', $this->userEmail);

    $this->byName('create-student')->click();
  }

  private function loginAsProfessor()
  {
    $username = 'ksung';
    $userPassword = 'password';

    $usernameInput = $this->byName('log');
    $usernameInput->value($username);
    $this->assertEquals($username, $usernameInput->value());

    $passwordInput = $this->byName('pwd');
    $passwordInput->value($userPassword);
    $this->assertEquals($userPassword, $passwordInput->value());

    $this->byId('loginform')->submit();

    $element = $this->byId('loginbar');
    $this->assertContains('Hello, Kelvin Sung.', $element->text());
  }

  private function fillFormField($formName, $formValue)
  {
    $formField = $this->byName($formName);
    $formField->value($formValue);
    $this->assertEquals($formValue, $formField->value());
  }

}
?>
