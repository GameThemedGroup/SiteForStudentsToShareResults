<?php
/*
 * Expected State Values
 *   doShowUrl = false,
 *   formAction = ''
 *   formCallback = '',
 *   formClassValue = '',
 *   formDescriptionValue = '',
 *   formSubmitText = ''
 *   formTitle = 'Assignment Form Default',
 *   formTitleValue = '',
 *   formUrlValue = '',
 *   formHiddenValues = array(),
 *   formAppletClassList = array()
 */
?>

<form action="<?php echo $formCallback; ?>"
  method="post" enctype="multipart/form-data">

  <div id='create-assignment-box'>
    <div id='create-assignment-title'>
      <?php echo $formTitle; ?>
    </div>

    <div id='create-assignment-field'>
      <p class="create-assignment">Title</p>
      <input class='create-assignment' type="text" name="title"
        required value="<?php echo $formTitleValue; ?>" required>
    </div>

    <?php if ($doShowUrl): ?>
      <div id='create-assignment-field'>
        <p class="create-assignment">External URL</p>
        <input class='create-assignment' type="text" name="link"
          value="<?php echo $formUrlValue; ?>">
      </div>
    <?php endif; ?>

    <div id='create-assignment-field'>
      <p class="create-assignment">Description</p>
      <textarea cols="25" rows="5" name="description"
        required><?php echo $formDescriptionValue; ?></textarea>
    </div>

    <div id='create-assignment-field'>
      <p class="create-assignment">Jar File</p>
      <input class='create-assignment' type="file" name="jar">
    </div>

    <div id='create-assignment-field'>
      <p class="create-assignment">Main Class
        [<a href="#"
          title="Ex. ghostfinder=user.Main.class
      hangman=rslj.school.hangman.Hangman.class
      rolodex=alex.rolodex.Rolodex.class">?</a>]
      </p>
      <br />

      <?php foreach ($formAppletClassList as $appletClass): ?>
        <input type="radio" name="class" value="<?php echo $appletClass; ?>">
          <?php echo $appletClass; ?><br />
        </input>
      <?php endforeach; ?>

      <input type="radio" name="class" value="other" checked>Other</input>

      <input type="text" name="classInput"
        value="<?php echo $formClassValue; ?>">.class
      </input>

    </div>

    <div id='create-assignment-field'>
      <p class="create-assignment">Screenshot</p>
      <input class='create-assignment' type="file" name="image" accept="image/*"></input>
    </div>

    <div id="create-assignment-buttons">

    <?php foreach ($formHiddenValues as $key => $value): ?>
      <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
    <?php endforeach; ?>

    <input type="hidden" name="action" value="<?php echo $formAction; ?>">
    <input type="submit" value="<?php echo $formSubmitText; ?>" />

      <a href="<?php echo $formCallback; ?>">
        <button type="button">Cancel</button>
      </a>
    </div> <!-- create-assignment-buttons -->
  </div> <!-- Create-Assignment-Box -->
</form>

