<?php
$view = $_GET['output'];
switch ( $view ) {
  case 'wp_head':
    wp_head();
    break;
  case 'wp_footer':
    wp_footer() . 'test';
    break;
  default:
    $output = new wp_html;
    $output->view();
    break;
}
