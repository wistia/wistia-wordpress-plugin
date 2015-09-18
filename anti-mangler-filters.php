<?php
global $wistia_anti_mangler;
$wistia_anti_mangler = new WistiaAntiMangler();

function wistia_extract_embeds($text) {
  global $wistia_anti_mangler;
  return $wistia_anti_mangler->extract_embeds($text);
}

function wistia_insert_embeds($text) {
  global $wistia_anti_mangler;
  return $wistia_anti_mangler->insert_embeds($text);
}

function wistia_insert_embeds_for_editor($text) {
  global $wistia_anti_mangler;
  return $wistia_anti_mangler->insert_embeds_for_editor($text);
}

function wistia_add_scripts_if_necessary($text) {
  global $wistia_anti_mangler;
  return $wistia_anti_mangler->add_scripts_if_necessary($text);
}

/**
 * Extends the allowed HTML elements in the TinyMCE editor.
 *
 * @link http://bit.ly/11hhyG3
 *
 * @param array $init Initial associative array of allowed elements.
 *
 * @return array
 */
function add_valid_tiny_mce_elements($init) {
	$elements = 'div[*],iframe[*],script[*],object[*],embed[*],a[*],noscript[*]';

	/*
	Add to extended_valid_elements if it already exists,
	else ours becomes the only allowed elements.
	*/
	if (isset($init['extended_valid_elements']) && is_string($init['extended_valid_elements']))
    $init['extended_valid_elements'] = $init['extended_valid_elements'] . ',' . $elements;
	else
		$init['extended_valid_elements'] = $elements;

	return $init;
}

// Only use the mangler if it's turned on or not set. The value could be
// "empty" if the plugin was updated via git/svn/ftp, without going through the
// UI update path.
$wistia_wordpress_anti_mangler = get_wistia_wordpress_option('anti_mangler');
if (empty($wistia_wordpress_anti_mangler) || $wistia_wordpress_anti_mangler == 'on') {
  add_filter('content_save_pre', 'wistia_extract_embeds', 2);
  add_filter('content_save_pre', 'wistia_insert_embeds', 1001);
  add_filter('the_content', 'wistia_extract_embeds', 2);
  add_filter('the_content', 'wistia_insert_embeds', 1001);
  add_filter('the_content', 'wistia_add_scripts_if_necessary', 1002);
  add_filter('the_editor_content', 'wistia_extract_embeds', 2);
  add_filter('the_editor_content', 'wistia_insert_embeds_for_editor', 1001);
  add_filter('tiny_mce_before_init', 'add_valid_tiny_mce_elements' );
}
?>
