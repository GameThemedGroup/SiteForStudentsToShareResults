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

<?php
    
    require('./wp-blog-header.php');
    require('DBHandler.php');

    $dbHandler = new DBHandler;

    $operation = $_GET['op'];
    $results = '';

    if ($operation) {
        if ($operation == 'recreate') {
            $results = $dbHandler->RecreateTables();
        } else if ($operation == 'del') {
            $results = $dbHandler->DeleteTables();
        } else {
            $results = 'unknown operation ' . $operation;
        }
    }
?>
<html>
    <head></head>
    <body>
        <table>
            <tr>
                <th>Database operation</th>
                <th>Operation link</th>
            </tr>
            <tr>
                <td>Recreate Tables</td>
                <td><a href="<?php echo site_url('/debug/'); ?>?op=recreate">Recreate Tables</a></td>
            </tr>
            <tr>
                <td>Delete Tables</td>
                <td><a href="<?php echo site_url('/debug/'); ?>?op=del">Delete Tables</a></td>
            </tr>
            <tr>
                <td>Status</td>
                <td>
                    <?php
                        print_r($results);
                    ?>
                </td>
            </tr>
        </table>
    </body>
</html>

<?php get_footer() ?>
