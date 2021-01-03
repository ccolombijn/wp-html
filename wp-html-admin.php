<?php

// ----------------------------------------------------------------------
// wp-admin

function theme_setup() // thema opties wp-admin
 {

    add_theme_support( 'html5' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'custom-background', array(
  	   'default-color' => '000000',
    ) );
    add_theme_support( 'custom-logo', array(
		    'flex-width' => true,
	 ) );
   add_theme_support( 'post-thumbnails' );

   $GLOBALS['content_width'] = 1200;



 } // theme_setup()

add_action( 'after_setup_theme', 'theme_setup' );


function wpdev_filter_login_head() // custom_logo bij inlogscherm Wp-admin
{

    if ( has_custom_logo() ) :

        $image = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'medium' );
        ?>
        <style type="text/css">
            .login h1 a {
                background-image: url(<?php echo esc_url( $image[0] ); ?>);
                -webkit-background-size: <?php echo absint( $image[1] )?>px;
                background-size: <?php echo absint( $image[1] ) ?>px;
                height: <?php echo absint( $image[2] ) ?>px;
                width: <?php echo absint( $image[1] ) ?>px;
            }
        </style>
        <?php
    endif;
} //  wpdev_filter_login_head()

add_action( 'login_head', 'wpdev_filter_login_head', 100 );

function wp_html_admin()
{
  $wp_user = wp_get_current_user();
  $wp_allowed_roles = array('administrator');
  $wp_capability = current_user_can('edit_themes');
  ?>
	<div class="wrap">
		<h1 class="wp-heading-inline">Wordpress naar HTML</h1>
    <a href="http://localhost/wordpress/wp-admin/admin.php?page=wp-html-add" class="page-title-action">Nieuw element</a>
    <hr class="wp-header-end">
    <?php

      $the_config = file_get_contents(  get_template_directory() . '/config.json' );
      $the_config = json_decode( $the_config );

    ?>
    <table class="form-table">
      <tr>
        <th scope="row"><label>HTML Exportmap</label></th>
        <td><input type="text"  value="<?php echo $the_config->dir ?>" class="regular-text">
          <p class="description">Deze map bevat alle bestanden zoals geexporteerd uit bijv. Adobe Muse</p>
        </td>
      </tr>
      <tr>
        <th scope="row"><label>HTML Bestand</label></th>
        <td><input type="text"  value="<?php echo $the_config->file ?>" class="regular-text">

        </td>
      </tr>
      <tr>
        <th scope="row"><label>Sleutelwaarden</label></th>
        <td><input type="text"  value="<?php echo $the_config->keys ?>" class="regular-text">

        </td>
      </tr>
    </table>
    <table class="wp-list-table widefat fixed striped pages">
	   <thead>
	      <tr><th><span>Element</span></th><th><span>Sleutelwaarde</span></th>	</tr>
	   </thead>

    	<tbody id="the-list">
        <?php

        $the_elements = file_get_contents(  get_template_directory() . '/elements.json' );
        $the_elements = json_decode( $the_elements );

        foreach( $the_elements as $key=>$val )
        { ?>
          <tr><td><strong><?php echo $val ?></strong></td><td><strong><?php echo $key ?></strong></td></tr>
        <?php
        } ?>

      </tbody>

    </table>
  </div>
	<?php

}

function wp_html_menu()
{

  add_menu_page('WP Html', 'Wordpress naar HTML', 'manage_options', 'wp-html', 'wp_html_admin', 'dashicons-media-code', 3);
}
//if( $wp_capability ){
  add_action('admin_menu', 'wp_html_menu');
//}
