<?php
/**
 * Template Name: Single Assignment
 * Description: Shows assignment details, allows professor to edit, allows student to submit assignments
 *
 * Author: Rodelle Ladia Jr.
 * Date: 27 October 2013
 */

get_header(); ?>

<?php
  $currentUser = wp_get_current_user();
  $isTeacher = gtcs_user_has_role('author');
  $isStudent = gtcs_user_has_role('subscriber');

  if($_GET["assignid"])
  {
    $assignmentId = $_GET["assignid"];
    $assignment = get_post($assignmentId);
  }

  // get sort mode, default is by date
  if($_GET['sort'])
  {
    $sort = $_GET['sort'];
  }
  else
  {
    $sort = 'date';
  }

  // get view mode, default is description
  if($_GET['view'])
  {
    $view = $_GET['view'];
  }
  else
  {
    $view = 'description';
  }

  $terms = wp_get_post_terms($assignmentId);
  $courseId = str_ireplace ('course:' ,'' , $terms[0]->name);
  $course = $gtcs12_db->GetCourseByCourseId($courseId);

  // check if logged in user is a teacher and owner of assignment
  if($isTeacher)
  {
    if($assignment->post_author == $currentUser->ID)
    {
      $isOwner = true;
    }
  }

  // check if logged in user is student and enrolled in this course
  if($isStudent)
  {
    $isEnrolled = true; // needs to be changed, currently no function to check if student enrolled
  }

  // retrieve students and submissions for table
  $students = $gtcs12_db->GetStudents($courseId);
  $submissions = $gtcs12_db->GetAllSubmissions($assignmentId);

  // toggle opening/closing assignment
  if($_GET['op'] == 'open')
    update_post_meta($assignmentId, 'isEnabled', 1, 0);
  else if($_GET['op'] == 'close')
    update_post_meta($assignmentId, 'isEnabled', 0, 1);

  $status = get_post_meta($assignmentId, 'isEnabled', true);

  // sort submission table entries
  if($sort == 'author')
  {
    usort($submissions, "cmpAuthorA");
    usort($students, "cmpAuthorB");
  }
  else
  {
    // sort by date
    usort($submissions, "cmpDate");
  }

  // helper functions needed for sorting
  function cmpAuthorA($a, $b)
  {
    return strcmp(strtolower($a->AuthorName), strtolower($b->AuthorName));
  }

  function cmpAuthorB($a, $b)
  {
    return strcmp(strtolower($a->Name), strtolower($b->Name));
  }

  function cmpDate($a, $b)
  {
    return strcmp($a->SubmissionDate, $b->SubmissionDate);
  }
?>

<!DOCTYPE html>
<html lang="en">
  <div id="assignment-whole">
    <a class="link" href="<?php echo site_url('/my-class/?id=' . $courseId) ?>">Back to course</a>
<?php if($isOwner) : ?>
    <a class="link" href="<?php echo site_url('/manage-assignment/?op=edit&assignid=' . $assignmentId . '&courseid=' . $courseId) ?>">Edit this Assignment</a>
<?php endif ?>
    <div id="assignnment-title">
<?php if($assignment->post_title)
        echo $assignment->post_title;
      else
        echo 'Empty';
?>
    </div>
    <div id="assignment-top">
      <img src="<?php bloginfo('template_directory'); ?>/images/blank-project.png" width="155" height="155">
      <p class="assignment-meta"><b>Course </b><?php echo $course->Name ?></p>
      <p class="assignment-meta"><b>Created </b><?php echo date('F d, Y', strtotime($assignment->post_date)) ?></p>
      <p class="assignment-meta"><b>Status</b>

<?php if($status) : ?>
        Open to Submissions
<?php   if($isOwner) : ?>
        <a href="<?php echo site_url('/assignment/?assignid=' . $assignmentId . '&op=close') ?>">[Close]</a>
<?php   endif ?>
<?php else : ?>
        Closed to Submissions
<?php   if($isOwner) : ?>
        <a href="<?php echo site_url('/assignment/?assignid=' . $assignmentId . '&op=open') ?>">[Open]</a>
<?php   endif ?>
<?php endif ?>
      </p>
      <div id="assignment-buttons">
        <div id="assignment-button">
<?php if($view == 'description') : ?>
          <b>Description</b>
<?php else : ?>
          <a href="<?php echo site_url('/assignment/?assignid=' . $assignmentId . '&view=description') ?>">Description</a>
<?php endif ?>
        </div>
        <div id="assignment-button">
<?php if($view == 'applet') : ?>
          <b>Applet</b>
<?php else : ?>
          <a href="<?php echo site_url('/assignment/?assignid=' . $assignmentId . '&view=applet') ?>">Applet</a>
<?php endif ?>
        </div>
      </div>
    </div>
    <div id="assignment-description">
<?php if($view == 'description') : ?>
      <?php echo nl2br($assignment->post_content); ?>
<?php elseif($view == 'applet') : ?>
      Put applet here
<?php endif ?>
    </div>
  </div>



  <div id="sort-box">
    <form action="<?php echo site_url('/assignment/') ?>" method="get">
      <input type="hidden" name="assignid" value="<?php echo $assignmentId ?>">
      <select name="sort">
        <option disabled="disabled" selected>Sort by</option>
        <option value="date">Submission Date</option>
        <option value="author">Author</option>
      </select>
      <input type="submit" value="Sort"/>
    </form>
  </div>

  <div id='table'>
    <div id='table-title'>Submissions</div>
    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th>Author</th>
          <th>Date Submitted</th>
        </tr>
      </thead>
      <tbody>
<?php $submitters = array() ?>
<?php foreach($submissions as $submission) : ?>
<?php   $submitters[$submission->AuthorName] = true ?>
        <tr>
          <th>
            <a href="<?php echo site_url('?p=') . $submission->SubmissionId ?>"><?php echo $submission->Title ?></a>
          </th>
          <th>
            <a href="<?php echo site_url('/profile/?user=') . $submission->AuthorId ?>"><?php echo $submission->AuthorName ?>
          </th>
          <th><?php echo date('m/d/y (h:i a)', strtotime($submission->SubmissionDate)) ?></th>
        </tr>
<?php endforeach; ?>
<?php if(count($students) > count($submitters)) : ?>
        <tr class="break">
          <th></th>
          <th></th>
          <th></th>
        </tr>
<?php endif ?>
<?php foreach($students as $student) : ?>
<?php   if($student->StudentId != null) : ?>
<?php     if($submitters[$student->Name] == false) : ?>
        <tr>
          <th class="center" colspan="3">
            <a href="<?php echo site_url('/profile/?user=' . $student->Id) ?>"><?php echo $student->Name ?></a>
            has not submitted anything
          </th>
        </tr>
<?php     endif ?>
<?php   endif ?>
<?php endforeach ?>
<?php if($isEnrolled && $status) : ?>
        <tr class="break">
          <th></th>
          <th></th>
          <th></th>
        </tr>
        <tr>
          <th class="action" colspan="3">
            <b><a class="action" href="<?php echo site_url('/manage-assignments/?courseid=' . $courseId) ?>">Submit Assignment</a></b>
          </th>
        </tr>
<?php endif; ?>
      </tbody>
    </table>
  </div>
</html>

<?php get_footer() ?>
