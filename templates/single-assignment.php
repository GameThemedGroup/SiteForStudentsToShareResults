<?php
/**
 * Template Name: Single Assignment
 * Description: Shows assignment details, allows professor to edit, allows
 * student to submit assignments
 *
 * Author: Rodelle Ladia Jr.
 * Date: 27 October 2013
 */

get_header(); ?>

<?php include_once(get_template_directory() . '/logic/single-assignment.php'); ?>

<?php
  $studentList = $pageState->studentList;
  $submissionList = $pageState->submissionList;
  $isOwner = $pageState->isOwner;
  $isEnrolled = $pageState->isEnrolled;
  $canSubmit = $pageState->canSubmit;
  $displayedAssignment = $pageState->displayedAssignment;
  $displayedCourse = $pageState->displayedCourse;
  $view = $pageState->view;
  $nonSubmitters = $pageState->nonSubmitters;
  $assignmentId = $pageState->assignmentId;
?>

<!-- Assignment Display -->
<div id="assignment-whole">


  <a class="link" href="<?php echo $url['my-class'] . "?id={$courseId}"; ?>">
    Back to course</a>

  <?php if ($isOwner) : ?>
    <a class="link" href="<?php echo $url['assignments'] .
      "?editId={$assignmentId}&id={$courseId}"; ?>">
      Edit this Assignment</a>
  <?php endif; ?>

  <div id="assignnment-title">
    <?php echo $displayedAssignment->post_title; ?>
  </div>

  <div id="assignment-top">
    <img src="<?php bloginfo('template_directory'); ?>/images/blank-project.png"
      width="155" height="155" />

    <p class="assignment-meta"><b>Course </b>
      <?php echo $displayedCourse->Name ?></p>

    <p class="assignment-meta"><b>Created </b>
      <?php echo date('F d, Y', strtotime($displayedAssignment->post_date)); ?></p>

    <p class="assignment-meta">
      <b>Status</b>
      <?php if ($canSubmit) : ?>Open to Submissions
        <?php if ($isOwner) : ?>
          <a href="<?php echo $url['assignment'] . "?id={$assignmentId}&op=close"; ?>">
            [Close]
          </a>
        <?php endif; ?>
      <?php else: ?>Closed to Submissions
        <?php if ($isOwner): ?>
          <a href="<?php echo $url['assignment'] . "?id={$assignmentId}&op=open"; ?>">
            [Open]
          </a>
        <?php endif; ?>
      <?php endif; ?>
    </p><!-- assignment-meta -->

    <div id="assignment-buttons">
      <div id="assignment-button">
        <?php if($view == 'description') : ?>
          <b>Description</b>
        <?php else : ?>
          <a href="<?php echo $url['assignment'] .
            "?id={$assignmentId}&view=description"; ?>">
            Description
          </a>
        <?php endif; ?>
      </div> <!-- assignment-button -->

      <div id="assignment-button">
        <?php if ($view == 'applet'): ?><b>Applet</b>
        <?php else : ?>
          <a href="<?php echo $url['assignment'] .
            "?id={$assignmentId}&view=applet"; ?>">
            Applet
          </a>
        <?php endif; ?>
      </div> <!-- assignment-button -->

    </div> <!-- assignment-buttons -->

  </div> <!-- assignment-top -->

  <div id="assignment-description">
    <?php if ($view == 'description'): ?>
      <?php echo nl2br($displayedAssignment->post_content); ?>
    <?php elseif ($view == 'applet'): ?>Put applet here
    <?php endif; ?>
  </div>

</div> <!-- assignment-whole -->
<!-- Assignment Display -->

<!-- Sort Selector -->
<div id="sort-box">
  <form action="<?php echo $url['assignment']; ?>" method="get">
    <input type="hidden" name="id" value="<?php echo $assignmentId ?>">
    <select name="sort">
      <option disabled="disabled" selected>Sort by</option>
      <option value="date">Submission Date</option>
      <option value="author">Author</option>
    </select>
    <input type="submit" value="Sort"/>
  </form>
</div>
<!-- Sort Selector -->

<!-- Submission Table -->
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

    <?php foreach($submissionList as $submission) : ?>
      <?php $submitters[$submission->AuthorName] = true; ?>
      <tr>
        <th>
          <a href="<?php echo $url['assignment'] . "?p={$submission->SubmissionId}"; ?>">
            <?php echo $submission->Title ?>
          </a>
        </th>
        <th>
          <a href="<?php echo $url['profile'] . "?user={$submission->AuthorId}"; ?>">
            <?php echo $submission->AuthorName; ?>
          </a>
        </th>
        <th><?php echo date('m/d/y (h:i a)', strtotime($submission->SubmissionDate)) ?></th>
      </tr>
    <?php endforeach; ?>

    <?php if ($isOwner): ?>
    <?php foreach($nonSubmitters as $student): ?>
      <tr>
        <th class="center" colspan="3">
          <a href="<?php echo $url['profile'] . "?user={$student->ID}"; ?>">
            <?php echo $student->display_name; ?>
          </a>
          has not submitted anything
        </th>
      </tr>
    <?php endforeach; ?>
    <?php endif; ?>

    <?php if($isEnrolled && $canSubmit) : ?>
      <tr class="break">
        <th></th>
        <th></th>
        <th></th>
      </tr>
      <tr>
        <th class="action" colspan="3">
          <b><a href="<?php echo $url['assignment'] . "?id={$courseId}"; ?>">
            Submit Assignment
          </a></b>
        </th>
      </tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
<!-- Submission Table -->

<?php get_footer() ?>
