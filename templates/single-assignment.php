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
<?php $ps = $pageState; ?>

<!-- Assignment Display -->
<div id="assignment-whole">

  <a class="link" href="<?php echo $url['my-class'] . "?id={$ps->courseId}"; ?>">
    Back to course</a>

  <?php if ($ps->isOwner) : ?>
    <a class="link" href="<?php echo $url['assignments'] .
      "?editId={$ps->assignmentId}&id={$ps->courseId}"; ?>">
      Edit this Assignment</a>
  <?php endif; ?>

  <div id="assignnment-title">
    <?php echo $ps->displayedAssignment->post_title; ?>
  </div>

  <div id="assignment-top">
    <img src="<?php bloginfo('template_directory'); ?>/images/blank-project.png"
      width="155" height="155" />

    <p class="assignment-meta"><b>Course </b>
      <?php echo $ps->displayedCourse->Name ?></p>

    <p class="assignment-meta"><b>Created </b>
      <?php echo date('F d, Y', strtotime($ps->displayedAssignment->post_date)); ?></p>

    <p class="assignment-meta">
      <b>Status</b>
      <?php if ($ps->canSubmit) : ?>Open to Submissions
        <?php if ($ps->isOwner) : ?>
          <a href="<?php echo $ps->url['assignment'] . "?id={$ps->assignmentId}&op=close"; ?>">
            [Close]
          </a>
        <?php endif; ?>
      <?php else: ?>Closed to Submissions
        <?php if ($ps->isOwner): ?>
          <a href="<?php echo $ps->url['assignment'] . "?id={$ps->assignmentId}&op=open"; ?>">
            [Open]
          </a>
        <?php endif; ?>
      <?php endif; ?>
    </p><!-- assignment-meta -->

    <div id="assignment-buttons">
      <div id="assignment-button">
        <?php if($ps->view == 'description') : ?>
          <b>Description</b>
        <?php else : ?>
          <a href="<?php echo $ps->url['assignment'] .
            "?id={$ps->assignmentId}&view=description"; ?>">
            Description
          </a>
        <?php endif; ?>
      </div> <!-- assignment-button -->

      <div id="assignment-button">
        <?php if ($ps->view == 'applet'): ?><b>Applet</b>
        <?php else : ?>
          <a href="<?php echo $ps->url['assignment'] .
            "?id={$ps->assignmentId}&view=applet"; ?>">
            Applet
          </a>
        <?php endif; ?>
      </div> <!-- assignment-button -->

    </div> <!-- assignment-buttons -->

  </div> <!-- assignment-top -->

  <div id="assignment-description">
    <?php if ($ps->view == 'description'): ?>
      <?php echo nl2br($ps->displayedAssignment->post_content); ?>
    <?php elseif ($ps->view == 'applet'): ?>Put applet here
    <?php endif; ?>
  </div>

</div> <!-- assignment-whole -->
<!-- Assignment Display -->

<!-- Sort Selector -->
<div id="sort-box">
  <form action="<?php echo $ps->url['assignment']; ?>" method="get">
    <input type="hidden" name="id" value="<?php echo $ps->assignmentId ?>">
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

    <?php foreach($ps->submissionList as $submission) : ?>
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

    <?php if(count($ps->studentList) > count($submitters)) : ?>
      <tr class="break">
        <th></th>
        <th></th>
        <th></th>
      </tr>
    <?php endif; ?>

    <?php foreach($ps->studentList as $student): ?>
      <?php if ($student->StudentId != null): ?>
        <?php if (!isset($submitters[$student->Name])): ?>
          <tr>
            <th class="center" colspan="3">
              <a href="<?php echo $url['profile'] . "?user={$student->Id}"; ?>">
                <?php echo $student->Name ?>
              </a>
              has not submitted anything
            </th>
          </tr>
        <?php endif; ?>
      <?php endif; ?>
    <?php endforeach; ?>

    <?php if($ps->isEnrolled && $ps->canSubmit) : ?>
      <tr class="break">
        <th></th>
        <th></th>
        <th></th>
      </tr>
      <tr>
        <th class="action" colspan="3">
          <b><a href="<?php echo $url['assignment'] . "?id={$ps->courseId}"; ?>">
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
