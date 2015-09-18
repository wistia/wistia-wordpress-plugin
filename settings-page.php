<?php
add_action( 'admin_menu', 'wistia_wordpress_add_admin_menu' );
add_action( 'admin_init', 'wistia_wordpress_settings_init' );

function wistia_wordpress_add_admin_menu() {
  add_options_page(
    'Wistia WordPress', 'Wistia WordPress', 'manage_options',
    'wistia_wordpress', 'wistia_wordpress_options_page'
  );
}

function wistia_wordpress_settings_init(  ) {
  register_setting('pluginPage', 'wistia_wordpress_settings');

  add_settings_section(
    'wistia_wordpress_pluginPage_section',
    __( '', 'wordpress' ),
    'wistia_wordpress_settings_section_callback',
    'pluginPage'
  );

  add_settings_field(
    'anti_mangler',
    __('Include Legacy Anti-Mangler', 'wordpress'),
    'wistia_wordpress_anti_mangler_render',
    'pluginPage',
    'wistia_wordpress_pluginPage_section'
  );
}

function wistia_wordpress_anti_mangler_render() {
  $wistia_wordpress_anti_mangler = get_wistia_wordpress_option('anti_mangler');
  $on = empty($wistia_wordpress_anti_mangler) ||
    $wistia_wordpress_anti_mangler == 'on';
  ?>
  <label>
    <input type='radio' name='wistia_wordpress_settings[anti_mangler]' <?php echo $on ? "checked='checked'" : ''; ?> value='on'>
    On
  </label>
  <label>
    <input type='radio' name='wistia_wordpress_settings[anti_mangler]' <?php echo $on ? '' : 'checked="checked"'; ?> value='off'>
    Off
  </label>
  <?php
}

function wistia_wordpress_settings_section_callback() {
  echo __('<p>The Wistia WordPress plugin enables oEmbeds to work in WordPress.</p> <p>There is also some legacy functionality which allows raw HTML, which we recommend turning OFF. It\'s better to use oEmbed!</p>', 'wordpress');
}

function wistia_wordpress_options_page() {
  ?>
  <form action='options.php' method='post'>
    <h2>Wistia WordPress</h2>
    <?php
    settings_fields('pluginPage');
    do_settings_sections('pluginPage');
    submit_button();
    ?>
  </form>
  <?php
}

// Link to Settings page from the Plugins page next to Deactivate | Edit.
$plugin = plugin_basename(wistia_wordpress_plugin_file());
function wistia_wordpress_add_settings_link($links) {
  $settings_link = '<a href="options-general.php?page=wistia_wordpress">' .
    __('Settings') . '</a>';
  array_push($links, $settings_link);
  return $links;
}
add_filter("plugin_action_links_$plugin", 'wistia_wordpress_add_settings_link');
?>
