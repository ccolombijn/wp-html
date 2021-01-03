<?php

require_once( 'dom-query.php' );

function dom_query_test()
{
  $html_query = new DOM_Query; // Roep instantie Class met DOMDocument en DOMXpath aan
  $html = '<div id="test">replace me</div>';
  $dom = $html_query->load( $html ); // Laad html in DOMDocument object regel 58
  $element = 'div#test'; // html element wat k wil aanpassen
  $content = 'replaced'; // inhoud die k wil plaatsen
  $html_query->set( $dom, $element, $content ); // <<< en hier gaat ie dus fout; 
  // zie ook if ( $element->nodeType === XML_TEXT_NODE ) {  op regel 82
  $html = $html_query->save();

  // bovenstaande zou k dus ook voor elkaar kunnen krijgen met
  // $html = str_replace('replace me',$content ,$html);
  // maar dan benader ik html als string en niet als object en daar gaat t uiteindelijk om
  //

    return $html;
}



echo dom_query_test();
