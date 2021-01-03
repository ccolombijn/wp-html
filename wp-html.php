<?php
/*
 * Laad html document en vervangt/plaatst gegevens Wordpress (pagina's) a.h.v. gedefinieerde sleutelwaarden
*/
require_once( 'dom-query.php' );

require 'wp-html-customizer.php';
require 'wp-html-admin.php';

session_start();

class wp_html
{

  // Wordpress

  private function get_wp( $post, $key )
  { // Wordpress gegevens a.h.v. $post en sleutelwaarde

    switch ( $key )
    {

      case 'title' : // Titel
        $val =  get_bloginfo( 'name' );
        break;

      case 'custom_background' : // Achtergrond
        $val = get_background_image();
        break;

      case 'custom_logo' : // Logo
        $val = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'medium' )[0];
        break;

      case 'post_title': // Titel van bericht/pagina/media
        $val = $post->post_title;
        break;

      case 'post_content' : // inhoud van bericht/pagina/media
        $val = $post->post_content;
        break;

      case 'post_excerpt' : // samenvatting van van bericht/pagina/media
        $val = $post->post_excerpt;
        break;

      case 'post_thumbnail_url' : // uitgelichte afbeelding bij post
        $val = get_the_post_thumbnail_url( $post, 'full' );
        break;

      case 'post_thumbnail_excerpt' : // beschrijving bij afbeelding bij post
        $val = get_post( get_post_thumbnail_id( $post ) )->post_excerpt;
        break;

      default :
        $val = $key;


    }
    $this->_log( 'get_wp()-> ' . $key . ' : ' . $val );
      return $val;


  } //  get_wp( $post, $key )

  private function wp_query()
  {
    $config = $this->get_json( get_template_directory() . '/config.json' );

    $args = array( // configuratie voor Wordpress query
      'post_type' => $config->wp_query[0]->post_type, // type post_type uit configuratiegegevens; all,page,media etc.
      'orderby' => 'menu_order',
      'order' => 'ASC',
    );
    $this->_log( 'wp_query()-> post_type : ' . $config->wp_query[0]->post_type );
    $wp_query = new WP_Query( $args );

      return $wp_query;
  } // wp_query()
  private function wp_elements_html( $html )
  { // vervang vaste Wordpress elementen; wp-head,wp-footer,get_wp->title

    // wp-head
    //$wp_head = $this->wp_element_output( 'wp-head' );
    //$html = str_replace( '</head>', $wp_head . '</head>', $html );
    // wp-head
    //$wp_footer = $this->wp_element_output( 'wp-footer' );
    //$html = str_replace( '</body>', $wp_footer . '</body>', $html );
    $this->_log( 'wp_elements_html()' );
    $html_query = new DOM_Query; // DOMDocument / xPath
    $dom = $html_query->load( $html );

    global $post;
    // get_wp->title
    $element_dom = $html_query->set_element( $dom, 'title', $this->get_wp( $post, 'title') );

    $html = $dom->saveHTML();
      return $html;
  } // wp_elements_html( $html )

  private function wp_auto_elements_html( $html )
  { // Vervang elementen met Wordpress data op basis van #id of .class
  }
  private function wp_element_output( $element )
  {
    $wp_output =  file_get_contents( site_url() . '?output=' . $element );
    if ( strpos( $wp_output, '</title>'  ) === false )
    { // Verwijder <title> in wp output
      $wp_output_ = explode( '</title>', $wp_output );
      $wp_output = $wp_output_[1];
    }
      return $wp_output;
  }

  private function wp_template( $template )
  { //

  }
  private function wp_query_slug_keys( $html )
  {  // Vervang slug keys in html met Wordpress data in loop


    $keys = explode( ',', $config->keys ); // Maak array van sleutelwaarden in configuratiegegevens
    $wp_query = $this->wp_query(); // Roep Wordpress Query aan

    if ( $wp_query->have_posts() ) // Controleer of Wordpress iets kan weergeven
    {
       while ( $wp_query->have_posts() ) // loop door Wordpress Query
       {

         $wp_query->the_post();
         $slug = $post->post_name; // Haal slug (verkorte naam van Wordpress post voor o.a. url naar post)

         foreach ( $keys as $key ) // Loop door sleutelwaarden
         {

           $val = $this->get_wp( $post, $key ); // Gegevens uit Wordpress ahv huidge post en sleutelwaarde
           $html = str_replace( '{'  . $slug . '_' . $key . '}', $val , $html ); // Vervang slug_key in html

         }


       }
    }

      return $html ;

  } // wp_query_slug_keys( $html )
  private function wp_loop_html( $html, $elements ){

    $html_query = new DOM_Query; // DOMDocument
    $dom = $html_query->load( $html ); // Laad html in DOMDocument object
    $wp_query = $this->wp_query(); // Roep Wordpress Query aan

    $replacements = '';

    if ( $wp_query->have_posts() ) // Controleer of Wordpress iets kan weergeven
    {

      while ( $wp_query->have_posts() ) // loop door Wordpress Query
      {
        global $post;
        $wp_query->the_post();
        $slug = $post->post_name; // Slug van huidige post


        foreach ( $elements as $key=>$val ) // Loop door elementen
        {

          $element = $val; // Doelelement
          // Conroleer of $replacements sleutelwaarde bevat
          if(strpos( $replacements, $key . ' '  ) === false ){

            $key_part = explode( '_', $key );

            // ----------------------------------------------------------------------

            if( $key_part[1] == 'post' )
            { // Sleutelwaarde verwijst naar post
              if( $key_part[0] == $slug )
              { // Slug van huidige post in Wordpress loop komt overeen met post in Sleutelwaarde
                  $key_post = str_replace( $key_part[0] . '_', '', $key );
                  // log
                  $this->_log( 'wp_loop_html()->element: ' . $element . ' (' . $element_dom->length . ') ,sleutelwaarde : ' . $key );
                  // Gegevens uit Wordpress ahv Sleutelwaarde
                  $content = $this->get_wp( $post, $key_post );

                  if(strpos( $element, 'img'  ) === false )
                  { // element is geen afbeelding
                    if( $key_post == 'post_thumbnail_url'  )
                    { // Sleutelwaarde verwijst naar uitgelichte afbeelding, plaats als achtergrond
                      $element_dom = $html_query->set_attr(  $dom, $element, 'style', "background-image:url('" . $content . "')" );
                    } else
                    { // vervang inhoud element
                      $element_dom = $html_query->set_element( $dom, $element, $content );
                    }
                  }else{ // element is afbeelding
                    $element_dom = $html_query->set_attr(  $dom, $element, 'src', $content );
                  }
                  // voeg vervangen element toe aam $replacements
                  $replacements .= $key . ' ';

              }
            }else {
                // log
                $this->_log( 'wp_loop_html()->element: ' . $element . ' (' . $element_dom->length . ') , sleutelwaarde : ' . $key );

                $content = $this->get_wp( $post, $key ); // Gegevens uit Wordpress ahv sleutelwaarde
                if( strpos( $element, 'img'  ) === false ){
                  if( strpos(  $key, 'custom_background'  ) === false ){
                    $element_dom = $html_query->set_element( $dom, $element, $content );
                  } else {
                    $element_dom = $html_query->set_attr(  $dom, $element, 'style', "background-image:url('" . $content . "')" );
                  }

                }else{

                  $element_dom = $html_query->set_attr(  $dom, $element, 'src', $content );
                }
                $replacements .= $key . ' ';

            } // if( strpos( $key, 'post' ) !== false )
            // ----------------------------------------------------------------------

          }

        } // foreach ( $elements as $key=>$val )

      }// while ( $wp_query->have_posts() )
      // log
      $this->_log( 'wp_loop_html->$replacements : ' . $replacements );

      wp_reset_postdata();
    }//

    $html = $dom->saveHTML(); // DOMDocument object naar string

      return $html;

  } // wp_loop_html( $html, $elements )
  // ----------------------------------------------------------------------
  // html

  private function get_json( $src )
  { // Laad JSON bestand en laad gegevens in object
    $this->_log( 'get_json( ' . $src . ')' );

    $data = file_get_contents( $src ); // Laad bestand
    $data = json_decode( $data ); // Laad gegevens in object

      return $data;
  } // get_json( $src )

  private function sanitize_output( $buffer ) // Minify html
  { // https://stackoverflow.com/questions/6225351/how-to-minify-php-page-html-output

      $search = array(
          '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
          '/[^\S ]+\</s',     // strip whitespaces before tags, except space
          '/(\s)+/s',         // shorten multiple whitespace sequences
          '/<!--(.|\s)*?-->/' // Remove HTML comments
      );

      $replace = array(
          '>',
          '<',
          '\\1',
          ''
      );

      $buffer = preg_replace( $search, $replace, $buffer );

      return $buffer;

  } // sanitize_output( $buffer )

  private function dom_query( $html ){

    $html_query = new DOM_Query; // Roep Class met DOMDocement en DOMXpath aan
    $dom = $html_query->load( $html ); // Laad html in DOMDocument object

      return $dom;
  }

  private function dom_query_elements( $html )
  { // laad html als object in DOMDocument, query elementen en voeg vermelde waarden in



    $dom = $this->dom_query( $html ); // Laad html in DOMDocument object
    $elements = $this->get_json( get_template_directory() . '/elements.json' ); // Laad elementen

    foreach ( $elements as $key=>$val ) { // Loop door elementen

      $element = $val;
      $html_query->set( $dom, $element, $key ); // vervang sleutelwaarde van element

    }

    $html = $dom->saveHTML(); // DOMDocument object naar string

      return $html;

  } // dom_query_elements( $html )
  private $config;
  private $elements;

  private function data( $src ){
    $data = $this->get_json( get_template_directory() . '/' . $src . '.json' );
    switch( $src ){
      case 'config' :
        $this->$config = $data;
        break;
      case 'elements' :
        $this->$elements = $data;
        break;
    }
  }
  private $log; // log voor test doeleinden (wordt weergegeven indien $config->log=true )
  private function _log( $entry ){
    $this->$log .= 'console.log("' . $entry . '");';
  }
  // ----------------------------------------------------------------------

  public function view() // Weergaveprocedure; configuratiegegevens, doelpad, bronbestand, elementen, Wordpress
  {

    $config = $this->get_json( get_template_directory() . '/config.json' ); // Laad configuratiegegevens; doelpad,html bestandsnaam en sleutelwaarden

    $file_path = get_template_directory() . '/' . $config->dir . '/' . $config->file; // doelpad voor html bestand

    $html = file_get_contents( $file_path ); // laad html bestand
    if ( $config->minify ) { // Minify (tenzij $config->minify=true ); haal spaties, regelonderbrekingen en comments weg om onnodig gegevensgebruik van output te verminderen
      $html = $this->sanitize_output( $html );
    }
    //$html = $this->wp_query_slug_keys( $html ); // voeg Wordpress slug_keys in

    //$html = $this->dom_query_elements( $html ); // Pas elementen aan met vermelde sleutelwaarde

    $elements = $this->get_json( get_template_directory() . '/elements.json' ); // Laad elementen

    $html = $this->wp_loop_html( $html, $elements ); // Pas elementen aan met vermelde sleutelwaarde met gegevens uit Wordpress
    $html = $this->wp_elements_html( $html );

    if ( $config->log ) {
      $html = str_replace( '{log}', $this->$log, $html );
    } else {
      $html = str_replace( '<script>{log}</script>', '', $html );
    }

      echo $html;

  } // view()






}
