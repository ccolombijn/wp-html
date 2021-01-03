<?php
function wp_data()
{
  function wp_output()
  {

    global $post;

    $output = array(
      'title' => wp_val( $post, 'title' ),
      'custom_background' => wp_val( $post, 'custom_background' ),
      'custom_logo' => wp_val( $post, 'custom_logo' ),
      'theme_path' => get_template_directory(),

    );

    $args = array(
      'post_type' => $config->wp_query[0]->post_type,
      'orderby' => 'menu_order',
      'order' => 'ASC',
    );

    $query = new WP_Query( $args );

    $pages = [];

    if ( $query->have_posts() )
    {
       while ( $query->have_posts() ) // wp loop
       {

         $query->the_post();
         $slug = $post->post_name;

         array_push( $pages, $slug );

         $keys = $setup->keys;

         foreach( $keys as $key )
         {

           //array_push( $output, $slug . '_' . $key => wp_val( $post, $key ) );
           $output[ $slug . '_' . $key ] = wp_val( $post, $key );

         } // foreach( $keys as $key )

       } // while ( $query->have_posts() )

       wp_reset_postdata();
       //array_push( $output, 'pages' => $pages );
       $output[ 'pages' ] = $pages;
       return $output;

     } // if ( $query->have_posts() )
  }
  function wp_val( $post, $key )
  { // specifieke wordpress data ahv key

    switch ( $key )
    {

      case 'title' :
        $val =  get_bloginfo( 'name' );
        break;

      case 'custom_background' :
        $val = get_background_image();
        break;

      case 'custom_logo' :
        $val = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'medium' );
        break;

      case 'post_title':
        $val = $post->post_title;
        break;

      case 'post_content' :
        $val = $post->post_content;
        break;

      case 'post_excerpt' :
        $val = $post->post_excerpt;
        break;

      case 'post_thumbnail_url' :
        $val = get_the_post_thumbnail_url( $post->ID, 'full' );
        break;

      case 'post_thumbnail_excerpt' :
        $val = get_post( get_post_thumbnail_id( $post ) )->post_excerpt;
        break;

    }
    return $val;
  } // get_wp( $post, $key )
}
