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
<div id="sidebar-menu">
  <div id="sidebar-menu-title">Courses</div>
  <ul class="sidebar-menu">
<?php if($courseList) : ?>
<?php   foreach($courseList as $course) : ?>
<?php     if($courseId == $course->Id) : ?>
    <li class="sidebar-menu">
      <p class="sidebar-menu-top"><?php echo $course->Name ?></p>
      <p class="sidebar-menu-bottom"><?php echo $course->Quarter . ', ' . $course->Year ?></p>
    </li>
<?php     else : ?>
    <li class="sidebar-menu">
      <a class="sidebar-menu" href="<?php echo site_url('/students/?courseid=' . $course->Id) ?>">
        <p class="sidebar-menu-top"><?php echo $course->Name ?></p>
        <p class="sidebar-menu-bottom"><?php echo $course->Quarter . ', ' . $course->Year ?></p>
      </a>
    </li>
<?php     endif ?>
<?php   endforeach ?>
<?php else : ?>
    <li class="sidebar-menu-center">You have no courses</li>
<?php endif ?>
  </ul>
</div>
<!-- Course Selector -->

<!-- Single Student Creation Form -->
<div id="create-student-box-top">
  <div id='create-student-title'>Create student</div>
  <form action="<?php echo site_url('/students/?courseid=' . $courseId) ?>" method="post">
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
  <form action="<?php echo get_permalink() . "?courseid={$courseId}" ?>" method="post" enctype="multipart/form-data">
    <div id="create-student-field">
      <p class="create-student-bottom">Spreadsheet</p>
      <input type="file" name="studentdata">
    </div>
    <div id="create-student-buttons">
      <input type="hidden" name="courseid" value="<?php echo $courseId; ?>">
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
        <th>Student</th>
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
    <th><?php echo $student->display_name; ?></th>
    <th>
      <form action="<?php echo site_url('/students/') ?>" method="post">
        <select name="action">
          <option disabled="disabled" selected>Choose an action</option>
          <option value="delete">Delete</option>
          <option value="emailPassword">Email Password</option>
        </select>
        <input type="hidden" name="studentid" value="<?php echo $student->ID; ?>">
        <input type="hidden" name="courseid" value="<?php echo $courseId; ?>">
        <input type="submit" value="Confirm"/>
      </form>
    </th>
  </tr>
<?php endforeach; ?>
<?php endif; ?>
    </tbody>
  </table>
</div>
<!-- Student List Display -->

<?php get_footer() ?>
