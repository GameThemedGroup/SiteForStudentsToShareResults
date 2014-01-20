<?php
/*
 * Template Name: Manage Students
 * Description: Allows for the creation/deletion of students
 *
 * Author: Andrey Brushchenko
 * Date: 11/18/2013
 */

get_header(); ?>

<?php include_once(get_template_directory() . '/logic/students.php'); ?>
<?php if ($userFeedback): ?>
  <div id="action-box"><?php echo $userFeedback; ?></div>
<?php endif; ?>

<!-- Course Selector -->
<?php require_once(get_template_directory() . '/templates/course-selector.php'); ?>

<!-- Single Student Creation Form -->
<div id="create-student-box-top">
  <div id='create-student-title'>Create student</div>
  <form action="<?php echo site_url('/students/?courseId=' . $courseId) ?>" method="post">
    <div id="create-student-field">
      <p class="create-student-top">Username</p>
      <input class='create-student' type="text" name="inptUserName" required>
    </div>
    <div id="create-student-field">
      <p class="create-student-top">First Name</p>
      <input class='create-student' type="text" name="inptFirstName" required>
    </div>
    <div id="create-student-field">
      <p class="create-student-top">Last Name</p>
      <input class='create-student' type="text" name="inptLastName" required>
    </div>
    <div id="create-student-field">
      <p class="create-student-top">Email</p>
      <input class='create-student' type="text" name="inptEmail" required>
    </div>
    <div id="create-student-buttons">
      <input type="hidden" name="action" value="create">
      <input type="submit" value="Create"/>
      <a href="<?php echo site_url('/class/') ?>"><button type="button">Cancel</button></a>
    </div>
  </form>
</div>
<!-- Single Student Creation Form -->

<!-- Student File Upload Form -->
<div id="create-student-box-bottom">
  <div id='create-student-title'>Create students via file</div>
  <form action="<?php echo get_permalink() . "?courseId={$courseId}" ?>" method="post" enctype="multipart/form-data">
    <div id="create-student-field">
      <p class="create-student-bottom">Spreadsheet</p>
      <input type="file" name="studentdata">
    </div>
    <div id="create-student-buttons">
      <input type="hidden" name="courseId" value="<?php echo $courseId; ?>">
      <input type="hidden" name="action" value="csvUpload">
      <input type="submit">
      <a href="<?php echo site_url('/class/') ?>"><button type="button">Cancel</button></a>
    </div>
  </form>
</div>
<!-- Student File Upload Form -->

<!-- Student List Display -->
<div id='table'>
  <div id='table-title'>Manage enrolled students</div>
  <table>
    <thead>
      <tr>
        <th>Login</th>
        <th>Name</th>
        <th>Display Name</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>

<?php if(false == $hasStudents): ?>
  <tr>
    <th class="center" colspan="4">This course has no enrolled students</th>
  </tr>
<?php else: ?>

  <?php foreach($studentList as $student): ?>
    <tr>
      <th><?php echo $student->user_login; ?></th>
      <th><?php echo $student->real_name; ?></th>
      <th><?php echo $student->display_name; ?></th>
      <th>
        <form action="<?php echo site_url('/students/?courseId=') . $courseId; ?>"
          method="post">

          <select name="action">
            <option disabled="disabled" selected>Choose an action</option>
            <option value="delete">Delete</option>
            <option value="resetPassword">Reset Password</option>
          </select>

          <input type="hidden" name="studentid" value="<?php echo $student->ID; ?>">
          <input type="hidden" name="courseId" value="<?php echo $courseId; ?>">
          <input type="submit" value="Confirm"/>
        </form>
      </th>
    </tr>
  <?php endforeach; ?>

  <tr>
    <th colspan="3"><strong>All Students</strong></th>
    <th>
      <form action="<?php echo site_url('/students/?courseId=') . $courseId; ?>"
        method="post">

        <select name="action">
          <option disabled="disabled" selected>Choose an action</option>
          <option value="deleteAll">Delete</option>
          <option value="resetAllPasswords">Reset Passwords</option>
        </select>

        <input type="hidden" name="courseId" value="<?php echo $courseId; ?>">
        <input type="submit" value="Confirm"/>
      </form>
    </th>
  </tr>
<?php endif; ?>

    </tbody>
  </table>
</div>
<!-- Student List Display -->

<?php get_footer() ?>
