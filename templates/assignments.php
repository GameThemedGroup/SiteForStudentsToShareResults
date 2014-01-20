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
<?php include_once(get_template_directory() . '/templates/submit_assignment_form.php'); ?>

<!-- Course Selector -->
<?php require_once(get_template_directory() . '/templates/course-selector.php'); ?>

<!-- Upload Assignments via XML form -->
<div id="create-student-box-bottom">
  <div id='create-student-title'>Upload Assignments from XML</div>
  <form action="<?php echo get_permalink() . "?courseId={$courseId}" ?>"
    method="post" enctype="multipart/form-data">

    <div id="create-student-field">
      <p class="create-student-bottom">XML</p>
      <input type="file" name="xml">
    </div>
    <div id="create-student-buttons">
      <input type="hidden" name="courseId" value="<?php echo $courseId; ?>">
      <input type="hidden" name="action" value="upload">
      <input type="submit" value="Upload">

      <a href="<?php echo site_url('/assignments/') . "?courseId={$courseId}"; ?>">
        <button type="button">Cancel</button></a>
    </div>
  </form>
</div>
<!-- Upload Assignments via XML form -->

<!-- Assignment List -->
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
      <?php $assignLink = site_url('/assignment/?courseId=' . $assignment->AssignmentId); ?>
      <tr>
        <th><a href="<?php echo $assignLink; ?>">
           <?php echo $assignment->Title ?></a>
        </th>

        <th><?php echo date('F d, Y', strtotime($assignment->Date)); ?></th>
        <th>
          <form action="
            <?php echo site_url('/assignments/?courseId=') . $courseId; ?>" method="post">

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
<!-- Assignment List -->

<?php get_footer(); ?>
