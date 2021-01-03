<?php
require_once( 'dom-query.php' );

$html_query = new DOM_Query;
$html = '<div>tag</div>'; // tag
$html .= '<div class="test_class">tag.class</div>'; // tag.class
$html .= '<div id="test_id">tag#id</div>'; // tag#id
$html .= '<div>tag</div>'; // tag
$html .= '<div class="test_class">tag.class</div> '; // tag.class
$html .= '<div class="test_class another_class">tag.class</div> '; // tag.class

$dom = $html_query->load( $html ); // HTML naar DOMDocument Object

$content = 'content';

$element_tag = 'div'; // zoek tag (meerdere instanties)
$element_dom = $html_query->get_element( $dom, $element_tag );
$element_tag_length = $element_dom->length;



$element_id = 'div#test_id'; // vervang inhoud tag#id (enkele instantie)
$element_dom = $html_query->set_element( $dom, $element_id, $content );
//$element_id_length = $element_dom->length;
$element_id_length = count( $element_dom );


$element_class = 'div.test_class'; // vervang inhoud tag.class (meerdere instanties)
$element_dom = $html_query->set_element( $dom, $element_class, $content );
$element_class_length = $element_dom->length;

$html = $dom->saveHTML(); // DOMDocument naar string

$output = $html;
$output .=  '<hr> Element <b>' . $element_tag . '</b> : ' . $element_tag_length;
$output .=  '<hr> Element <b>' . $element_id . '</b> : ' . $element_id_length;
$output .=  '<hr> Element <b>' . $element_class . '</b> : ' . $element_class_length;

echo  $output;
