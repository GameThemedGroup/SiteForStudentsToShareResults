<?php
// Heavily based on Dion Hulse's 'Add From Server' Wordpress plugin
// Handles an individual file upload.
//
// Uploads a file from the server and returns an associative array of 
// file attributes that mirrors the array returned by wp_handle_upload.
//
// @return file (string) the local path to the uploaded file
//         url  (string) the public url for the uploaded file
//         type (string) the mime type of the uploaded file
//
// @param file        the absolute file path of the file to be uploaded
// @param post_id     ? 
// @param import_date ? 

function gtcs_handle_import_file($file, $post_id = 0, $import_date = 'file') {
  set_time_limit(120);

  // Initially, Base it on the -current- time.
  $time = current_time('mysql', 1);
  // Next, If it's post to base the upload off:
  if ( 'post' == $import_date && $post_id > 0 ) {
    $post = get_post($post_id);
    if ( $post && substr( $post->post_date_gmt, 0, 4 ) > 0 )
      $time = $post->post_date_gmt;
  } elseif ( 'file' == $import_date ) {
    $time = gmdate( 'Y-m-d H:i:s', filemtime($file) );
  }

  // A writable uploads dir will pass this test. Again, there's no point overriding this one.
  if ( ! ( ( $uploads = wp_upload_dir($time) ) && false === $uploads['error'] ) )
    return new WP_Error( 'upload_error', $uploads['error']);

  $wp_filetype = wp_check_filetype( $file, null );

  extract( $wp_filetype );

  if ( ( !$type || !$ext ) && !current_user_can( 'unfiltered_upload' ) )
    return new WP_Error('wrong_file_type', __( 'Sorry, this file type is not permitted for security reasons.' ) ); //A WP-core string..

  //Is the file allready in the uploads folder?
  if ( preg_match('|^' . preg_quote(str_replace('\\', '/', $uploads['basedir'])) . '(.*)$|i', $file, $mat) ) {

    $filename = basename($file);
    $new_file = $file;

    $url = $uploads['baseurl'] . $mat[1];

    $attachment = get_posts(array( 'post_type' => 'attachment', 'meta_key' => '_wp_attached_file', 'meta_value' => ltrim($mat[1], '/') ));
    if ( !empty($attachment) )
      return new WP_Error('file_exists', __( 'Sorry, That file already exists in the WordPress media library.', 'add-from-server' ) );

    //Ok, Its in the uploads folder, But NOT in WordPress's media library.
    if ( 'file' == $import_date ) {
      $time = filemtime($file);
      if ( preg_match("|(\d+)/(\d+)|", $mat[1], $datemat) ) { //So lets set the date of the import to the date folder its in, IF its in a date folder.
        $hour = $min = $sec = 0;
        $day = 1;
        $year = $datemat[1];
        $month = $datemat[2];

        // If the files datetime is set, and it's in the same region of upload directory, set the minute details to that too, else, override it.
        if ( $time && date('Y-m', $time) == "$year-$month" )
          list($hour, $min, $sec, $day) = explode(';', date('H;i;s;j', $time) );

        $time = mktime($hour, $min, $sec, $month, $day, $year);
      }
      $time = gmdate( 'Y-m-d H:i:s', $time);

      // A new time has been found! Get the new uploads folder:
      // A writable uploads dir will pass this test. Again, there's no point overriding this one.
      if ( ! ( ( $uploads = wp_upload_dir($time) ) && false === $uploads['error'] ) )
        return new WP_Error( 'upload_error', $uploads['error']);
      $url = $uploads['baseurl'] . $mat[1];
    }
  } else {
    $filename = wp_unique_filename( $uploads['path'], basename($file));

    // copy the file to the uploads dir
    $new_file = $uploads['path'] . '/' . $filename;
    if ( false === copy( $file, $new_file ) )
      return new WP_Error('upload_error', sprintf( __('The selected file could not be copied to %s.', 'add-from-server'), $uploads['path']) );

    // Set correct file permissions
    $stat = stat( dirname( $new_file ));
    $perms = $stat['mode'] & 0000666;
    chmod( $new_file, $perms );
    // Compute the URL
    $url = $uploads['url'] . '/' . $filename;

    if ( 'file' == $import_date )
      $time = gmdate( 'Y-m-d H:i:s', filemtime($file));
  }

  //Apply upload filters
  $return = apply_filters( 'wp_handle_upload', array( 'file' => $new_file, 'url' => $url, 'type' => $type ) );


  return $return;
}

?>
