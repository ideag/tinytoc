<?php
/*
Plugin Name: tinyTOC
Plugin URI: http://wp.tribuna.lt/tiny-toc
Description: Automaticly builds a Table of Contents once specific number (eg. 3) of headings (h1-h6) is reached and inserts it before or after post/page content
Version: 0.3
Author: Arūnas
Author URI: http://wp.tribuna.lt/
License: GPLv2 or later
Text Domain: tiny_toc
*/
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
  echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
  exit;
}

//==========================================================
// init textdomain
load_plugin_textdomain( 'tiny_toc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
load_muplugin_textdomain( 'tiny_toc', dirname( plugin_basename( __FILE__ ) ) . '/languages/');

//==========================================================
// load associated files
require_once(plugin_dir_path( __FILE__ ).'tiny_options.php');
require_once(plugin_dir_path( __FILE__ ).'tiny_widget.php');

// init tinyConfiguration
$tiny_toc_options = new tiny_toc_options(
  'tiny_toc',
  __('tinyTOC','tiny_toc'),
  __('tinyTOC Options','tiny_toc'),
  array(
    "main" => array(
      'title' => __('Main Settings','tiny_toc'),
      'callback' => '',
      'options' => array(
        'min' => array(
          'title'=>__('Minimum entries for TOC','tiny_toc'),
          'callback' => 'select',
          'args' => array(
            'values' => array(
              2=>2,
              3=>3,
              4=>4,
              5=>5,
              6=>6,
              7=>7,
              8=>8,
              9=>9,
              10=>10,
            )
          )
        ),
        'position' => array(
          'title'=>__('Insert TOC','tiny_toc'),
          'callback' => 'radio',
          'args' => array(
            'values' => array(
              'above' => __('Above the text','tiny_toc'),
              'below' => __('Below the text','tiny_toc'),
              'neither' => __('Do not display automatically','tiny_toc'),
//              'custom' =>
            )
          )
        )
      )
    )
  ),
  array(
    "use_css"=>false,
    "position"=>'above',
    "min"=>3
  ),
  __FILE__
);
$tiny_toc_options->load();
register_activation_hook(__FILE__, array($tiny_toc_options,'add_defaults'));
add_action('admin_init', array($tiny_toc_options,'init') );
add_action('admin_menu', array($tiny_toc_options,'add_page'));


add_filter( 'the_content', array('tiny_toc','filter'), 100);
add_shortcode( 'toc', array('tiny_toc','shortcode'));
function get_toc($attr=array()) {return tiny_toc::template($attr);}
function the_toc($attr=array()) {echo tiny_toc::template($attr);}
/* Find all headings and create a TOC */
class tiny_toc {
  static function template($attr=array()) {
    global $post, $tiny_toc_options;
    $min = (isset($attr['min'])&&$attr['min']>0)?$attr['min']:$tiny_toc_options->values['min'];
    $toc = tiny_toc::create($post->post_content,$min);
    return $toc;
  }
  static function shortcode($attr,$content=false) {
    global $post, $tiny_toc_options;
    $min = (isset($attr['min'])&&$attr['min']>0)?$attr['min']:$tiny_toc_options->values['min'];
    $toc = tiny_toc::create($post->post_content,$min);
    return $toc;
  }
  static function filter($content) {
    global $tiny_toc_options;
    $toc = tiny_toc::create($content,$tiny_toc_options->values['min']);
    if ($tiny_toc_options->values['position']=='above') {
      $content = $toc.$content;
    } elseif ($tiny_toc_options->values['position']=='below') {
      $content = $content.$toc;
    }
    return $content;
  }

  static function find_parent(&$items,$item) {
    if (sizeof($items)==0) { return 0; }
    $i = 0;
    $parent = false;
    do {
      ++$i;
      $previous = sizeof($items)-$i;
      if ($item->depth>$items[$previous]->depth) {
        $parent = $items[$previous]->db_id;
      }
    } while (!$parent && sizeof($items)-$i > 0);
    if (sizeof($items)-$i == 0) { return 0; }
    $a = 0;
    while ($item->depth - $items[$previous]->depth > 1) {
      ++$a;
      $empty_item = new stdClass();
      $empty_item->text = '';
      $empty_item->name = '';
      $empty_item->depth = $item->depth-$a;
      $empty_item->id = $parent.'-skip'.$a;
      $empty_item->db_id = sizeof($items)+1;
      $empty_item->parent = $parent;
      $empty_item->empty = true;
      $items[] = $empty_item;
      $previous = sizeof($items)-$i;
    }
    return $parent;
  }

  static function parse(&$content) {
    $content = '<html><body>'.($content).'</body></html>';
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($content);
    libxml_use_internal_errors(false);
    $xpath = new DOMXPath($dom);
    $tags = $xpath->query('/html/body/*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]');
    $items = array();
    $min_depth = 6;
    $parent = array();
    for($i=0;$i<$tags->length;++$i) {
      $id = $tags->item($i)->getAttribute('id');
      if(!$id) {
        $id = 'h'.$i;
        $tags->item($i)->setAttribute('id',$id);
      }
      $depth = $tags->item($i)->nodeName[1];
      if ($depth<$min_depth) {
        $min_depth = $depth;
      }
      $item = new stdClass();
      $item->text = $tags->item($i)->nodeValue;
      $item->name = $tags->item($i)->nodeName;
      $item->depth =$depth;
      $item->id = $id;
      $item->parent = tiny_toc::find_parent($items,$item);
      $item->db_id = sizeof($items)+1;
      $items[] = $item;
    }
    $text = $xpath->query('/html/body');
    $text = $dom->saveHTML($text->item(0));
    $content = $text;
    return $items;
  }

  static function create(&$content, $min) {
    $items = tiny_toc::parse($content);
    $output = '';
    if (sizeof($items)>=$min) {
      $walker = new tiny_toc_walker();
      $output = $walker->walk($items,0);
      $output = "<nav class=\"tiny_toc\">\n<ol>\n{$output}</ol>\n</nav>\n\n";
    }
    return $output;
  }
}

class tiny_toc_walker extends Walker {
  var $db_fields = array(
    'parent' => 'parent',
    'id' => 'db_id'
  );
  function start_lvl(&$output, $depth = 0, $args = array()) {
    $output .= "\n<ol>\n";
  }
  function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
    $output .= '<li>';
    if (isset($object->empty) && $object->empty) {
    } else {
      $output .= "<a href=\"#{$object->id}\">{$object->text}</a>";
    }
  }
  function end_el( &$output, $object, $depth = 0, $args = array() ) {
    $output .= "</li>\n";
  }
  function end_lvl(&$output,$depth=0,$args=array()) {
    $output .= "</ol>\n";
  }
}

?>
