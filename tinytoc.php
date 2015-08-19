<?php
/*
Plugin Name: tinyTOC
Plugin URI: http://wordpress.org/plugins/tinytoc
Description: Automaticly builds a Table of Contents using headings (h1-h6) in post/page/CPT content
Version: 0.8.0
Author: Arūnas Liuiza
Author URI: http://arunas.co/
License: GPLv2
Text Domain: tinytoc
Domain Path: /languages

    Copyright 2014  Arūnas Liuiza  (email : ask@arunas.co)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// block direct access to plugin file
defined('ABSPATH') or die( __("No script kiddies please!", 'tinytoc' ) );

// uninstall hook
register_uninstall_hook( __FILE__, array( 'tinyTOC', 'uninstall' ) );
// init tinyTOC
add_action( 'plugins_loaded', array( 'tinyTOC', 'init' ) );

class tinyTOC {
  public static $options = array(
    "general_position"  => 'before',
    "general_min"       => 3,
    "general_widget"    => false,
    "general_list_type" => 'ol',
    "general_bookmark"  => 'numeric',
  );
  public static function init() {
    self::init_options();
    if ( is_admin() ) {
      require_once ( plugin_dir_path( __FILE__ ).'includes/options.php' );
      add_action( 'admin_menu', array( 'tinyTOC', 'init_settings' ) );
      add_filter( 'plugin_action_links', array( 'tinyTOC', 'settings_link' ), 10, 2 );
    }    
    add_filter(     'the_content',  array( 'tinyTOC', 'filter' ), 100 );
    add_shortcode(  'toc',          array( 'tinyTOC', 'shortcode' ) );
    if ( self::$options['general_widget'] ) {
      require_once ( plugin_dir_path( __FILE__ ).'includes/widget.php' );
      add_action( 'widgets_init', array( 'tinyTOC', 'widget' ) );
    }
    load_plugin_textdomain( 'tinytoc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
  }
  public static function init_options() {
    $options_old = get_option( 'tiny_toc_options' );
    if ( $options_old ) {
      self::update_05($options_old);
    }
    $options = get_option( 'tinytoc_options' );
    self::$options = wp_parse_args( $options, self::$options );
  }
  public static function init_settings() {
    $settings = array(
      'id'          => 'tinytoc_options',
      'title'       => __( 'tinyTOC Options', 'tinytoc' ),
      'menu_title'  => __( 'tinyTOC', 'tinytoc' ),
      'fields'      => array(
        "general" => array(
          'title' => __('Main Settings','tinytoc'),
          'callback' => '',
          'options' => array(
            'min' => array(
              'title'=>__('Minimum entries for TOC','tinytoc'),
              'callback' => 'number',
              'args' => array(
                'min'  => 1,
                'max'  => 10,
                'step' => 1, 
                'description' => __('How many headings have to be found in post content for TOC to be generated?', 'tinytoc'),
              )
            ),
            'list_type' => array(
              'title'    => __('List appearance','tinytoc'),
              'callback' => 'radio',
              'args'     => array(
                'values'   => array(
                  'ol'       => __('Numbers','tinytoc'),
                  'ul'       => __('Bullets','tinytoc'),
                )
              )
            ),
            'position' => array(
              'title'    => __('Insert TOC','tinytoc'),
              'callback' => 'radio',
              'args'     => array(
                'description' => __('Where to insert TOC in post content. Choose "Do not display autmatically" if you want to use widget/shortcode instead.', 'tinytoc'),
                'values'   => array(
                  'before'   => __('Above the text','tinytoc'),
                  'after'    => __('Below the text','tinytoc'),
                  'false'    => __('Do not display automatically','tinytoc'),
                )
              )
            ),
            'bookmark' => array(
              'title'    => __('Link format','tinytoc'),
              'callback' => 'radio',
              'args'     => array(
                'description' => __('What format to use for auto generated TOC links.', 'tinytoc'),
                'values'   => array(
                  'numeric'   => __('Numeric (e.g. #h1, #h2 ...)','tinytoc'),
                  'slug'      => __('Text (e.g. #my-title-here)','tinytoc'),
                  'combined'  => __('Combined (e.g. #h1-my-title-here)','tinytoc'),
                )
              )
            ),
            'widget' => array(
              'title'       => __( 'Enable Widget', 'tinytoc' ),
              'callback'    => 'checkbox',
              'args'     => array(
                'description' => __( 'If unchecked, the tinyTOC widget will not be loaded.', 'tinytoc' )
              ),
            )
          )
        )
      )
    );
    tinyTOC_Options::init( $settings );
  }
  public static function settings_link($links, $file) {
    if ( $file === plugin_basename( __FILE__ ) ) {
      $links['settings'] = '<a href="' .
        admin_url( "options-general.php?page=tinytoc/includes/options.php" ) .
        '">' . 
        __( 'Settings', 'tinytoc' ) .
        '</a>';
    }
    return $links;
  }


  public static function shortcode($attr=array(),$content=false) {
    global $post;
    $defaults = array(
      'min' => self::$options['general_min'],
    );
    $attr = shortcode_atts( $defaults, $attr );
    $toc = self::create($post->post_content, $attr['min'] );
    return $toc;
  }
  public static function filter($content) {
    $toc = self::create( $content, self::$options['general_min'] );
    if ('before' == self::$options['general_position'] ) {
      $content = $toc."\r\n".$content;
    } elseif ('after' == self::$options['general_position'] ) {
      $content = $content."\r\n".$toc;
    }
    return $content;
  }
  public static function create(&$content, $min) {
    $items = self::parse($content);
    $output = '';
    if (sizeof($items)>=$min) {
      $walker = new tinyTOC_walker();
      $output = $walker->walk($items,0);
      $tag = self::$options['general_list_type'];
      $output = "<nav class=\"tinytoc tiny_toc\">\n<{$tag}>\n{$output}</{$tag}>\n</nav>\n\n";
    }
    return $output;
  }
  public static function widget() {
    register_widget( 'TinyTOC_Widget' );
  }

  private static function find_parent(&$items,$item) {
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
    if (!$parent && sizeof($items)-$i == 0) { return 0; }
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
  private static function parse(&$content) {
    $content = mb_convert_encoding($content, 'HTML-ENTITIES', get_bloginfo('charset'));
    $content = '<html><head><meta charset="'.get_bloginfo('charset').'"></head><body>'.($content).'</body></html>';
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($content);
    libxml_use_internal_errors(false);
    $xpath = new DOMXPath($dom);
    $tags = $xpath->query('/html/body/*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]');
    $items = array();
    $min_depth = 6;
    $parent = array();
    $used = array();
    for ( $i=0; $i<$tags->length; ++$i ) {
      $text = $tags->item($i)->nodeValue;
      $id = $tags->item($i)->getAttribute('id');
      $name = $id;
      if( !$id ) {
        $slug = remove_accents($text);
        $slug = sanitize_title_with_dashes($slug);
        $id = 'h'.$i;
        switch ( self::$options['general_bookmark'] ) {
          case 'numeric' :
            $name = $id;
          break;
          case 'slug' :
            $name = $slug;
          break;
          case 'combined' :
            $name = $id.'-'.$slug; 
          break;
        }
        // Prevent the same names
        $old_name = $name;
        if ( in_array( $name, $used ) ) {
          $occurences = array_count_values($used);
          $next_number = ( $occurences[$name] + 1 );
          $name = $name .'-'. $next_number;
        }
        $used[] = $old_name;

        $tags->item($i)->setAttribute('id',$name);
      }
      $depth = $tags->item($i)->nodeName[1];
      if ($depth<$min_depth) {
        $min_depth = $depth;
      }
      $item = new stdClass();
      $item->text = $text;
      $item->name = $tags->item($i)->nodeName;
      $item->depth =$depth;
      $item->id = $name;
      $item->parent = self::find_parent($items,$item);
      $item->db_id = sizeof($items)+1;
      $items[] = $item;
    }
    $text = $xpath->query('/html/body');
    $text = $dom->saveHTML($text->item(0));
    if ( version_compare(PHP_VERSION, '5.3.6', '<') ) {
      $text = str_replace( array('<html>', '</html>', '<body>', '</body>'), '', $text );
      $text = preg_replace( '/^<!DOCTYPE.+?>/', '', $text );
    }
    $content = $text;
    return $items;
  }
  private static function update_05( $old_options = array() ) {
    $options = array();
    foreach ( $old_options as $key => $value ) {
      $key = 'general_'.$key;
      $options[$key] = $value;
    }
    switch ($options['general_position']) {
      case 'above' : 
        $options['general_position'] = 'before';
      break;
      case 'below' : 
        $options['general_position'] = 'after';
      break;
      case 'neither' : 
        $options['general_position'] = 'false';
      break;
    }
    if ( !isset( $options['general_widget'] ) ) {
      $options['general_widget'] = false;
    }
    unset($options['general_use_css']);
    self::$options = wp_parse_args( $options, self::$options );
    update_option( 'tinytoc_options', self::$options );
    delete_option( 'tiny_toc_options' );
  }
  
  public static function uninstall() {
    // delete plugin options
    delete_option( 'tinytoc_options' );
  }
}

function get_toc($attr=array()) {return tinyTOC::shortcode($attr);}
function the_toc($attr=array()) {echo tinyTOC::shortcode($attr);}

class tinyTOC_walker extends Walker {
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