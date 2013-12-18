<?php
// File: services/attachments.php
// Description: Functions related to handling attachment
//
// Author: Rodelle Ladia Jr.
// Date:   16 December 2013

// Uploads a file from $_FILES and returns an array of attributes of the
// returned file
//
// @param file_index the index of $_FILES where the file is located
// TODO check and handle errors
function gtcs_HandleFileUpload($fileIndex)
{
  //TODO find out if there are any problems using ABSPATH
  if (!function_exists('wp_handle_upload'))
    require_once( ABSPATH . 'wp-admin/includes/file.php' );

  $file_name = $_FILES[$file_index]['name'];
  $uploaded_file_type = wp_check_filetype(basename($file_name));
  $file_type = $uploaded_file_type['type'];

  if($file_type['ext'] == false) // TODO add error log here
    return; // attachment type not supported

  $file = $_FILES[$file_index];
  $upload_overrides = array('test_form' => false);

  // TODO handle errors
  $uploaded_file = wp_handle_upload($file, $upload_overrides);

  // TODO limit file types here?
  return $uploaded_file;
}

// Attaches the given file to the post. The file must first exist in the
// wordpress uploads directory and can most easily be done via
// UploadFile or gtcs_handle_import_file
//
// @return the id of the attachment/post
//
// @param post_id     the id of the post the media is being added to
// @param file_attr   the file attributes returned by a successful call to
//                    UploadFile or gtcs_handle_import_file
// @param title       the title to be used by the wordpress media library
// @param type_value  the value for the post's 'type' meta_key
// @param is_featured_image  if true, the file will be used as the post's featured image
// @param post_author the id of the the post author. If NULL, defaults to current user
function gtcs_AttachFileToPost($post_id, $file_attr, $title, $type_value, $is_featured_image, $post_author = NULL)
{
  if ($post_author === NULL)
    $post_author = get_current_user_id();

  $file_name = $file_attr['file'];

  $uploaded_file_type = wp_check_filetype(basename($file_name));
  $file_type = $uploaded_file_type['type'];

  if($file_type['ext'] === false) {
    trigger_error(__FUNCTION__ . ": Attachment type not supported.", E_USER_WARNING);
    return;
  }

  $attachment_args = array(
    'post_author' => $post_author,
    'post_mime_type' => $file_type,
    'post_title' => $title,
    'post_content' => '',
    'post_status' => 'inherit'
  );

  $attach_id = wp_insert_attachment($attachment_args, $file_name, $post_id);
  $meta_key = "type";
  $meta_value = $type_value;
  update_post_meta($attach_id, $meta_key, $meta_value);

  if (!function_exists('wp_generate_attachment_metadata'))
    require_once(ABSPATH . 'wp-admin/includes/image.php');

  $attach_data = wp_generate_attachment_metadata($attach_id, $file_name);
  wp_update_attachment_metadata($attach_id, $attach_data);

  if($is_featured_image) {
    update_post_meta($post_id, '_thumbnail_id', $attach_id);
  }

  return $attach_id;
}
?>
