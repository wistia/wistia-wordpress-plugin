<?php
class WistiaAntiMangler {

  var $unfiltered_substrings = array();


  /**
   * Identify all wistia embeds, save their raw html, and replace them 
   * with a random alphanumeric placeholder. This is applied as a filter 
   * before any other filters.
   *
   * We wrap the placeholder in a div to prevent wpautop from surrounding 
   * our text.
   */
  function extract_embeds($text) {
    $ranges = $this->all_ranges_merged($text);
    $index_mod = 0;
    foreach ($ranges as $range) {
      $substring_uuid = '<div>' . uniqid('__WISTIA__') . '</div>';
      $substring_length = $range[1] - $range[0] + 1;
      $index_shift = $substring_length - strlen($substring_uuid);
      $this->unfiltered_substrings[$substring_uuid] = substr($text, $range[0] - $index_mod, $range[1] - $range[0] + 1);
      $text = substr_replace($text, $substring_uuid, $range[0] - $index_mod, $range[1] - $range[0] + 1);
      $index_mod += $index_shift;
    }
    return $text;
  }


  /**
   * Given all the placeholders created by extract_embeds, insert 
   * the saved raw html back into the text.
   */
  function insert_embeds($text) {
    foreach ($this->unfiltered_substrings as $substring_uuid => $unfiltered_substring) {
      $text = str_replace($substring_uuid, $unfiltered_substring, $text);
    }
    return $text;
  }


  /**
   * The same as insert_embeds, but with some escaping for the editor.
   */
  function insert_embeds_for_editor($text) {
    foreach ($this->unfiltered_substrings as $substring_uuid => $unfiltered_substring) {
      $text = str_replace(htmlspecialchars($substring_uuid, ENT_NOQUOTES), htmlspecialchars($unfiltered_substring, ENT_NOQUOTES), $text);
    }
    return $text;
  }


  /**
   * Add any required external scripts to this text to make it work properly.
   */
  function add_scripts_if_necessary($text) {
    $scripts_to_prepend = $this->scripts_to_prepend($text);
    $scripts_to_append = $this->scripts_to_append($text);
    $result = $text;
    if (count($scripts_to_prepend) > 0) {
      $text = $this->concat_script_tag($scripts_to_prepend) . "\n" . $text;
    }
    if (count($scripts_to_append) > 0) {
      $text = $text . "\n" . $this->concat_script_tag($scripts_to_append);
    }
    return $text;
  }


  /**
   * Match a regex and return the HTML tag it belongs to.
   * If no match is found or the HTML is unbalanced, return null.
   *
   * @param   regexp  $needle     Regex to match.
   * @param   string  $haystack   Html to search.
   * @param   int     $offset     Optional offset to start matching from. Defaults to 0.
   *
   * @return  null|array  An array representing a range. Elem 0 is the start, elem 1 is the end.
   */
  function whole_tag_range_for_match( $needle, $haystack, $offset = 0 ) {
    // find a handle to start the search
    $matches = array();
    preg_match($needle, $haystack, $matches, PREG_OFFSET_CAPTURE, $offset);
    if (count($matches) == 0) {
      return null;
    }
    $handle_index = $matches[0][1];

    // find the opening carrot.
    $start_carrot_index = -1;
    for ($i = $handle_index; $i >= 0; $i--) {
      $char = $haystack[$i];
      $next_char = $haystack[$i + 1];
      if ($char == '<' && ctype_alnum($next_char)) {
        $start_carrot_index = $i;
        break;
      }
    }

    // get the tag name for validation later
    preg_match('/[^\s>]+/', $haystack, $matches, null, $start_carrot_index + 1);
    $opening_tag_name = $matches[0];

    // find the closing carrot. 
    $end_carrot_index = -1;
    for ($i = $start_carrot_index + 1; $i < strlen($haystack); $i++) {
      $char = $haystack[$i];
      if ($char == '>') {
        $end_carrot_index = $i;
        break;
      }
    }

    // find the closing tag. fail if tags don't match or not balanced.
    $opened_tag_count = 1;
    for ($i = $end_carrot_index + 1; $i < strlen($haystack) - 1; $i++) {
      $char = $haystack[$i];
      $next_char = $haystack[$i + 1];
      $on_opening_tag = $char == '<' && ctype_alpha($next_char);
      $on_closing_tag = ($char == '<' && $next_char == '/') || ($char == '/' && $next_char == '>');
      if ($opened_tag_count === 1 && $on_closing_tag) {
        preg_match('/[^\s>]+/', $haystack, $matches, null, $i + 2);
        $closing_tag_name = $matches[0];
        if (strtolower($opening_tag_name) == strtolower($closing_tag_name)) {
          for ($j = $i + 1; $j < strlen($haystack); $j++) {
            if ($haystack[$j] == '>') {
              return array($start_carrot_index, $j);
            }
          }
        }
        return null;
      }
      if ($on_opening_tag) {
        $opened_tag_count++;
      } else if ($on_closing_tag) {
        $opened_tag_count--;
      }
    }
    return null;
  }

  /**
   * Get all tag ranges, without intersection, that match a regex in a string.
   *
   * @param  regexp  $needle
   * @param  string  $haystack
   *
   * @return  array  An array of ranges, as defined in whole_tag_range_for_match.
   */
  function all_tag_ranges_for_match( $needle, $haystack ) {
    $result = array();
    $offset = 0;
    while ($range = $this->whole_tag_range_for_match($needle, $haystack, $offset)) {
      array_push($result, $range);
      $offset = $range[1] + 1;
    }
    return $result;
  }

  /**
   * Basically a substr function, but use a range instead of offset/length.
   */
  function substr_for_range($range, $haystack) {
    return substr($haystack, $range[0], $range[1] - $range[0] + 1);
  }

  /**
   * Given an array of ranges, return an array of substrs.
   */
  function substrs_for_ranges( $ranges, $haystack ) {
    $result = array();
    foreach ($ranges as $range) {
      array_push($result, $this->substr_for_range($range, $haystack));
    }
    return $result;
  }

  /**
   * Given two ranges, find the substr of text between them.
   */
  function substr_between_ranges($range1, $range2, $haystack) {
    return substr($haystack, $range1[1] + 1, $range2[0] - $range1[1] - 1);
  }

  /**
   * Used to sort an array of ranges, as given by all_tag_ranges_for_match.
   * Ranges can be out of order if multiple arrays have been merged.
   */
  function sort_ranges($a, $b) {
    return $a[0] - $b[0];
  }

  /**
   * Given an array of ranges, join any that are consecutive and separated only 
   * by whitespace. This lets us turn partial embed matching into full embed matching, 
   * unless the user intentionally adds elements in between.
   *
   * @param   array   $ranges     An array of ranges, as defined in whole_tag_range_for_match.
   * @param   string  $haystack   The text from which the ranges were derived.
   *
   * @return  array
   */
  function merge_ranges_only_separated_by_whitespace( $ranges, $haystack ) {
    if (count($ranges) == 0) {
      return array();
    }
    $last_i = 0;
    $result = array();
    foreach ($ranges as $range) {
      array_push($result, array($range[0], $range[1]));
    }
    usort($result, array($this, 'sort_ranges'));
    for ($i = 1; $i < count($result); $i++) {
      $str_between = $this->substr_between_ranges($result[$last_i], $result[$i], $haystack);
      if ($str_between == '' || ctype_space($str_between)) {
        $result[$last_i][1] = $result[$i][1];
        $result[$i] = null;
      } else {
        $last_i = $i;
      }
    }
    $result_no_nulls = array();
    foreach($result as $elem) {
      if ($elem != null) {
        array_push($result_no_nulls, $elem);
      }
    }
    return $result_no_nulls;
  }

  /**
   * Identify wistia iframes by name="wistia_embed" or name="wistia_playlist".
   * 
   * @param  string  $haystack
   *
   * @return  array
   */
  function all_iframe_ranges( $haystack ) {
    $result = array();
    $ranges = $this->all_tag_ranges_for_match('/name=\"wistia_embed\"|name=\"wistia_playlist\"/', $haystack);
    foreach ($ranges as $range) {
      $html_in_range = $this->substr_for_range($range, $haystack);
      if (preg_match('/^<iframe/', $html_in_range)) {
        array_push($result, $range);
      }
    }
    return $result;
  }

  /**
   * Identify external scripts by fast.wistia.com, inline scripts by Wistia.embed for Wistia.plugin.
   * 
   * @param  string  $haystack
   *
   * @return  array
   */
  function all_script_ranges( $haystack ) {
    $result = array();
    $ranges = $this->all_tag_ranges_for_match('/src=[\'\"]https?:\/\/fast\.wistia\.com|Wistia\.embed|Wistia\.plugin/', $haystack);
    foreach ($ranges as $range) {
      $html_in_range = $this->substr_for_range($range, $haystack);
      if (preg_match('/^<script/', $html_in_range)) {
        array_push($result, $range);
      }
    }
    return $result;
  }

  /**
   * Identify external scripts by fast.wistia.com.
   * 
   * @param  string  $haystack
   *
   * @return  array
   */
  function all_external_script_ranges( $haystack ) {
    $result = array();
    $ranges = $this->all_tag_ranges_for_match('/src=[\'\"]https?:\/\/fast\.wistia\.com/', $haystack);
    foreach ($ranges as $range) {
      $html_in_range = $this->substr_for_range($range, $haystack);
      if (preg_match('/^<script/', $html_in_range)) {
        array_push($result, $range);
      }
    }
    return $result;
  }

  /**
   * Identify API embed containers by class="wistia_embed" or class="wistia_playlist" on a div.
   * 
   * @param  string  $haystack
   *
   * @return  array
   */
  function all_api_embed_ranges( $haystack ) {
    $result = array();
    $ranges = $this->all_tag_ranges_for_match('/class=\"wistia_embed|class=\"wistia_playlist/', $haystack);
    foreach ($ranges as $range) {
      $html_in_range = substr($haystack, $range[0], $range[1] - $range[0]);
      if (preg_match('/^<div/', $html_in_range)) {
        array_push($result, $range);
      }
    }
    return $result;
  }

  /**
   * Identify popover links by class="wistia-popover on an <a>.
   * 
   * @param  string  $haystack
   *
   * @return  array
   */
  function all_popover_ranges( $haystack ) {
    $result = array();
    $ranges = $this->all_tag_ranges_for_match('/class=\"wistia-popover/', $haystack);
    foreach ($ranges as $range) {
      $html_in_range = substr($haystack, $range[0], $range[1] - $range[0]);
      if (preg_match('/^<a/', $html_in_range)) {
        array_push($result, $range);
      }
    }
    return $result;
  }


  /**
   * Identify noscript ranges by id=".*?_noscript_transcript".
   * 
   * @param  string  $haystack
   *
   * @return  array
   */
  function all_noscript_ranges( $haystack ) {
    $result = array();
    $ranges = $this->all_tag_ranges_for_match('/id=\"wistia_.*?_noscript_transcript"/', $haystack);
    foreach ($ranges as $range) {
      $html_in_range = substr($haystack, $range[0], $range[1] - $range[0]);
      if (preg_match('/^<noscript/', $html_in_range)) {
        array_push($result, $range);
      }
    }
    return $result;
  }

  /**
   * Return all ranges for things that look like wistia embeds.
   * 
   * @param  string  $haystack
   *
   * @return  array
   */
  function all_ranges( $haystack ) {
    return array_merge($this->all_iframe_ranges($haystack),
      $this->all_script_ranges($haystack),
      $this->all_api_embed_ranges($haystack),
      $this->all_popover_ranges($haystack),
      $this->all_noscript_ranges($haystack));
  }

  /**
   * Return all ranges for things that look like wistia embeds, in the order 
   * that they appear in the text.
   * 
   * @param  string  $haystack
   *
   * @return  array
   */
  function all_ranges_ordered( $haystack ) {
    $result = $this->all_ranges($haystack);
    usort($result, array($this, 'sort_ranges'));
    return $result;
  }

  /**
   * Return all ranges of wistia embeds, consolidated.
   * 
   * @param  string  $haystack
   *
   * @return  array
   */
  function all_ranges_merged( $haystack ) {
    return $this->merge_ranges_only_separated_by_whitespace($this->all_ranges($haystack), $haystack);
  }


  /**
   * Find any wistia scripts included in the text and save their names. e.g.
   * array('E-v1', 'socialbar-v1', 'postRoll-v1', 'popover-v1')
   * 
   * @param  string  $haystack
   *
   * @return  array
   */
  function find_existing_scripts($text) {
    $existing_scripts = array();
    $script_ranges = $this->all_external_script_ranges($text);
    foreach ($script_ranges as $range) {
      $script_text = $this->substr_for_range($range, $text);
      if ($matches = preg_match('/src=["\'](?:https?)?\/\/fast\.wistia\.com\/static\/concat\/(.*?)\.js/', $script_text)) {
        array_push($existing_scripts, explode(',', urldecode($matches[1])));
      } else if ($matches = preg_match('/src=["\'](?:https?)?\/\/fast\.wistia\.com\/static\/(.*?-v\d)\.js/', $script_text)) {
        array_push($existing_scripts, explode(',', urldecode($matches[1])));
      }
    }
    return array_unique($existing_scripts);
  }

  /**
   * Given a function call, find the whole function range from open to close. Note 
   * that this probably fails if an anonymous function is passed as a parameter, 
   * but our embeds never accept that form.
   * 
   * @param  string  $func_name  The name of the function to match.
   * @param  text  The text to search in.
   * @param  offset  Optional, the offset to start searching from.
   *
   * @return  array
   */
  function find_whole_function_call_range( $func_name, $text, $offset = 0 ) {
    $regexp = '/' . str_replace('.', '\.', $func_name) . '\s*\(/';
    if (preg_match($regexp, $text, $matches, PREG_OFFSET_CAPTURE, $offset)) {
      $start_index = $matches[0][1];
      if (preg_match('/\);/', $text, $matches, PREG_OFFSET_CAPTURE, $start_index)) {
        $end_index = $matches[0][1] + strlen($matches[0][0]);
        return array($start_index, $end_index);
      }
    }
    return null;
  }

  /**
   * Given a function call, find the whole function from open to close. Note 
   * that this probably fails if an anonymous function is passed as a parameter, 
   * but our embeds never accept that form.
   * 
   * @param  string  $func_name  The name of the function to match.
   * @param  text  The text to search in.
   * @param  offset  Optional, the offset to start searching from.
   *
   * @return  string
   */
  function find_whole_function_call( $func_name, $text, $offset = 0 ) {
    $range = $this->find_whole_function_call_range($func_name, $text, $offset);
    if ($range != null) {
      return $this->substr_for_range($range, $text);
    } else {
      return null;
    }
  }

  /**
   * Determine what external scripts are required based purely on the contents 
   * of the inline scripts.
   * 
   * @param  string  $text
   *
   * @return  array
   */
  function infer_required_scripts($text) {
    $required_scripts = array(array(), array());
    $script_ranges = $this->all_script_ranges($text);
    foreach ($script_ranges as $range) {
      $script_text = $this->substr_for_range($range, $text);
      if (!preg_match('/src=["\']/', $script_text)) {
        if ($func_text = $this->find_whole_function_call('Wistia.embed', $script_text)) {
          if (preg_match('/["\']?version["\']?:\s*["\'](v\d+)["\']/', $func_text, $match)) {
            array_push($required_scripts[0], 'E-' . $match[1]);
          } else {
            array_push($required_scripts[0], 'E-v1');
          }
        }
        if ($func_text = $this->find_whole_function_call('Wistia.playlist', $script_text)) {
          if (preg_match('/["\']?version["\']?:\s*["\'](v\d+)["\']/', $func_text, $match)) {
            $version = $match[1];
            array_push($required_scripts[0], 'E-' . $version);
            array_push($required_scripts[0], 'playlist-' . $version);
          }
          if (preg_match('/["\']?theme["\']?:\s*["\'](\w+)["\']/', $func_text, $match)) {
            $theme = $match[1];
            array_push($required_scripts[0], 'playlist-' . $version . '-' . $theme);
          }
        }
        if ($func_text = $this->find_whole_function_call('Wistia.plugin.socialbar', $script_text)) {
          if (preg_match('/["\']?version["\']?:\s*["\'](v\d+)["\']/', $func_text, $match)) {
            array_push($required_scripts[0], 'socialbar-' . $match[1]);
          }
        }
        if ($func_text = $this->find_whole_function_call('Wistia.plugin.postRoll', $script_text)) {
          if (preg_match('/["\']?version["\']?:\s*["\'](v\d+)["\']/', $func_text, $match)) {
            array_push($required_scripts[0], 'postRoll-' . $match[1]);
          }
        }
        if ($func_text = $this->find_whole_function_call('Wistia.plugin.transcript', $script_text)) {
          if (preg_match('/["\']?version["\']?:\s*["\'](v\d+)["\']/', $func_text, $match)) {
            array_push($required_scripts[0], 'transcript-' . $match[1]);
          }
        }
        if ($func_text = $this->find_whole_function_call('Wistia.plugin.requireEmail', $script_text)) {
          if (preg_match('/["\']?version["\']?:\s*["\'](v\d+)["\']/', $func_text, $match)) {
            array_push($required_scripts[0], 'requireEmail-' . $match[1]);
          }
        }
      }
    }
    $popover_ranges = $this->all_popover_ranges($text);
    foreach ($popover_ranges as $range) {
      $popover_text = $this->substr_for_range($range, $text);
      if (preg_match('/popover=(v\d+)/', $popover_text, $match)) {
        array_push($required_scripts[1], 'popover-' . $match[1]);
      }
    }
    $iframe_ranges = $this->all_iframe_ranges($text);
    if (count($iframe_ranges) > 0) {
      array_push($required_scripts[1], 'iframe-api-v1');
    }
    return array(array_unique($required_scripts[0]), $required_scripts[1]);
  }


  /**
   * Determine what scripts need to be prepended to the text for it to 
   * function properly.
   * 
   * @param  string  $text
   *
   * @return  array
   */
  function scripts_to_prepend($text) {
    $existing_scripts = $this->find_existing_scripts($text);
    $required_scripts = $this->infer_required_scripts($text);
    $required_scripts = $required_scripts[0];
    $scripts_to_prepend = array();
    foreach ($required_scripts as $script) {
      if (!array_search($script, $existing_scripts)) {
        array_push($scripts_to_prepend, $script);
      }
    }
    return $scripts_to_prepend;
  }

  /**
   * Determine what scripts need to be appended to the text for it to 
   * function properly.
   * 
   * @param  string  $text
   *
   * @return  array
   */
  function scripts_to_append($text) {
    $existing_scripts = $this->find_existing_scripts($text);
    $required_scripts = $this->infer_required_scripts($text);
    $required_scripts = $required_scripts[1];
    $scripts_to_append = array();
    foreach ($required_scripts as $script) {
      if (!array_search($script, $existing_scripts)) {
        array_push($scripts_to_append, $script);
      }
    }
    return $scripts_to_append;
  }

  /**
   * Given an array of scripts, output a single script tag to include all of 
   * them.
   * 
   * @param  array  $scripts
   *
   * @return  array
   */
  function concat_script_tag($scripts) {
    return '<script charset="ISO-8859-1" src="http' . ($_SERVER['https'] == 'on' ? 's' : '') . '://fast.wistia.com/static/concat/' . implode($scripts, '%2C') . '.js"></script>';
  }
}
?>
