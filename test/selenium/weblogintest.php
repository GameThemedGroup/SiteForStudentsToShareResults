<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class WebLoginTest extends PHPUnit_Extensions_Selenium2TestCase
{
  protected function setUp()
  {
    $this->setBrowser('firefox');
    $this->setBrowserUrl('http://students.washington.edu/rladia/gtcs12/');
  }

  /*
  public function testTitle()
  {
    $this->url('http://students.washington.edu/rladia/gtcs12/');
    $this->assertEquals('GTCS12 | Just another WordPress site', $this->title());
  }
  */

  public function testLogin()
  {
    $this->url('http://students.washington.edu/rladia/gtcs12/');
    // Fill forms using value
    $this->timeouts()->implicitWait(30000);

    $usernameInput = $this->byName('log');
    $usernameInput->value('bbob');

    $passwordInput = $this->byName('pwd');
    $passwordInput->value('password');

    $this->byId('loginform')->submit();

    $element = $this->byId('loginbar');
    $this->assertContains('Hello, Billy Bob.', $element->text());
  }
  /*
  public function testColumnNames()
  {
    $this->url('http://students.washington.edu/rladia/gtcs12/');
    $element = $this->byId('recent-submission-feed-title');
    $this->assertEquals('Recent Submissions', $element->text());

    $element = $this->byId('project-sidebar-title');
    $this->assertEquals('Top Projects', $element->text());


    // Click Links to navigate
    $this->clickOnElement('link');
    //$this->waitForPageToLoad(1500);
    $element = $this->byId('pagetitle');
    $this->assertEquals('My Assignment 123', $element->text());



  }

   */
}
?>
