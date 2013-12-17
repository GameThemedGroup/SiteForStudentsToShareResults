<?php
/**
 * Template Name: Manage Courses
 * Description: Allows for the creation/deletion/editing of courses owned by current user
 *
 * Author: Andrey Brushchenko
 * Date: 11/1/2013
 */
get_header(); ?>

<?php
  $currentUser = wp_get_current_user();

  if (isset($_GET)) {
    $courseId = isset($_GET['courseid']) ? $_GET['courseid'] : NULL;
    $operation = isset($_GET['op']) ? $_GET['op'] : NULL;
  } elseif (isset($_POST)) {
    $operation = isset($_POST['op']) ? $_POST['op'] : NULL;
  } else {
    $courseId = NULL;
    $operation = NULL;
  }

  $action = "";
  $course = (object) array('Name' => '', 'Description' => '', 'Quarter' => '');

  $courseList = $gtcs12_db->GetCourseByFacultyId($currentUser->ID);

  if ($operation == 'delete' && $courseId != NULL) {
    $action = deleteCourse($courseId);
  } elseif($operation == 'edit' && $courseId != NULL) {
    $course = $gtcs12_db->GetCourseByCourseId($courseId);
  }

  if ($operation == 'update') {
    $action = UpdateCourse($currentUser->ID);
  } else if(gtcs_user_has_role('author')) { // is this user a professor
    $action = AddCourse();
  } else {
    $action = "invalid role";
  }

  $quarter = isset($course) ? $course->Quarter : "selected";


function AddCourse()
{
  $title       = isset($_POST['title']) ? $_POST['title'] : NULL;
  $quarter     = isset($_POST['slctQuarter']) ? $_POST['slctQuarter'] : NULL;
  $year        = isset($_POST['slctYear']) ? $_POST['slctYear'] : NULL;
  $description = isset($_POST['inptDescription']) ? $_POST['inptDescription'] : NULL;

  if ($title == NULL ||
      $quarter == NULL ||
      $year == NULL ||
      $description == NULL) {
    return "Invalid input when creating course";
  }

  $gtcs12_db->AddCourse($courseId, $title, $quarter, $year, $userId, $description);
  return "course created";

}

function UpdateCourse($userId)
{
  $courseId    = isset($_POST['courseid']) ? $_POST['courseid'] : NULL;
  $title       = isset($_POST['title']) ? $_POST['title'] : NULL;
  $quarter     = isset($_POST['slctQuarter']) ? $_POST['slctQuarter'] : NULL;
  $year        = isset($_POST['slctYear']) ? $_POST['slctYear'] : NULL;
  $description = isset($_POST['inptDescription']) ? $_POST['inptDescription'] : NULL;

  if ($courseId == NULL ||
      $title == NULL ||
      $quarter == NULL ||
      $year == NULL ||
      $description == NULL) {
    return "Invalid input when editing course";
  }

  $gtcs12_db->UpdateCourse($courseId, $title, $quarter, $year, $userId, $description);
  return "course edited";
}

function deleteCourse($courseId)
{
  global $gtcs12_db;
  $course = $gtcs12_db->GetCourseByCourseId($courseId);

  if ($course) { // course exists
    if ($course->FacultyId == $currentUser->ID) { // user owns course
      $gtcs12_db->DeleteCourse($courseId);
      return "course deleted";
    } else {
      return "not owner";
    }
  } else {
    return "course not found";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php if($action == "not owner") : ?>
  <div id="error-box">Error:You don't have ownership of that course</div>
<?php elseif($action == "course not found") : ?>
  <div id="error-box">Error:Course not found</div>
<?php elseif($action == "invalid role") : ?>
  <div id="error-box">Error:You don't have permission to create a course</div>
<?php elseif($action == "course deleted") : ?>
  <div id="action-box">Course deleted</div>
<?php elseif($action == "course edited") : ?>
  <div id="action-box">Course edited</div>
<?php elseif($action == "course created") : ?>
  <div id="action-box">Course created</div>
<?php endif ?>

  <form action="<?php echo site_url('/courses/') ?>" method="post">
    <div id='create-course-box'>
      <div id='create-course-title'>
        <?php echo ($operation == 'edit' ? "Edit course" : "Create course"); ?>
      </div>
      <div id='create-course-field'>
        <p class="create-course">Title</p>
        <input class='create-course'
          type="text" name="inptTitle" value="<?php echo $course->Name ?>" required>
        <br>
      </div>
      <div id='create-course-field'>
        <p class="create-course">Quarter</p>
        <select class='create-course' name='slctQuarter'>
          <option value="Autumn"
            <?php echo ($quarter == 'Autumn' ? "selected" : ""); ?>>Autumn</option>
          <option value="Winter"
            <?php echo ($quarter == 'Winter' ? "selected" : ""); ?>>Winter</option>
          <option value="Spring"
            <?php echo ($quarter == 'Spring' ? "selected" : ""); ?>>Spring</option>
          <option value="Summer"
            <?php echo ($quarter == 'Summer' ? "selected" : ""); ?>>Summer</option>
        </select>
      </div>
      <div id='create-course-field'>
        <p class="create-course">Year</p>
        <select class='create-course' name='slctYear'>
          <?php for($x = -1; $x <= 1; $x++) // one year back, one year forward
            {
              $selectBoxYear = date("Y") + $x;

              if($selectBoxYear == $course->Year)
                $selected = 'selected';
              else
                $selected = '';

              echo $selected;
              echo "<option value=\"" . (date("Y") + $x) . "\"" . $selected . ">" . $selectBoxYear . "</option>";
            }
          ?>
        </select>
      </div>
      <div id='create-course-field'>
        <p class="create-course">Description</p>
        <textarea cols="25" rows="10" autocomplete="off" name="inptDescription" required><?php echo $course->Description ?></textarea>
      </div>
      <div id="create-course-buttons">
<?php if($operation == 'create' || $operation == NULL) : ?>
        <input type="submit" value="Create"/>
<?php elseif($operation == 'edit') : ?>
        <input type="hidden" name="op" value="update">
        <input type="hidden" name="courseid" value="<?php echo $courseId ?>">
        <input type="submit" value="Finish Editing"/>
<?php endif ?>
        <a class="lin" href="<?php echo site_url('/my-class/') ?>"><button type="button">Cancel</button></a>
      </div>
    </div>
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
    <?php $courseLink = site_url('/my-class/?id=' . $course->Id); ?>
        <tr>
          <th><a href="<?php echo $courseLink ?>"><?php echo $course->Name; ?></th>
          <th><?php echo $course->Quarter; ?></th>
          <th><?php echo $course->Year; ?></th>
          <th>
            <form action="<?php echo site_url('/courses/') ?>" method="get">
              <select name="op">
                <option disabled="disabled" selected>Choose an action</option>
                <option value="edit">Edit</option>
                <option value="delete">Delete</option>
              </select>
              <input type="hidden" name="courseid" value="<?php echo $course->Id ?>">
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
  </div>
</html>

<?php get_footer() ?>
