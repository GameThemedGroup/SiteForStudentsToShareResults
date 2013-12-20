<?php
/**
 * Template Name: My Class
 * Description: Shows course information, assignments, and other students.
 *
 * Author: Andrey Brushchenko
 * Date: 10/6/2013
 */
get_header(); ?>

<?php include_once(get_template_directory() . '/logic/my-class.php'); ?>

<?php if (!isset($course)) : ?>
  <div id="action-box">Course can not be found</div>
  </html>
  <?php get_footer(); return; ?>
<?php endif; ?>

  <div id="class-whole">
    <div id="class-title"><?php echo $course->Name ?>
      <?php if($isOwner) : ?>
        <a id="link" href='<?php echo site_url('/courses/') . "?editId=" . $courseId;?>'>
          Edit Course
        </a>
      <?php endif; ?>
    </div>

    <div id="class-info1"><b>Professor </b>
      <?php echo $professor->last_name . ', ' . $professor->first_name . ' '; ?>
      [<a href="<?php echo $professor_link; ?>">
        <?php echo $professor->user_login; ?>
      </a>]
    </div>

    <div id="class-info2">
      <b>Email </b><?php echo $professor->user_email; ?>
    </div>

    <div id="class-info1">
      <b>Quarter </b><?php echo "{$course->Quarter}, {$course->Year}"; ?>
    </div>

    <div id="class-description">
      <b><p>Description</p></b>
      <?php echo ($course->Description
        ? nl2br($course->Description)
        : "This course has no description") ?>
    </div>

  </div> <!-- class-whole -->

<!-- Course Selector -->
<?php if($isOwner) : ?>
  <div id="sidebar-menu">
    <div id="sidebar-menu-title">My Courses</div>
    <ul class="sidebar-menu">
<?php foreach($courseList as $course) : ?>
<?php   if($courseId == $course->Id) : ?>
      <li class="sidebar-menu-selected">
<?php   else : ?>
      <li class="sidebar-menu">
<?php   endif ?>
        <a href="<?php echo site_url('/my-class/?id=' . $course->Id) ?>">
          <p class="sidebar-menu-top"><?php echo $course->Name ?></p>
          <p class="sidebar-menu-bottom"><?php echo $course->Quarter . ', ' . $course->Year ?></p>
        </a>
      </li>
<?php endforeach; ?>
      <li class="sidebar-menu-center"><a class="action" href="<?php echo site_url('/courses/') ?>">Create course</a></li>
    </ul>
  </div>
<?php endif; ?> <!-- sidebar-menu -->
<!-- Course Selector -->

<!-- Student List -->
  <div id="sidebar-menu">
    <div id="sidebar-menu-title">Students</div>
      <ul class="sidebar-menu">
<?php
  include_once(get_template_directory() . '/common/users.php');
  $studentIds = GTCS_Users::GetStudents($courseId);
  $students = get_users(array('include' => $studentIds));
?>
<?php if ($students) : ?>
<?php   foreach($students as $student) : ?>
              <li class="sidebar-menu">
                <p class="sidebar-menu-middle"><a href="<?php echo site_url('/profile/?user=' . $student->ID) ?>"><?php echo $student->display_name?></a></p>
              </li>
<?php   endforeach ?>
<?php endif ?>
<?php if($isOwner) : ?>
        <li class="sidebar-menu-center"><a class="action" href="<?php echo site_url('/students/?id=' . $courseId) ?>">Add students</a></li>
<?php endif ?>
        </ul>
    </div>
<!-- Student List -->

<div id="table">
  <div id='table-title'>Assignments</div>
  <table>
    <thead>
      <tr>
        <th>Assignment</th>
        <th>Date Posted</th>
        <th>Status</th>
      </tr>
    </thead>

    <tbody>
      <?php if($assignmentList): ?>
      <?php foreach($assignmentList as $assignment): ?>
        <?php
          $status = get_post_meta($assignment->AssignmentId, 'isEnabled', true);
          $assignlink = site_url('/assignment/?id=' . $assignment->AssignmentId);
        ?>

        <tr>
          <th><a href="<?php echo $assignlink; ?>">
            <?php echo $assignment->Title; ?></a>
          </th>

          <th><?php echo date('F d, Y', strtotime($assignment->Date)); ?></th>
          <th><?php echo $status ? "Open" : "Closed"; ?></th>
        </tr>
      <?php endforeach; ?>
      <?php elseif ($isOwner == false): ?>
        <tr>
          <th class="center" colspan="2">There are no assignments</th>
        </tr>
      <?php endif; ?>

      <?php if ($isOwner): ?>
        <tr class="break">
          <th></th>
          <th></th>
          <th></th>
        </tr>
        <tr>
          <th class="action" colspan="3">
            <a class="action" href="<?php echo site_url('/assignments/?id=' . $courseId) ?>">
              Create an assignment
            </a>
          </th>
        </tr>
      <?php endif; ?>

    </tbody>
  </table>
</div>

</html>

<?php get_footer(); ?>
