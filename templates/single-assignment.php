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
  $assignmentId = $pageState->assignmentId;
  $canSubmit = $pageState->canSubmit;
  $courseId = $pageState->courseId;
  $displayedAssignment = $pageState->displayedAssignment;
  $displayedCourse = $pageState->displayedCourse;
  $isEditing = $pageState->isEditing;
  $isEnrolled = $pageState->isEnrolled;
  $isOwner = $pageState->isOwner;
  $nonSubmitters = $pageState->nonSubmitters;
  $studentList = $pageState->studentList;
  $submissionList = $pageState->submissionList;
  $userFeedback = $pageState->userFeedback;
  $view = $pageState->view;
?>

<!-- Assignment Display -->
<div id="assignment-whole">

  <?php if($userFeedback) : ?>
    <div id="action-box"><?php echo $userFeedback; ?></div>
  <?php endif ?>

  <a class="link" href="<?php echo $url['class'] . "?id={$courseId}"; ?>">
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

    <!-- Open/Close Assignment -->
    <form style='margin:0; padding:0;'
      action=<?php echo $url['assignment'] . "?id={$assignmentId}"; ?> m
      method="post">

      <p class="assignment-meta">
        <b>Status</b>
        <?php if ($canSubmit) : ?>Open to Submissions
        <?php else: ?>Closed to Submissions
        <?php endif; ?>

        <?php if ($isOwner) : ?>
            <input type="hidden" name="id" value="<?php echo $assignmentId; ?>">
            <input type="hidden" name="action"
              value="<?php echo $canSubmit ? 'close' : 'open'; ?>"/>
            <input style='display:inline;' type="submit"
              value="<?php echo $canSubmit ? 'Close' : 'Open'; ?>"/>
        <?php endif; ?>
      </p>
    </form>
    <!-- Open/Close Assignment -->

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

<?php
  $submissionTitle = $pageState->submissionTitle;
  $submissionDescription = $pageState->submissionDescription;
  $isEditing = $pageState->isEditing;
?>
<!-- Assignment Submission Form -->
<?php if ($isEnrolled && $canSubmit): ?>
<form action="<?php echo $url['assignment'] . "?id={$assignmentId}"; ?>"
  method="post" enctype="multipart/form-data">

  <div id='create-assignment-box'>
    <div id='create-assignment-title'>
      <?php echo $isEditing ? 'Edit ' : 'Submit Assignment' . $submissionTitle; ?>
    </div>

    <div id='create-assignment-field'>
      <p class="create-assignment">Title</p>
      <input class='create-assignment' type="text" name="title"
        value="<?php //echo $displayedSubmission->post_title; ?>" required>
    </div>

    <div id='create-assignment-field'>
      <p class="create-assignment">Description</p>
      <textarea cols="25" rows="5" name="description"
        required><?php echo $submissionDescription; ?></textarea>
    </div>

    <div id='create-assignment-field'>
      <p class="create-assignment">Jar File</p>
      <input class='create-assignment' type="file" name="jar">
    </div>

    <div id='create-assignment-field'>
      <p class="create-assignment">Main Class [?]</p><br />
      <input type="radio" name="class" value="Main.class" checked>Main.class<br />
      <input type="radio" name="class" value="user.Main.class">user.Main.class<br />
      <input type="radio" name="class" value="other">Other
      <input type="text" name="classInput">.class</input>
    </div>

    <div id='create-assignment-field'>
      <p class="create-assignment">Screenshot</p>
      <input class='create-assignment' type="file" name="image" accept="image/*">
    </div>

    <div id="create-assignment-buttons">

      <input type="hidden" name="assignmentId" value="<?php echo $assignmentId; ?>">
      <input type="hidden" name="courseId" value="<?php echo $courseId; ?>">

    <?php if ($isEditing): ?>
        <input type="hidden" name="action" value="edit">
        <input type="submit" value="Finish Editing"/>
    <?php else: ?>
        <input type="hidden" name="action" value="create">
        <input type="submit" value="Submit"/>
    <?php endif; ?>
      <a href="<?php echo site_url("assignment/?id={$courseId}"); ?>">
        <button type="button">Cancel</button>
      </a>
    </div> <!-- create-assignment-buttons -->
  </div> <!-- Create-Assignment-Box -->
</form>
<? endif; ?>
<!-- Assignment Submission Form -->

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
