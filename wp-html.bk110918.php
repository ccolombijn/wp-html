<?php
/*
 * Laad html document en vervangt/plaatst gegevens Wordpress (pagina's) a.h.v. gedefinieerde sleutelwaarden
*/


class wp_html
{

  // Wordpress

  private function get_wp_pages()
  {


  }

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
        $val = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'medium' );
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
        $val = get_the_post_thumbnail_url( $post->ID, 'full' );
        break;

      case 'post_thumbnail_excerpt' : // beschrijving bij afbeelding bij post
        $val = get_post( get_post_thumbnail_id( $post ) )->post_excerpt;
        break;

      default :
        $val = 'geen waarde voor ' . $key;


    }
    return $val;


  } //  get_wp( $post, $key )




  // ----------------------------------------------------------------------
  // html



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

  private function wp_query()
  {
    $config = $this->get_json( get_template_directory() . '/config.json' );

    $args = array( // configuratie voor Wordpress query
      'post_type' => $config->wp_query[0]->post_type, // type post_type uit configuratiegegevens; all,page,media etc.
      'orderby' => 'menu_order',
      'order' => 'ASC',
    );

    $wp_query = new WP_Query( $args );

      return $wp_query;
  }

  private function wp_loop_html( $html, $elements ){

    $html_query = new DOM_Query; // DOMDocument
    $dom = $html_query->load( $html ); // Laad html in DOMDocument object
    $wp_query = $this->wp_query(); // Roep Wordpress Query aan

    if ( $wp_query->have_posts() ) // Controleer of Wordpress iets kan weergeven
    {

      while ( $wp_query->have_posts() ) // loop door Wordpress Query
      {
          foreach ( $elements as $key=>$val ) // Loop door elementen
          {
          global $post;

          $wp_query->the_post();
          $slug = $post->post_name; // Slug van huidige post
          $element = $val; // Doelelement
          $key_part = explode( '_', $key );
          // ----------------------------------------------------------------------
          if( strpos( $key, 'post' ) !== false )
          { // Controleer of sleutelwaarde naar post verwijst

            if ( $key_part[0] == $slug ){ // Eerst deel van sleutelwaarde komt overeen met slug van huidige post

              $key_post = str_replace( $key_part[0] . '_', '', $key);
              //$content = $this->get_wp( $post, $key_post ); // Gegevens uit Wordpress ahv huidge post en sleutelwaarde
              $content = 'test';
              $element_dom = $html_query->set_element( $dom, $element, $content ); // Vervang content in DOMDocument object met DOMXpath Query

            }
            elseif ( $key_part[0] == 'img' )
            { // Plaats als afbeelding

              if ( $key_part[1] == $slug ){

                $key_post = str_replace( $key_part[0] . '_' . $key_part[1] . '_' , '', $key);
                $content = $this->get_wp( $post, $key_post );
                $html_query->set_attr( $dom, $element, 'src', $content );

              } // if ( $key_part[1] == $slug ){

            } elseif ( $key_part[0] == 'bg' ) { // Plaats als achtergrondafbeelding

              if ( $key_part[1] == $slug ){

              } // if ( $key_part[1] == $slug )

            } //elseif ( $key_part[0] == 'bg' )

          } // if( strpos( $key, 'post' ) !== false )
          // ----------------------------------------------------------------------
          else
          { // Sleutelwaarde verwijst niet naar post



              $content = $this->get_wp( $post, $key ); // Gegevens uit Wordpress ahv sleutelwaarde
              $element_dom = $html_query->set_element( $dom, $element, $content );



          }

          $element_dom = $html_query->set_element( $dom, $element,  $this->get_wp( $post, $key ));
        } // foreach ( $elements as $key=>$val )

      }// while ( $wp_query->have_posts() )
      wp_reset_postdata();
    }//

    $html = $dom->saveHTML(); // DOMDocument object naar string

      return $html;

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

  private function dom_query( $html ){

    $html_query = new DOM_Query; // Roep Class met DOMDocement en DOMXpath aan
    $dom = $html_query->load( $html ); // Laad html in DOMDocument object

      return $dom;
  }

  private function dom_query_elements( $html )
  { // laad html als object in DOMDocument, query elementen en voeg vermelde waarden in



    //$dom = $this->dom_query( $html ); // Laad html in DOMDocument object

    $html_query = new DOM_Query; // Roep Class met DOMDocement en DOMXpath aan
    $dom = $html_query->load( $html ); // Laad html in DOMDocument object
    $elements = $this->get_json( get_template_directory() . '/elements.json' ); // Laad elementen

    foreach ( $elements as $key=>$val ) { // Loop door elementen

      $element = $val;
      $html_query->set( $dom, $element, $key ); // vervang sleutelwaarde van element

    }

    $html = $dom->saveHTML(); // DOMDocument object naar string

      return $html;

  } // dom_query_elements( $html )
  // ----------------------------------------------------------------------

  public function view() // Weergaveprocedure; configuratiegegevens, doelpad, bronbestand, elementen, Wordpress
  {

    $config = $this->get_json( get_template_directory() . '/config.json' ); // Laad configuratiegegevens; doelpad,html bestandsnaam en sleutelwaarden

    $file_path = get_template_directory() . '/' . $config->dir . '/' . $config->file; // doelpad voor html bestand

    $html = file_get_contents( $file_path ); // laad html bestand

    $html = $this->sanitize_output( $html ); // Minify; haal spaties, regelonderbrekingen en comments weg om onnodig gegevensgebruik van output te verminderen

    //$html = $this->wp_query_slug_keys( $html ); // voeg Wordpress slug_keys in

    //$html = $this->dom_query_elements( $html ); // Pas elementen aan met vermelde sleutelwaarde

    $elements = $this->get_json( get_template_directory() . '/elements.json' ); // Laad elementen

    $html = $this->wp_loop_html( $html, $elements ); // Pas elementen aan met vermelde sleutelwaarde met gegevens uit Wordpress

      echo $html;


  } // view()



  private function get_json( $src ) // Laad JSON bestand en laad gegevens in object
  {
    $data = file_get_contents( $src ); // Laad bestand
    $data = json_decode( $data ); // Laad gegevens in object

      return $data;
  }


}
