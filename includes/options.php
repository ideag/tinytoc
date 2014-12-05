<?php 
// ========================================== SETTINGS

class tinyTOC_Options {
  private static $fields = array();
  private static $id = '';
  private static $menu_title = '';
  private static $title = '';
  private static $description = '';
  private static $file = '';
  private static $role = 'manage_options';

  public static function init( $args = '' ) {
    if ( !is_array( $args ) ) {
      $args = wp_parse_args( $args );
    }
    self::$fields     = $args['fields'];
    self::$file       = isset( $args['file'] ) && $args['file'] ? $args['file'] : __FILE__;
    self::$id         = $args['id'];
    self::$menu_title = $args['menu_title'];
    self::$title      = $args['title'];
    self::$role       = isset( $args['role'] ) && $args['role'] ? $args['role'] : self::$role;
    self::build_settings();
    add_options_page(self::$title, self::$menu_title, self::$role, self::$file, array('tinyTOC_Options','page'));
  }

  // Register our settings. Add the settings section, and settings fields
  public static function build_settings(){
    register_setting( self::$id, self::$id, array( 'tinyTOC_Options' , 'validate' ) );
    if (is_array(self::$fields)) foreach (self::$fields as $group_id => $group) {
      add_settings_section( $group_id, $group['title'], $group['callback']?is_array($group['callback'])?$group['callback']:array('tinyTOC_Options',$group['callback']):'', self::$file );
      if (is_array($group['options'])) foreach ($group['options'] as $option_id => $option) {
        $option['args']['option_id'] = $group_id.'_'.$option_id;
        $option['args']['title'] = $option['title'];
        add_settings_field($option_id, $option['title'], $option['callback']?is_array($option['callback'])?$option['callback']:array('tinyTOC_Options',$option['callback']):'', self::$file, $group_id,$option['args']);      
      }
    }
  }

  // ************************************************************************************************************
  // Utilities
  public static function is_assoc($arr) {
    return array_keys($arr) !== range(0, count($arr) - 1);
  }

  // ************************************************************************************************************

  // Callback functions

  // DROP-DOWN-BOX - Name: select - Argument : values: array()
  public static function select($args) {
    $items = $args['values'];
    echo "<select id='".self::$id."_{$args['option_id']}' name='".self::$id."[{$args['option_id']}]'>";
    if (self::is_assoc($items)) {
      foreach($items as $key=>$item) {
        $selected = selected( $key, tinyTOC::$options[$args['option_id']], false );
        echo "<option value='$key' $selected>$item</option>";
      }
    } else {
      foreach($items as $item) {
        $selected = selected( $item, tinyTOC::$options[$args['option_id']], false );
        echo "<option value='$item' $selected>$item</option>";
      }
    }
    echo "</select>";
    if ( isset( $args['description'] ) ) {
      echo '<p class="description">'.$args['description'].'</p>';
    }
  }

  // CHECKBOX - Name: checkbox
  public static function checkbox($args) {
    $checked = checked( tinyTOC::$options[$args['option_id']], true, false );
    echo "<input ".$checked." id='{$args['option_id']}' name='".self::$id."[{$args['option_id']}]' type='checkbox' value=\"1\"/>";
    if ( isset( $args['description'] ) ) {
      echo '<p class="description">'.$args['description'].'</p>';
    }
  }

  // TEXTAREA - Name: textarea - Arguments: rows:int=4 cols:int=20
  public static function textarea($args) {
    if (!$args['rows']) $args['rows']=4;
    if (!$args['cols']) $args['cols']=20;
    echo "<textarea id='{$args['option_id']}' name='".self::$id."[{$args['option_id']}]' rows='{$args['rows']}' cols='{$args['cols']}' type='textarea'>".tinyTOC::$options[$args['option_id']]."</textarea>";
    if ( isset( $args['description'] ) ) {
      echo '<p class="description">'.$args['description'].'</p>';
    }
  }

  // TEXTBOX - Name: text - Arguments: size:int=40
  public static function text($args) {
    if (!$args['size']) $args['size']=40;
    echo "<input id='{$args['option_id']}' name='".self::$id."[{$args['option_id']}]' size='{$args['size']}' type='text' value='".tinyTOC::$options[$args['option_id']]."' />";
    if ( isset( $args['description'] ) ) {
      echo '<p class="description">'.$args['description'].'</p>';
    }
  }

  // NUMBER TEXTBOX - Name: text - Arguments: size:int=40
  public static function number($args) {
    $options = '';
    if ( is_array($args) ) {
      foreach ($args as $key => $value) {
        if ( in_array( $key, array( 'option_id' ) ) ) {
          continue;
        }
        $options .= " {$key}=\"{$value}\"";
      }
    } 
    echo "<input id='{$args['option_id']}' name='".self::$id."[{$args['option_id']}]' type='number' value='".tinyTOC::$options[$args['option_id']]."'{$options}/>";
    if ( isset( $args['description'] ) ) {
      echo '<p class="description">'.$args['description'].'</p>';
    }
  }

  // PASSWORD-TEXTBOX - Name: password - Arguments: size:int=40
  public static function password($args) {
    if (!$args['size']) $args['size']=40;
    echo "<input id='{$args['option_id']}' name='".self::$id."[{$args['option_id']}]' size='{$args['size']}' type='password' value='".tinyTOC::$options[$args['option_id']]."' />";
    if ( isset( $args['description'] ) ) {
      echo '<p class="description">'.$args['description'].'</p>';
    }
  }

  // RADIO-BUTTON - Name: plugin_options[option_set1]
  public static function radio($args) {
    $items = $args['values'];
    if (self::is_assoc($items)) {
      foreach($items as $key=>$item) {
        $checked = checked( $key, tinyTOC::$options[$args['option_id']], false );
        echo "<label><input ".$checked." value='$key' name='".self::$id."[{$args['option_id']}]' type='radio' /> $item</label><br />";
      }
    } else {
      foreach($items as $item) {
        $checked = checked( $item, tinyTOC::$options[$args['option_id']], false );
        echo "<label><input ".$checked." value='$item' name='".self::$id."[{$args['option_id']}]' type='radio' /> $item</label><br />";
      }
    }
    if ( isset( $args['description'] ) ) {
      echo '<p class="description">'.$args['description'].'</p>';
    }
  }
  // checklist - Name: plugin_options[option_set1]
  public static function checklist($args) {
    $items = $args['values'];
    if (self::is_assoc($items)) {
      foreach($items as $key=>$item) {
        $checked = checked( in_array( $key, tinyTOC::$options[$args['option_id']] ), true, false );
        echo "<label><input ".$checked." value='$key' name='".self::$id."[{$args['option_id']}][]' type='checkbox' /> $item</label><br />";
      }
    } else {
      foreach($items as $item) {
        $checked = checked( in_array( $item, tinyTOC::$options[$args['option_id']] ), true, false );
        echo "<label><input ".$checked." value='$item' name='".self::$id."[{$args['option_id']}][]' type='checkbox' /> $item</label><br />";
      }
    }
    if ( isset( $args['description'] ) ) {
      echo '<p class="description">'.$args['description'].'</p>';
    }
  }

  // Display the admin options page
  public static function page() {
    if (!current_user_can(self::$role)) {
        wp_die( __( 'You do not have sufficient permissions to access this page.', 'tinyTOC' ) );
    }
  ?>
    <div class="wrap">
      <div class="icon32" id="icon-page"><br></div>
      <h2><?php echo self::$title; ?></h2>
      <?php echo self::$description; ?>
      <form action="options.php" method="post">
      <?php settings_fields(self::$id); ?>
      <?php do_settings_sections(self::$file); ?>
      <?php submit_button( __( 'Save Changes', 'tinyTOC' ) , 'primary' ); ?>
      </form>
    </div>
  <?php
  }

  // Validate user data for some/all of your input fields
  public static function validate($input) {
    // sanitize count
    return $input; // return sanitized input
  }

}

?>