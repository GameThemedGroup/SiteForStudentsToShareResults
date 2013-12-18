<?php
/**
 * Template Name: Manage Assignments
 * Description: Allows for the creation/deletion of assignments.
 *
 * Author: Andrey Brushchenko
 * Date: 10/6/2013
 */
get_header(); ?>

<?php
/* Expected State Variables:
 ******************************************************************************
 * userFeedback - Message to display to user after a successful or failed
 *   action. Set to '' if there is no feedback to display.
 *
 * course - an object containing the following fields
 *   Name - the course name
 *   Description - the course description
 *   Quarter - text represntation of the academic quarter. Ex. Summer, Spring
 *
 * courseList - a list of courses beloging to the professor.
 *   $courseList == null if the professor has no courses
 *
 * quarterList - an array of academic quarters in the form
 *   (quarter => selectStatus).
 *   Ex. "Summer" => true
 *       "Fall"   => false
 *   The user will be able to select between Summer and Fall with Summber being
 *   the default selection.
 *
 * url[] - array containing urls to the following pages
 *   courses  => this page
 *   my-class => templates/my-class.php
 *
 * isEditing - true if the user is editing a course
 */
?>

<?php include_once(get_template_directory() . '/logic/assignments.php'); ?>

<!DOCTYPE html>
<html lang="en">
<?php if($action) : ?>
  <div id="action-box"><?php echo $userFeedback; ?></div>
<?php endif ?>

<form action="<?php echo get_permalink(); ?>" method="post" enctype="multipart/form-data">
  <div id='create-assignment-box'>
    <div id='create-assignment-title'>
      <?php if ($isEditing): ?>Edit Assignment
      <?php else: ?>Create Assignment
      <?php endif; ?>
    </div>

    <div id='create-assignment-field'>
      <p class="create-assignment">Title</p>
      <input class='create-assignment' type="text" name="title"
        value="<?php echo $displayedAssignment->post_title; ?>" required>
    </div>

    <div id='create-assignment-field'>
      <p class="create-assignment">Description</p>
      <textarea cols="25" rows="5" name="description" required><?php echo $displayedAssignment->post_content; ?></textarea>
    </div>

    <div id='create-assignment-field'>
      <p class="create-assignment">Sample File</p>
      <input class='create-assignment' type="file" name="jar">
    </div>

    <div id='create-assignment-field'>
      <p class="create-assignment">Preview Image</p>
      <input class='create-assignment' type="file" name="image" accept="image/*">
    </div>

    <div id="create-assignment-buttons">

      <input type="hidden" name="assignId" value="<?php echo $assignmentId; ?>">
      <input type="hidden" name="courseId" value="<?php echo $courseId; ?>">

    <?php if ($isEditing): ?>
        <input type="hidden" name="action" value="update">
        <input type="submit" value="Finish Editing"/>
    <?php else: ?>
        <input type="hidden" name="action" value="create">
        <input type="submit" value="Create"/>
    <?php endif; ?>
      <a href="<?php echo site_url('assignments/?id=' . $courseId) ?>">
        <button type="button">Cancel</button>
      </a>
    </div> <!-- create-assignment-buttons -->
  </div> <!-- Create-Assignment-Box -->
</form>

<?php if($isProfessor) : ?>
  <div id="sidebar-menu">
    <div id="sidebar-menu-title">Courses</div>
    <ul class="sidebar-menu">
<?php if($courseList) : ?>
<?php   foreach($courseList as $course) : ?>
<?php   if($courseId == $course->Id) : ?>
          <li class="sidebar-menu-selected">
<?php   else : ?>
          <li class="sidebar-menu">
<?php   endif ?>
        <a class="sidebar-menu" href="<?php echo site_url('/assignments/?id=' . $course->Id) ?>">
          <p class="sidebar-menu-top"><?php echo $course->Name ?></p>
          <p class="sidebar-menu-bottom"><?php echo $course->Quarter . ', ' . $course->Year ?></p>
        </a>
      </li>
<?php   endforeach ?>
<?php else : ?>
      <li class="sidebar-menu-center">You have no courses</li>
<?php endif ?>
    </ul>
  </div> <!-- sidebar-menu -->

<div id='table'>
  <div id='table-title'>Manage Assignments</div>
  <table>
    <thead>
      <tr>
        <th>Title</th>
        <th>Date Posted</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if(!$assignmentList) : ?>
        <tr>
         <th class="center" colspan="3">You have no assignments for this course</th>
        </tr>
      <?php else: ?>
      <?php foreach($assignmentList as $assignment) : ?>
        <?php $assignLink = site_url('/assignment/?assignid=' . $assignment->AssignmentId); ?>
          <tr>
            <th><a href="<?php echo $assignLink; ?>">
               <?php echo $assignment->Title ?></a>
            </th>

            <th><?php echo date('F d, Y', strtotime($assignment->Date)); ?></th>
            <th>
              <form action="
                <?php echo site_url('/assignments/?id=') . $courseId; ?>" method="post">

                <select name="action">
                  <option disabled="disabled" selected>Choose an action</option>
                  <option value="edit">Edit</option>
                  <option value="delete">Delete</option>
                </select>

                <input type="hidden" name="assignmentId"
                  value="<?php echo $assignment->AssignmentId; ?>">

                <input type="hidden" name="courseId"
                  value="<?php echo $courseId; ?>">

                <input type="submit" value="Confirm"/>

              </form>
            </th>
          </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
</html>

<?php get_footer(); ?>
