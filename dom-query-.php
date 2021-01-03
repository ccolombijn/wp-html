<?php
require 'JSLikeHTMLElement.php';
libxml_use_internal_errors(true);

 /**
 * DOMDocument em DOMXpath
 * -
 */
class DOM_Query
{ // http://nl1.php.net/manual/en/class.domdocument.php


  private function dom( $html )
  // HTML string naar DOMDocument
  {
    //$dom = new \DOMDocument();
    $dom = new \DOMDocument('1.0', 'UTF-8');
    $dom->validateOnParse = true;
    $dom->registerNodeClass('DOMElement', 'JSLikeHTMLElement');
    $dom->loadHTML( $html );

      return $dom;

  }

  private function xpath(  $dom, $query )
  // Query DOMXPath
  {

    $xpath = new \DOMXPath( $dom );

      return $xpath->query( $query )->item(0);

  }

  private function get_tag_( $dom, $tag )
  { // http://ca.php.net/manual/en/domdocument.getelementsbytagname.php
    return $dom->getElementsByTagName( $tag );
  }

  private function get_id_( $dom, $id)
  { //

    return $this->get_attr_( $dom, 'id', $id );

  }

  private function get_class_( $dom, $class )
  {
    return $this->get_attr_( $dom, 'class', $id );
  }

  private function get_attr_( $dom, $attr, $val )
  {
    $xpath = new DomXPath($dom);
    $query = "//*[contains(concat(' ', normalize-space(@' . $attr . '), ' '), ' $val ')]";
    return $xpath->query( $query );
  }

  private function query( $element )
  // Converteert element naar XPath Query
  {
    if ( strpos( $element, '#' ) !== false ) // element#id
    {
      $el = explode( '#',$element );
      $query = '//' . $el[0] . '[@id=\'' . $el[1] . '\']';

    }
    elseif ( strpos( $element, '.' ) !== false ) // element.class
    {
      $el = explode( '.',$element );
      $query = '//' . $el[0] . '[@class=\'' . $el[1] . '\']';

    }
    else { // element
      $query = '//' . $element;
    }

      return $query;

  }

  private function element( $dom, $element )
  {
    if ( strpos( $element, '#' ) !== false ) // element#id
    {
      $el = explode( '#',$element );
      $element = $this->get_attr_( $dom, 'id', $el[1] );
    }
    elseif ( strpos( $element, '.' ) !== false ) // element.class
    {
      $el = explode( '.',$element );
      $element = $this->get_attr_( $dom, 'class', $el[1] );
    }
    else {
      $element = $this->get_tag_( $dom, $element );
    }

      return $element;

  }

  // ----------------------------------------------------------------------


  public function load( $html )
  // String naar DOM object
  {
      return $this->dom( $html );
  }

  public function get( $element ) // Moet aangepast worden;  $element->innerHTML werkt niet met JSLileHTMLElement.php
   // Content element weergeven
  { // https://keyvan.net/2010/07/javascript-like-innerhtml-access-in-php/

    $query = $this->query( $element );
    $element = $this->xpath(  $query );

    //$content = $element->innerHTML;

    //  return $content;
  }

  public function set_attr( $dom, $element, $attr, $val)
  // Attribute element aanpassen
  { // http://php.net/manual/en/domelement.setattribute.php

      $dom_nodelist = $this->get_element( $dom, $element );

      foreach( $dom_nodelist as $element ){
        $element->setAttribute($attr, $val);
      }

        return $dom_nodelist;
  }

  public function set_element( $dom, $element, $content )
  {
    $dom_nodelist = $this->element( $dom, $element );
    //$replacement = $dom->createDocumentFragment();
    //$replacement->appendXML($content);

    foreach( $dom_nodelist as $element ){
      //foreach ($element->childNodes as $child) {
        //$element->removeChild($child);
        //$element->appendChild($replacement);
        $element->innerHTML = $content;
      //}
    }
      return $dom_nodelist;
  }

  public function get_element( $dom, $element )
  {
    $dom_nodelist = $this->element( $dom, $element );
    return $dom_nodelist;
  }

  public function set( $dom, $element, $content )
  // Content element aanpassen
  { //http://nl1.php.net/manual/en/domdocument.createtextnode.php

    $query = $this->query( $element ); // element naar query

    $element = $this->xpath( $dom, $query ); // quey naar xpath

    $replacement = $dom->createDocumentFragment();

    $replacement->appendXML($content);

    foreach ($element->childNodes as $child) {
      // element moet content($child) bevatten wil $replacement vervangen kunnen worden; werkt dus niet op leeg element

      $element->removeChild($child);
      $element->appendChild($replacement);

    }
      return $element;
    //if ( $element->nodeType === XML_TEXT_NODE ) {
    //  $element->innerHTML = $content;
    //}

  }

  public function length( $dom, $element )
  { // http://php.net/manual/en/domxpath.query.php
    //
    $query = $this->query( $element ); // element naar query

    $element = $this->xpath( $dom, $query );

      //return count($element);
      return $element->length;
      // Notice: Undefined property: DOMElement::$length
  }

}
