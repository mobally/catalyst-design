<?php

function bps_escaped_form_data47 ()
{
	list ($form, $location) = bps_template_args ();

	$meta = bps_meta ($form);
	$fields = bps_parse_request (bps_get_request ('form', $form));

	$F = new stdClass;
	$F->id = $form;
	$F->location = $location;
	$F->header = bps_wpml ($form, '-', 'header', $meta['header']);
	$F->toggle = ($meta['toggle'] == 'Enabled');
	$F->toggle_text = bps_wpml ($form, '-', 'toggle form', $meta['button']);

	$dirs = bps_directories ();
	$F->action = $location == 'directory'?
		parse_url ($_SERVER['REQUEST_URI'], PHP_URL_PATH):
		$dirs[bps_wpml_id ($meta['action'])]->link;

	if (defined ('DOING_AJAX'))
		$F->action = parse_url ($_SERVER['HTTP_REFERER'], PHP_URL_PATH);

	$F->method = $meta['method'];
	$F->fields = array ();

	foreach ($meta['field_code'] as $k => $id)
	{
		if (empty ($fields[$id]))  continue;

		$f = $fields[$id];
		$mode = $meta['field_mode'][$k];
		if (!bps_Fields::set_display ($f, $mode))  continue;

		$f->label = $f->name;
		$custom_label = bps_wpml ($form, $id, 'label', $meta['field_label'][$k]);
		if (!empty ($custom_label))
		{
			$f->label = $custom_label;
			$F->fields[] = bps_set_hidden_field ($f->code. '_label', $f->label);
		}

		$custom_desc = bps_wpml ($form, $id, 'comment', $meta['field_desc'][$k]);
		if ($custom_desc == '-')
			$f->description = '';
		else if (!empty ($custom_desc))
			$f->description = $custom_desc;

		if (!isset ($f->filter))
		{
			$f->min = $f->max = $f->value = '';
			$f->values = array ();
			if ($f->display == 'distance')
				$f->value['distance'] = $f->value['units'] = $f->value['location'] = $f->value['lat'] = $f->value['lng'] = '';
		}
		else
		{
			$f->min = isset ($f->value['min'])? $f->value['min']: '';
			$f->max = isset ($f->value['max'])? $f->value['max']: '';
			$f->values = (array)$f->value;
		}

		do_action ('bps_field_before_search_form', $f);

		if ($mode != '')  $f->code .= '_'. $mode;
		$f->unique_id = bps_unique_id ($f->code);

		$F->fields[] = $f;
	}

	$F->fields[] = bps_set_hidden_field (BPS_FORM, $form);

	do_action ('bps_before_search_form', $F);

	$F->toggle_text = esc_attr ($F->toggle_text);
	foreach ($F->fields as $f)
	{
		if (!is_array ($f->value))  $f->value = esc_attr (stripslashes ($f->value));
		if ($f->display == 'hidden')  continue;

		$f->label = esc_attr ($f->label);
		$f->description = esc_attr ($f->description);
		foreach ($f->values as $k => $value)  $f->values[$k] = esc_attr (stripslashes ($value));
		$options = array ();
		foreach ($f->options as $key => $label)  $options[esc_attr ($key)] = esc_attr ($label);
		$f->options = $options;
	}

	return $F;
}

function bps_escaped_filters_data47 ()
{
	list ($request, $full) = bps_template_args ();

	$F = new stdClass;
	$action = parse_url ($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$action = add_query_arg (BPS_FORM, 'clear', $action);
	$F->action = $full? esc_url ($action): '';
	$F->fields = array ();

	$fields = bps_parse_request ($request);
	foreach ($fields as $f)
	{
		if (!isset ($f->filter))  continue;
		if (!bps_Fields::set_display ($f, $f->filter))  continue;

		if (empty ($f->label))  $f->label = $f->name;

		$f->min = isset ($f->value['min'])? $f->value['min']: '';
		$f->max = isset ($f->value['max'])? $f->value['max']: '';
		$f->values = (array)$f->value;

		do_action ('bps_field_before_filters', $f);
		$F->fields[] = $f;
	}

	do_action ('bps_before_filters', $F);
	usort ($F->fields, 'bps_sort_fields');

	foreach ($F->fields as $f)
	{
		$f->label = esc_attr ($f->label);
		if (!is_array ($f->value))  $f->value = esc_attr (stripslashes ($f->value));
		foreach ($f->values as $k => $value)  $f->values[$k] = stripslashes ($value);

		foreach ($f->options as $key => $label)  $f->options[$key] = esc_attr ($label);
	}

	return $F;
}

function bps_print_filter ($f)
{
	if (!empty ($f->options))
	{
		$values = array ();
		foreach ($f->options as $key => $label)
			if (in_array ($key, $f->values))  $values[] = $label;
	}

	switch ($f->filter)
	{
	case 'range':
	case 'age_range':
		if (!isset ($f->value['max']))
			return sprintf (esc_html__('min: %1$s', 'bp-profile-search'), $f->value['min']);
		if (!isset ($f->value['min']))
			return sprintf (esc_html__('max: %1$s', 'bp-profile-search'), $f->value['max']);
		return sprintf (esc_html__('min: %1$s, max: %2$s', 'bp-profile-search'), $f->value['min'], $f->value['max']);

	case '':
		if (isset ($values))
			return sprintf (esc_html__('is: %1$s', 'bp-profile-search'), $values[0]);
		return sprintf (esc_html__('is: %1$s', 'bp-profile-search'), $f->value);

	case 'contains':
		return sprintf (esc_html__('contains: %1$s', 'bp-profile-search'), $f->value);

	case 'like':
		return sprintf (esc_html__('is like: %1$s', 'bp-profile-search'), $f->value);

	case 'one_of':
		if (count ($values) == 1)
			return sprintf (esc_html__('is: %1$s', 'bp-profile-search'), $values[0]);
		return sprintf (esc_html__('is one of: %1$s', 'bp-profile-search'), implode (', ', $values));

	case 'match_any':
		if (count ($values) == 1)
			return sprintf (esc_html__('match: %1$s', 'bp-profile-search'), $values[0]);
		return sprintf (esc_html__('match any: %1$s', 'bp-profile-search'), implode (', ', $values));

	case 'match_all':
		if (count ($values) == 1)
			return sprintf (esc_html__('match: %1$s', 'bp-profile-search'), $values[0]);
		return sprintf (esc_html__('match all: %1$s', 'bp-profile-search'), implode (', ', $values));

	case 'distance':
		if ($f->value['units'] == 'km')
			return sprintf (esc_html__('is within %1$s km of: %2$s', 'bp-profile-search'), $f->value['distance'], $f->value['location']);
		return sprintf (esc_html__('is within %1$s miles of: %2$s', 'bp-profile-search'), $f->value['distance'], $f->value['location']);

	default:
		return "BP Profile Search: undefined filter <em>$f->filter</em>";
	}
}

function bps_autocomplete_script ($f)
{
	wp_enqueue_script ($f->script_handle);
	$autocomplete_options = apply_filters ('bps_autocomplete_options', "{types: ['geocode']}", $f);
	$geolocation_options = apply_filters ('bps_geolocation_options', "{timeout: 5000}", $f);
?>
	<input type="hidden" id="Lat_<?php echo $f->unique_id; ?>"
		name="<?php echo $f->code. '[lat]'; ?>"
		value="<?php echo $f->value['lat']; ?>">
	<input type="hidden" id="Lng_<?php echo $f->unique_id; ?>"
		name="<?php echo $f->code. '[lng]'; ?>"
		value="<?php echo $f->value['lng']; ?>">

	<script type="text/javascript">
		function bps_<?php echo $f->unique_id; ?>() {
			var input = document.getElementById('<?php echo $f->unique_id; ?>');
			var options = <?php echo $autocomplete_options; ?>;
			var autocomplete = new google.maps.places.Autocomplete(input, options);
			google.maps.event.addListener(autocomplete, 'place_changed', function() {
				var place = autocomplete.getPlace();
				document.getElementById('Lat_<?php echo $f->unique_id; ?>').value = place.geometry.location.lat();
				document.getElementById('Lng_<?php echo $f->unique_id; ?>').value = place.geometry.location.lng();
			});
		}
		jQuery(document).ready (bps_<?php echo $f->unique_id; ?>);

		function bps_locate_<?php echo $f->unique_id; ?>() {
			if (navigator.geolocation) {
				var options = <?php echo $geolocation_options; ?>;
				navigator.geolocation.getCurrentPosition(function(position) {
					document.getElementById('Lat_<?php echo $f->unique_id; ?>').value = position.coords.latitude;
					document.getElementById('Lng_<?php echo $f->unique_id; ?>').value = position.coords.longitude;
					bps_address_<?php echo $f->unique_id; ?>(position);
				}, function(error) {
					alert('ERROR ' + error.code + ': ' + error.message);
				}, options);
			} else {
				alert('ERROR: Geolocation is not supported by this browser');
			}
		}
		jQuery('#Btn_<?php echo $f->unique_id; ?>').click (bps_locate_<?php echo $f->unique_id; ?>);

		function bps_address_<?php echo $f->unique_id; ?>(position) {
			var geocoder = new google.maps.Geocoder;
			var latlng = {lat: position.coords.latitude, lng: position.coords.longitude};
			geocoder.geocode({'location': latlng}, function(results, status) {
				if (status === 'OK') {
					if (results[0]) {
						document.getElementById('<?php echo $f->unique_id; ?>').value = results[0].formatted_address;
					} else {
						alert('ERROR: Geocoder found no results');
					}
				} else {
					alert('ERROR: Geocoder status: ' + status);
				}
			});
		}
	</script>
<?php
}

function bps_unique_id ($id)
{
	static $k = array ();

	$k[$id] = isset ($k[$id])? $k[$id] + 1: 0;
	return $k[$id]? $id. '_'. $k[$id]: $id;
}
