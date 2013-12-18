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
  
  if(isset($_GET['courseid'])) // get the course id
  {
    $courseId = $_GET['courseid'];
  }
  
  if(isset($_GET['op'])) // get the operation
  {
    $operation = $_GET['op'];
  }
  elseif(isset($_POST['op'])) // 
  {
    $operation = $_POST['op'];
  }
  else 
  {
    $operation = '';
  }
  
  if($operation == 'delete') // has a course been marked for deletion
  {
    if(isset($courseId) && $courseId != '') // has courseid been provided
    {
      $course = $gtcs12_db->GetCourseByCourseId($courseId);

      if(isset($course)) // does course exists
      {
        if($course->FacultyId == $currentUser->ID) // does user own course
        { 
          $action = DeleteCourse($courseId, $currentUser->ID);
        }
        else
        {
          $action = "You do not own this course";
        }
        
        unset($course);
      }
      else
      {
        $action = "Course not found";
      }
    }
  }
  elseif(isset($operation) && $operation == 'edit') // has a course been marked to edit
  {
    $course = $gtcs12_db->GetCourseByCourseId($courseId);
  }

  if($_POST) 
  {
    if($operation == 'update')
    { 
      $action = UpdateCourse($currentUser->ID);
    }
    elseif($operation == 'create') 
    {
      $action = AddCourse($currentUser->ID);
    }
  }
  
function AddCourse($userId)
{
  global $gtcs12_db;

  $title       = isset($_POST['inptTitle']) ? $_POST['inptTitle'] : NULL;
  $quarter     = isset($_POST['slctQuarter']) ? $_POST['slctQuarter'] : NULL;
  $year        = isset($_POST['slctYear']) ? $_POST['slctYear'] : NULL;
  $description = isset($_POST['inptDescription']) ? $_POST['inptDescription'] : NULL;

  if ($title == NULL ||
      $quarter == NULL ||
      $year == NULL ||
      $description == NULL) {
    return "Invalid input when creating course";
  }

  $gtcs12_db->AddCourse($title, $quarter, $year, $userId, $description);
  return "<b>" . $title . "</b> has been created";
}

function UpdateCourse($userId)
{
  global $gtcs12_db;

  $courseId    = isset($_POST['courseid']) ? $_POST['courseid'] : NULL;
  $title       = isset($_POST['inptTitle']) ? $_POST['inptTitle'] : NULL;
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
  return "<b>" . $title . "</b> has been updated";
}

function DeleteCourse($courseId, $userId)
{
  global $gtcs12_db;
  $course = $gtcs12_db->GetCourseByCourseId($courseId);

  if ($course) { // course exists
    if ($course->FacultyId == $userId) { // user owns course
      $gtcs12_db->DeleteCourse($courseId);
      return "<b>" . $course->Name . "</b> has been deleted";
    } else {
      return "You do not own this course";
    }
  } else {
    return "Course not found";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php if(isset($action)) : ?>
  <div id="action-box"><?php echo $action ?></div>
<?php endif ?>

  <form action="<?php echo site_url('/manage-courses/') ?>" method="post">
    <div id='create-course-box'>
      <div id='create-course-title'>
        <?php echo (isset($operation) && $operation == 'edit' ? "Edit course" : "Create course"); ?>      
      </div>      
      <div id='create-course-field'>
        <p class="create-course">Title</p>
        <input class='create-course' type="text" name="inptTitle" value="<?php if(isset($course)) echo $course->Name ?>" required>
      </div>
      <div id='create-course-field'>
        <p class="create-course">Quarter</p>
        <select class='create-course' name='slctQuarter'>
          <option value="Autumn" <?php echo (isset($course) && $course->Quarter == 'Autumn' ? "selected" : ""); ?>>Autumn</option>
          <option value="Winter" <?php echo (isset($course) && $course->Quarter == 'Winter' ? "selected" : ""); ?>>Winter</option>
          <option value="Spring" <?php echo (isset($course) && $course->Quarter == 'Spring' ? "selected" : ""); ?>>Spring</option>
          <option value="Summer" <?php echo (isset($course) && $course->Quarter == 'Summer' ? "selected" : ""); ?>>Summer</option>               
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
        <textarea cols="25" rows="10" autocomplete="off" name="inptDescription" required><?php if(isset($course)) echo $course->Description ?></textarea>
      </div>
      <div id="create-course-buttons">
<?php if($operation == 'create' || $operation == 'delete' || $operation == 'update' || $operation == '') : ?>
        <input type="hidden" name="op" value="create">
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
<?php $courses = $gtcs12_db->GetCourseByFacultyId($currentUser->ID); ?>
<?php if($courses) : ?>
<?php   foreach($courses as $course) : ?> 
<?php     $courseLink = site_url('/my-class/?id=' . $course->Id); ?>
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
<?php   endforeach; ?>
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
