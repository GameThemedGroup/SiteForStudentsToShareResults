<?php
/**
 * Template Name: Manage Courses
 * Description: Allows for the creation/deletion/editing of courses owned by
 * current user
 *
 * Author: Andrey Brushchenko
 * Date: 11/1/2013
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
 * isEditing - true if the user is editing a course
 */
?>

<?php include_once(get_template_directory() . '/logic/courses.php'); ?>

<?php
  $course = $pageState->course;
  $courseList = $pageState->courseList;
  $isEditing = $pageState->isEditing;
  $quarterList = $pageState->quarterList;
  $userFeedback = $pageState->userFeedback;
  $yearList = $pageState->yearList;
?>

<?php if ($userFeedback): ?>
  <div id="error-box"><?php echo $userFeedback; ?></div>
<?php endif; ?>

  <form action="<?php echo $url['courses']; ?>" method="post">
    <div id='create-course-box'>

      <div id='create-course-title'>
        <?php if ($isEditing): ?>Edit Course
        <?php else: ?>Create Course
        <?php endif; ?>
      </div>

      <div id='create-course-field'>
        <p class="create-course">Title</p>
        <input class='create-course' type="text" name="title"
          value="<?php echo $course->Name; ?>" required>
        <br>
      </div>

      <div id='create-course-field'>
        <p class="create-course">Quarter</p>
        <select class='create-course' name='quarter'>
        <?php foreach ($quarterList as $quarter => $status): ?>
          <option value="<?php echo $quarter; ?>"
            <?php if ($status) echo "selected"; ?>>
            <?php echo $quarter; ?>
          </option>
        <?php endforeach; ?>
        </select>
      </div>

      <div id='create-course-field'>
        <p class="create-course">Year</p>
        <select class='create-course' name='year'>
          <?php foreach ($yearList as $year => $status): ?>
          <option value="<?php echo $year; ?>"
            <?php if ($status) echo "selected"; ?>>
            <?php echo $year; ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div id='create-course-field'>
        <p class="create-course">Description</p>
        <textarea cols="25" rows="10" autocomplete="off" name="description"
          required><?php echo $course->Description ?></textarea>
      </div>

      <div id="create-course-buttons">
        <?php if (!$isEditing): ?>
          <input type="hidden" name="action" value="create">
          <input type="submit" value="Create"/>
        <?php else: ?>
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="courseid" value="<?php echo $courseId; ?>">
          <input type="submit" value="Finish Editing"/>
        <?php endif; ?>
        <a class="lin" href="<?php echo $url['courses']; ?>">
          <button type="button">Cancel</button>
        </a>
      </div>

    </div> <!-- create-course-box -->
  </form>

  <div id='table'>
    <div id='table-title'>Manage courses</div>
    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th>Quarter</th>
          <th>Year</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($courseList): ?>
      <?php foreach ($courseList as $course): ?>
        <?php $courseLink = $url['my-class'] . '/?id=' . $course->Id; ?>
        <tr>
          <th><a href="<?php echo $courseLink; ?>"><?php echo $course->Name; ?></th>
          <th><?php echo $course->Quarter; ?></th>
          <th><?php echo $course->Year; ?></th>
          <th>
            <form action="<?php echo $url['courses']; ?>" method="post">
              <select name="action">
                <option disabled="disabled" selected>Choose an action</option>
                <option value="edit">Edit</option>
                <option value="delete">Delete</option>
              </select>
              <input type="hidden" name="courseid" value="<?php echo $course->Id; ?>">
              <input type="submit" value="Confirm"/>
            </form>
          </th>
        </tr>
      <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <th class="center" colspan="4">You have no courses</th>
        </tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div> <!-- table -->

</html>

<?php get_footer(); ?>
