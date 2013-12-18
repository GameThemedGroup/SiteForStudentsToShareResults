<?php
/**
 * Template Name: Manage Assignments
 * Description: Allows for the creation/deletion of assignments.
 *
 * Author: Andrey Brushchenko
 * Date: 10/6/2013
 */
get_header(); ?>

<?php include_once(get_template_directory() . '/logic/assignments.php'); ?>

<!DOCTYPE html>
<html lang="en">
<?php if($action != null) : ?>
  <div id="action-box"><?php echo $action ?></div>
<?php endif ?>

<form action="<?php echo get_permalink() . "?courseid={$courseId}"; ?>" method="post" enctype="multipart/form-data">
  <div id='create-assignment-box'>
    <div id='create-assignment-title'>
      <?php echo ($getOperation == 'edit') ? "Edit Assignment" : "Create Assignment"; ?>
    </div>
    <div id='create-assignment-field'>
      <p class="create-assignment">Title</p>
      <input class='create-assignment' type="text" name="inptTitle"
        value="<?php echo $assignment ? $assignment->post_title : ''; ?>" required>
    </div>
    <div id='create-assignment-field'>
      <p class="create-assignment">Description</p>
      <?php $descriptionValue = $assignment ? $assignment->post_content : ''; ?>
      <textarea cols="25" rows="10" autocomplete="off" name="txtDescription" required><?php echo $descriptionValue; ?></textarea>
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
  <?php if($getOperation == 'create' || $getOperation == 'delete' || $getOperation == '') : ?>
      <input type="hidden" name="op" value="create">
      <input type="submit" value="Create"/>
  <?php elseif($getOperation == 'edit') : ?>
      <input type="hidden" name="op" value="update">
      <input type="submit" value="Finish Editing"/>
  <?php endif ?>
      <input type="hidden" name="assignid" value="<?php echo $assignmentId ?>">
      <input type="hidden" name="courseid" value="<?php echo $courseId ?>">
      <a href="<?php echo site_url('/my-class/?id=' . $courseId) ?>"><button type="button">Cancel</button></a>
    </div>
  </div> <!-- Create-Assignment-Box -->
</form>

<?php if($isProfessor) : ?>
  <div id="sidebar-menu">
    <div id="sidebar-menu-title">Courses</div>
    <ul class="sidebar-menu">
<?php if($courses) : ?>
<?php   foreach($courses as $course) : ?>
<?php   if($courseId == $course->Id) : ?>
          <li class="sidebar-menu-selected">
<?php   else : ?>
          <li class="sidebar-menu">
<?php   endif ?>
        <a class="sidebar-menu" href="<?php echo site_url('/assignments/?courseid=' . $course->Id) ?>">
          <p class="sidebar-menu-top"><?php echo $course->Name ?></p>
          <p class="sidebar-menu-bottom"><?php echo $course->Quarter . ', ' . $course->Year ?></p>
        </a>
      </li>
<?php   endforeach ?>
<?php else : ?>
      <li class="sidebar-menu-center">You have no courses</li>
<?php endif ?>
    </ul>
  </div>

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
      <?php $assignments = $gtcs12_db->GetAllAssignments($courseId) ?>
      <?php if($assignments) : ?>
        <?php foreach($assignments as $assignment) : ?>
          <?php $assignLink = site_url('/assignment/?assignid=' . $assignment->AssignmentId); ?>
            <tr>
              <th><a href="<?php echo $assignLink ?>"><?php echo $assignment->Title ?></a></th>
              <th><?php echo date('F d, Y', strtotime($assignment->Date)); ?></th>
              <th>
                <form action="<?php echo site_url('/assignments/') ?>" method="get">
                  <select name="op">
                    <option disabled="disabled" selected>Choose an action</option>
                    <option value="edit">Edit</option>
                    <option value="delete">Delete</option>
                  </select>
                  <input type="hidden" name="assignid" value="<?php echo $assignment->AssignmentId ?>">
                  <input type="hidden" name="courseid" value="<?php echo $courseId ?>">
                  <input type="submit" value="Confirm"/>
                </form>
              </th>
            </tr>
        <?php endforeach; ?>
      <?php else: ?>
            <tr>
              <th class="center" colspan="3">You have no assignments for this course</th>
            </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<?php endif ?>
</html>

<?php get_footer(); ?>
