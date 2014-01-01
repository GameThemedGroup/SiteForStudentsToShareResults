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
 * assignmentId
 *
 * assignmentList - a list of assignments belonging to the course
 *   $sizeof(assignmentList) == 0 if the course has no assignments
 *
 * courseId
 *
 * courseList
 *
 * displayedAssignment - an object containing the following fields
 *
 * isEditing - true if the user is editing the assignment
 *
 * userFeedback - Message to display to user after a successful or failed
 *   action. Set to '' if there is no feedback to display.
*/
?>

<?php include_once(get_template_directory() . '/logic/assignments.php'); ?>

<?php if($userFeedback) : ?>
  <div id="action-box"><?php echo $userFeedback; ?></div>
<?php endif ?>

<!-- Assignment Creation Form -->
<form action="<?php echo site_url("assignments/?id={$courseId}"); ?>" method="post" enctype="multipart/form-data">
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
      <p class="create-assignment">External URL</p>
      <input class='create-assignment' type="text" name="link"
        value="<?php echo $displayedAssignment->link; ?>">
    </div>

    <div id='create-assignment-field'>
      <p class="create-assignment">Description</p>
      <textarea cols="25" rows="5" name="description" required><?php echo $displayedAssignment->post_content; ?></textarea>
    </div>

<?php
// TODO Let professors add sample jar files
/*
    <div id='create-assignment-field'>
      <p class="create-assignment">Sample File</p>
      <input class='create-assignment' type="file" name="jar">
    </div>
*/
?>
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
<!-- Assignment Creation Form -->

<!-- Course Selector -->
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
<?php endif; ?>
    </ul>
  </div> <!-- sidebar-menu -->
<!-- Course Selector -->

<!-- Upload Assignments via XML form -->
<div id="create-student-box-bottom">
  <div id='create-student-title'>Upload Assignments from XML</div>
  <form action="<?php echo get_permalink() . "?id={$courseId}" ?>"
    method="post" enctype="multipart/form-data">

    <div id="create-student-field">
      <p class="create-student-bottom">XML</p>
      <input type="file" name="xml">
    </div>
    <div id="create-student-buttons">
      <input type="hidden" name="id" value="<?php echo $courseId; ?>">
      <input type="hidden" name="action" value="upload">
      <input type="submit" value="Upload">

      <a href="<?php echo site_url('/assignments/') . "?id={$courseId}"; ?>">
        <button type="button">Cancel</button></a>
    </div>
  </form>
</div>
<!-- Upload Assignments via XML form -->

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
    <?php if (!$assignmentList) : ?>
      <tr>
       <th class="center" colspan="3">You have no assignments for this course</th>
      </tr>
    <?php else: ?>
    <?php foreach ($assignmentList as $assignment) : ?>
      <?php $assignLink = site_url('/assignment/?id=' . $assignment->AssignmentId); ?>
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
</html>

<?php get_footer(); ?>
