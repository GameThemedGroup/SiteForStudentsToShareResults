<?php
/* Course Selector
 *
 * Expected State Values
 *   courseList = array(),
 *   isOwner = true,
 *   isUser = true,
 *   pageCallback = '',
 */
?>
<?php if($isUser) : ?>
  <div id="sidebar-menu">
    <div id="sidebar-menu-title">My Courses</div>
    <ul class="sidebar-menu">
      <?php foreach($courseList as $course): ?>
        <li class=
          <?php echo ($courseId == $course->Id) ?
            "sidebar-menu-selected" :
            "sidebar-menu"; ?>>
          <a href="<?php echo $pageCallback . "?courseId={$course->Id}"; ?>">
            <p class="sidebar-menu-top"><?php echo $course->Name; ?></p>
            <p class="sidebar-menu-bottom">
              <?php echo "{$course->Quarter}, {$course->Year}"; ?></p>
          </a>
        </li>
      <?php endforeach; ?>

    <?php if ($isOwner): ?>
      <li class="sidebar-menu-center">
        <a class="action" href="<?php echo site_url('/courses/') ?>">Create course</a>
      </li>
    <?php endif; ?>

    </ul>
  </div>
<?php endif; ?>

