<?php

// On theme initialization
// Creates the database tables
// *TODO* check if they already exist
// *TODO* add versioning system for upgrades
add_action('after_switch_theme', 'initialize_tables');
function initialize_tables()
{
  global $gtcs12_db;

  $gtcs12_db->RecreateTables();
}

add_action('after_switch_theme', 'initialize_roles');
function initialize_roles() 
{
  // remove the default roles
  // remove_role('author');
  // remove_role('editor');
  // remove_role('contributor');
  // remove_role('subscriber');
  
  // remove old roles so they can be updated
  remove_role('teacher');
  remove_role('student');

  $result = add_role(
    //'teacher', 
    'author', 
    //'Teacher',
    'Author', 
    array( 
      'delete_posts' => true,
      'delete_published_posts' => true,
      'edit_posts' => true,
      'edit_published_posts' => true,
      'read' => true,
      'upload_files' => true,
    )
  );

  if ($result === null) {
    echo "Error. Teacher role not created.";
  }

  $result = add_role(
    'subscriber',
    //'student',
    'Subscriber',
    //'Student',
    array( 
      'delete_posts' => true,
      'delete_published_posts' => true,
      'edit_posts' => true,
      'edit_published_posts' => true,
      'read' => true,
      'upload_files' => true,
    )
  );
  
  if ($result === null) {
    echo "Error. Student role not created.";
  }
}

// creates an array that can be used to create a wordpresss page 
function default_page($name, $title, $template)
{
  return array( 
    'post_name'     => $name,  
    'post_title'    => $title,
    'post_status'   => 'publish',
    'post_type'     => 'page',
    'meta'          => array(
      '_wp_page_template'  => 'templates/'.$template, 
    ),
  );
}

add_action('after_switch_theme', 'create_default_pages');
function create_default_pages()
{
  $default_pages = array();
  $default_pages['main'] = default_page('main', 'Main', 'main.php');
  $default_pages['debug'] = default_page('debug', 'Debug', 'debug.php'); 
  $default_pages['my-class'] = default_page('my-class', 'MyClass', 'my-class.php'); 
  $default_pages['projects'] = default_page('projects', 'Projects', 'projects-search.php');
  $default_pages['profile'] = default_page('profile', 'Profile', 'profile.php'); 
  $default_pages['manage-courses'] = default_page('manage-courses', 'Manage Courses', 'manage-courses.php'); 
  $default_pages['manage-assignments'] = default_page('manage-assignments', 'Manage Assignments', 'manage-assignments.php'); 
  $default_pages['assignment'] = default_page('assignment', 'Assignment', 'single-assignment.php'); 
  $default_pages['manage-students'] = default_page('manage-students', 'Manage Students', 'manage-enrollments.php'); 
  
  foreach( $default_pages as $key => $page ) {
    if(get_page_by_title($page['post_title']) != null) // do not need to recreate page
      continue;

    if ($id = wp_insert_post($page)) {
      if (!empty($page['meta'])) {
        // iterate over the page's meta values
        foreach($page['meta'] as $meta_key => $meta_value) {
          update_post_meta($id, $meta_key, $meta_value);
        }
      }
    }
  }
}

add_action('after_switch_theme', 'create_default_menus');
function create_default_menus()
{
  $page_id = array();
  $page_id['main'] = get_page_by_title('Main')->ID;
  $page_id['myclass'] = get_page_by_title('My Class')->ID;
  $page_id['projects'] = get_page_by_title('Projects')->ID;

  $default_menus = array(
    'Main Menu' =>  array(
      'location' => 'primary',
      'menu-items' =>  array(
        'Home' =>  array(
          'menu-item-type'      =>  'post_type',
          'menu-item-title'     =>  'Home',
          'menu-item-object'    =>  'page',
          'menu-item-object-id' =>  $page_id['main'],
          'menu-item-status'    =>  'publish'
        ),
        'My Class' =>  array(
          'menu-item-type'      =>  'post_type',
          'menu-item-title'     =>  'My Class',
          'menu-item-object'    =>  'page',
          'menu-item-object-id' =>  $page_id['myclass'],
          'menu-item-status'    =>  'publish'
        ),
        'Projects' =>  array(
          'menu-item-type'      =>  'post_type',
          'menu-item-title'     =>  'Projects',
          'menu-item-object'    =>  'page',
          'menu-item-object-id' =>  $page_id['projects'],
          'menu-item-status'    =>  'publish'
        ),
      )
    )
  );

  // Creates the Default Menus
  $menu_locations = array();
  foreach($default_menus as $menu_name => $menu_args) {
    $menu_exists = wp_get_nav_menu_object($menu_name);
    
    if(!$menu_exists) {
      $menu_id = wp_create_nav_menu($menu_name);
      foreach( $menu_args['menu-items'] as $menu_item ) {
        // assigns the page, title, etc to the menu item
        wp_update_nav_menu_item( $menu_id, 0, $menu_item );
      }
      $menu_locations[$menu_args['location']] = $menu_id;
    }
  }

  if (isset($menu_locations)) {
    // Assigns the menu to the correct menu location (Primary Menu, Footer Menu, etc.)
    set_theme_mod('nav_menu_locations', array_map('absint', $menu_locations));
  }
}

// Changes the permalink settings to /%postname%/
add_action('after_switch_theme', 'update_permalinks');
function update_permalinks()
{
  if (get_option('permalink_structure') != '/%postname%/') {
    global $wp_rewrite;
    $wp_rewrite->set_permalink_structure('/%postname%/');
  }
}
?>
