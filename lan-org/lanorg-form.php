<?php

// Functions used to generate HTML code
$lanorg_field_html = array(
	'text' => 'lanorg_text_field_html',
	'select' => 'lanorg_select_field_html',
);

$lanorg_validators = array(
	'empty' => 'lanorg_validate_empty',
	'username_exists' => 'lanorg_validate_username_exists',
	'username_valid' => 'lanorg_validate_username_valid',
);

// Get POST values for each field
// @return Boolean 
function lanorg_form_post($fields, &$values, $prefix) {
	$complete = TRUE;
	foreach ($fields as $field) {
		$key = $field['key'];

		$name_attr = $prefix . $key;

		if (isset($_POST[$name_attr])) {
			$values[$key] = $_POST[$name_attr];
		}
		else {
			$complete = FALSE;
		}
	}
	return $complete;
}

// Generate HTML markup for a form
// Each field is enclosed in a <P> element
// @param fields Array which contains field
function lanorg_form_html_as_p($fields, $values, $prefix='', $errors=array()) {
	$markup = '';

	foreach ($fields as $field) {

		$key = $field['key'];
		$value = isset($values[$key]) ? $values[$key] : NULL;

		$error = NULL;

		if (isset($errors[$key])) {
			$error = $errors[$key];
		}

		$field_markup = lanorg_form_field_html($field, $value, $prefix, $error);

		if ($field_markup !== NULL) {
			$markup .= '<p>';
			$markup .= $field_markup;
			$markup .= '</p>';
		}
	}

	return $markup;
}

// Return HTML markup for a field
// @param field Array which contains field options
// @param value Field value
// @return string HTML markup or NULL if the field type is unknown
function lanorg_form_field_html($field, $value, $prefix='', &$error) {
	global $lanorg_field_html;
	$markup = NULL;

	if (isset($field['type']) && isset($field['key'])) {

		$type = $field['type'];
		$key = $field['key'];

		if (isset($lanorg_field_html[$type])) {
			$func = $lanorg_field_html[$type];

			$markup = call_user_func($func, $field, $value, $prefix, $error);
		}
	}

	return $markup;
}

// Validates each field
// Values are validated and error are added to the $error array.
// @return TRUE when no error is found.
function lanorg_form_validation($fields, $values, &$errors) {
	global $lanorg_validators;

	foreach ($fields as $field) {

		$key = $field['key'];

		if (isset($field['validator']) && isset($values[$key])) {

			$value = $values[$key];

			$validators = $field['validator'];

			// string is accepted, convert it to an array of one element
			if (is_string($validators)) {
				$validators = array($validators);
			}

			// The value passes a series of validations
			foreach ($validators as $validator) {

				if (isset($lanorg_validators[$validator])) {

					$validator_func = $lanorg_validators[$validator];

					$field_errors = array();

					call_user_func($validator_func, $field, $value, &$field_errors);

					// currently, only one error is retained
					if (count($field_errors)) {
						$errors[$key] = $field_errors[0];
					}
				}
			}
		}

	}

	return (bool) count($error);
}

// ** Fields type ***********

// Gets HTML code for a LABEL element
function lanorg_label_html($id, $label) {
	$markup = '';
	$label = htmlentities($label, NULL, 'UTF-8');
	$markup = '<label for="' . $id . '">' . $label . '</label>';
	return $markup;
}

// Displays errors
function lanorg_display_error() {
	
}

// Text field
// Parameters accepted in options array :
// key, label, default
function lanorg_text_field_html($options, $value, $prefix, $error) {
	$markup = '';
	$key = $prefix . $options['key'];
	$is_password = isset($options['password']) && $options['password'];
	$css_classes = 'lanorg-field lanorg-text';

	if ($error !== NULL) {
		$markup = $error . '<br/>';
		$css_classes .= ' lanorg-error';
	}

	// Default option
	if ($value === NULL && isset($options['default'])) {
		$value = $options['default'];
	}

	// Label tag, if supplied
	if (isset($options['label'])) {
		$markup .= lanorg_label_html($key, $options['label']);
	}

	$markup .= '<input ';
	$markup .= 'type="' . ($is_password ? 'password' : 'text') . '" ';
	$markup .= 'id="' . $key . '" ';
	$markup .= 'name="' . $key . '" ';

	if ($value !== NULL) {
		$markup .= 'value="' . htmlentities($value, NULL, 'UTF-8') . '" ';
	}
	$markup .= 'class="' . $css_classes . '"/>';
	return $markup;
}

// TODO: Select field
function lanorg_select_field_html($options, $value, $prefix) {

}

// ** Data validation ***********

// Checks if the field is empty
function lanorg_validate_empty($options, $value, &$errors) {
	if (empty($value)) {
		array_push($errors, 'Ce champ est obligatoire.');
	}
}

// Raise an error when the username is already taken
function lanorg_validate_username_exists($options, $value, &$errors) {
	if (username_exists($value)) {
		array_push($errors, 'Ce pseudonyme existe déjà.');
	}
}

// Raise an error when the username is already taken
function lanorg_validate_username_valid($options, $value, &$errors) {
	if (!validate_username($value)) {
		array_push($errors, 'Ce pseudonyme n\'est pas valide.');
	}
}
?>