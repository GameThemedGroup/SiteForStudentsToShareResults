<?php
/**
 * Template Name: Debug
 * Description:
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
get_header() ?>

<?php include_once(get_template_directory() . '/logic/debug.php'); ?>

<html>
  <head></head>
  <body>
    <table>
      <tr>
        <th>Database operation</th>
        <th>Operation link</th>
      </tr>
      <tr>
        <td>Reset Test Data</td>
        <td><a href="<?php echo site_url('/debug/'); ?>?op=reset">Reset Test Data</a></td>
      </tr>
      <tr>
        <td>Recreate Tables</td>
        <td><a href="<?php echo site_url('/debug/'); ?>?op=recreate">Recreate Tables</a></td>
      </tr>
      <tr>
        <td>Delete Tables</td>
        <td><a href="<?php echo site_url('/debug/'); ?>?op=del">Delete Tables</a></td>
      </tr>
    </table>
  </body>
</html>

<?php get_footer() ?>
