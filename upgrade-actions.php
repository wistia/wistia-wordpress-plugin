<?php
function wistia_wordpress_update_anti_mangler_default() {
  // This is required for `is_plugin_active`.
  include_once(ABSPATH . 'wp-admin/includes/plugin.php');

  // If wistia_wordpress_anti_mangler isn't defined on upgrade/install, let's
  // define it. If you're upgrading from an older version where the option
  // didn't exist, default it to ON so nothing changes. New installs, however,
  // should default to OFF.
  $wistia_wordpress_anti_mangler = get_wistia_wordpress_option('anti_mangler');
  $plugin_already_activated = is_plugin_active(wistia_wordpress_plugin_file());
  if (empty($wistia_wordpress_anti_mangler)) {
    if ($plugin_already_activated) {
      set_wistia_wordpress_option('anti_mangler', 'on');
    } else {
      set_wistia_wordpress_option('anti_mangler', 'off');
    }
  }

  // Record version in database for future upgrade path pivoting.
  $current_plugin_data = get_plugin_data(wistia_wordpress_plugin_file());
  $current_wistia_wordpress_version = $current_plugin_data['Version'];
  set_wistia_wordpress_option('version', $current_wistia_wordpress_version);
}

// Run when the user manually updates the plugin in the admin.
register_activation_hook(
  wistia_wordpress_plugin_file(),
  'wistia_wordpress_update_anti_mangler_default'
);
?>
