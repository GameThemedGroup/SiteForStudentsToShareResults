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
  $current_user = wp_get_current_user();

  if($_GET['op'] == 'delete' && $_GET['courseid'] != '') // has a course been marked for deletion
  {
    $courseId = $_GET['courseid'];
    $course = $gtcs12_db->GetCourseByCourseId($courseId);

    if($course) // does course exists
    {
      if($course->FacultyId == $current_user->ID) // does user own course
      { 
        $gtcs12_db->DeleteCourse($courseId);
        $action = "course deleted";
      }
      else
      {
        $action = "not owner";
      }
    }
    else
    {
      $action = "course not found";
    }
  }
  elseif($_GET['op'] == 'edit' && $_GET['courseid'] != '') // has a course been marked to edit
  {
    $courseId = $_GET['courseid'];
    $course = $gtcs12_db->GetCourseByCourseId($courseId);
  }

  if($_POST) 
  {
    if($_POST['op'] == 'update')
    { 
      $gtcs12_db->UpdateCourse($_POST['courseid'], $_POST['inptTitle'], $_POST['slctQuarter'], $_POST['slctYear'], $current_user->ID, $_POST['inptDescription']); 
      $action = "course edited";
    }
    else if(gtcs_user_has_role('author')) // is this user a professor
    {
      $gtcs12_db->AddCourse($_POST['inptTitle'], $_POST['slctQuarter'], $_POST['slctYear'], $current_user->ID, $_POST['inptDescription']);
      $action = "course created";
    }
    else
    {
      $action = "invalid role";
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

  <form action="<?php echo site_url('/manage-courses/') ?>" method="post">
    <div id='create-course-box'>
      <div id='create-course-title'>
        <?php echo ($_GET['op'] == 'edit' ? "Edit course" : "Create course"); ?>      
      </div>      
      <div id='create-course-field'>
        <p class="create-course">Title</p>
        <input class='create-course' type="text" name="inptTitle" value="<?php echo $course->Name ?>" required><br>
      </div>
      <div id='create-course-field'>
        <p class="create-course">Quarter</p>
        <select class='create-course' name='slctQuarter'>
          <option value="Autumn" <?php echo ($course->Quarter == 'Autumn' ? "selected" : ""); ?>>Autumn</option>
          <option value="Winter" <?php echo ($course->Quarter == 'Winter' ? "selected" : ""); ?>>Winter</option>
          <option value="Spring" <?php echo ($course->Quarter == 'Spring' ? "selected" : ""); ?>>Spring</option>
          <option value="Summer" <?php echo ($course->Quarter == 'Summer' ? "selected" : ""); ?>>Summer</option>               
        </select>
      </div>
      <div id='create-course-field'>
        <p class="create-course">Year</p>
        <select class='create-course' name='slctYear'>
          <?php for($x = 0; $x < 10; $x++)
            {
              $selectBoxYear = date("Y") - $x;

              if($selectBoxYear == $course->Year)
                $selected = 'selected';
              else
                $selected = '';

              echo $selected;
              echo "<option value=\"" . (date("Y") - $x) . "\"" . $selected . ">" . $selectBoxYear . "</option>";
            }
          ?>         
        </select>
      </div>
      <div id='create-course-field'>
        <p class="create-course">Description</p>
        <textarea cols="25" rows="10" autocomplete="off" name="inptDescription" required><?php echo $course->Description ?></textarea>
      </div>
      <div id="create-course-buttons">
<?php if($_GET['op'] == 'create' || $_GET['op'] == '') : ?>
        <input type="submit" value="Create"/>   
<?php elseif($_GET['op'] == 'edit') : ?>
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
<?php $courses = $gtcs12_db->GetCourseByFacultyId($current_user->ID); ?>
<?php if($courses) : ?>
<?php foreach($courses as $course) : ?> 
<?php $courseLink = site_url('/my-class/?id=' . $course->Id); ?>
        <tr>
          <th><a href="<?php echo $courseLink ?>"><?php echo $course->Name; ?></th>
          <th><?php echo $course->Quarter; ?></th>
          <th><?php echo $course->Year; ?></th>
          <th>
            <form action="<?php echo site_url('/manage-courses/') ?>" method="get">
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
<?php else : ?>
        <tr>
          <th class="center" colspan="4">You have no courses</th>
        </tr>
<?php endif ?>
      </tbody>
    </table>
  </div>
</html>

<?php get_footer() ?>
