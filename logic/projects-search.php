<?php
$defaultResultsPerPage = 10; // default number of projects per page
$defaultOrder = 'DES';      // default sort order
$defaultCategory = 'date';  // default sorting method

// keep track of results page being viewed
if(isset($_GET['x']))
  $currentPage = $_GET['x'];
else
  $currentPage = 1;

// gets the whole url for proper redirection to results page
if(isset($_GET['category']))
  $category = $_GET['category'];
else
  $category = $defaultCategory;

if(isset($_GET['order']))
  $order = $_GET['order'];
else
  $order = $defaultOrder ;

if(isset($_GET['results']))
  $resultsPerPage = $_GET['results'];
else
  $resultsPerPage = $defaultResultsPerPage ;

if(isset($_GET['search']))
  $search = $_GET['search'];
else
  $search = "";

// retrieve project posts
$args = array(
  'posts_per_page'   => $resultsPerPage,
  'offset'           => ($currentPage - 1) * $resultsPerPage,
  'order'            => $order,
  'orderby'          => $category,
  'post_status'      => 'publish',
  'suppress_filters' => true,
  'name'             => $search,
  'post_type'        => 'post'
  );

$postslist = get_posts($args);

// count of all posts
if($search == '')
{
  $numPosts = wp_count_posts('post')->publish;
}
else
{
  $numPosts = count($postslist);
}

// gets count of search result pages
$numPages = ceil($numPosts / $resultsPerPage);

//$gtcs12_db->CreateSubmission('submission post', 23, 4, 184, 'submission description');
?>
