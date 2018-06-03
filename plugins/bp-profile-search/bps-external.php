<?php

add_filter ('bps_add_fields', 'bps_users_setup', 99);
function bps_users_setup ($fields)
{
	$columns = array
	(
		'ID'					=> 'integer',
		'user_login'			=> 'text',
//		'user_pass'				=> 'text',
//		'user_nicename'			=> 'text',
		'user_email'			=> 'text',
		'user_url'				=> 'text',
		'user_registered'		=> 'date',
//		'user_activation_key'	=> 'text',
		'user_status'			=> 'integer',
		'display_name'			=> 'text',
	);

	$columns = apply_filters ('bps_users_columns', $columns);
	foreach ($columns as $column => $format)
	{	
		$f = new stdClass;
		$f->group = __('Users data', 'bp-profile-search');
		$f->id = $column;
		$f->code = $column;
		$f->name = $column;
		$f->description = '';

		$f->format = $format;
		$f->options = array ();
		$f->search = 'bps_users_search';

		$fields[] = $f;
	}

	return $fields;
}

function bps_users_search ($f)
{
	global $wpdb;

	$filter = $f->format. '_'.  ($f->filter == ''? 'is': $f->filter);
	$value = $f->value;

	$sql = array ('select' => '', 'where' => array ());
	$sql['select'] = "SELECT ID FROM {$wpdb->users}";

	switch ($filter)
	{
	case 'text_contains':
		$escaped = '%'. bps_esc_like ($value). '%';
		$sql['where'][$filter] = $wpdb->prepare ("{$f->id} LIKE %s", $escaped);
		break;

	case 'text_is':
		$sql['where'][$filter] = $wpdb->prepare ("{$f->id} = %s", $value);
		break;

	case 'text_like':
		$value = str_replace ('\\\\%', '\\%', $value);
		$value = str_replace ('\\\\_', '\\_', $value);
		$sql['where'][$filter] = $wpdb->prepare ("{$f->id} LIKE %s", $value);
		break;

	case 'integer_is':
		$sql['where'][$filter] = $wpdb->prepare ("{$f->id} = %d", $value);
		break;

	case 'integer_range':
		if (isset ($value['min']))  $sql['where']['min'] = $wpdb->prepare ("{$f->id} >= %d", $value['min']);
		if (isset ($value['max']))  $sql['where']['max'] = $wpdb->prepare ("{$f->id} <= %d", $value['max']);
		break;

	case 'date_age_range':
		$day = date ('j');
		$month = date ('n');
		$year = date ('Y');

		if (isset ($value['max']))
		{
			$ymin = $year - $value['max'] - 1; 
			$sql['where']['age_min'] = $wpdb->prepare ("DATE({$f->id}) > %s", "$ymin-$month-$day");
		}
		if (isset ($value['min']))
		{
			$ymax = $year - $value['min'];
			$sql['where']['age_max'] = $wpdb->prepare ("DATE({$f->id}) <= %s", "$ymax-$month-$day");
		}
		break;

	default:
		return array ();
	}

	$sql = apply_filters ('bps_field_sql', $sql, $f);
	$query = $sql['select']. ' WHERE '. implode (' AND ', $sql['where']);

	$results = $wpdb->get_col ($query);
	return $results;
}
