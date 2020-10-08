<?php

function h($str, $flags = ENT_QUOTES, $encoding = 'UTF-8') {
    return htmlspecialchars($str, $flags, $encoding);
}

function is_empty($var) {
    return ($var === '' || $var === null || $var === []);
}

function array_isset($key, $array) {
    return (array_key_exists($key, $array) && !is_empty($array[$key]));
}

function get_inputs(array $input_keys) {
    $inputs = [];
    foreach ($input_keys as $input_key) {
        if (isset($_POST[$input_key])) {
            $trimed_input = trim($_POST[$input_key]);
            if ($trimed_input == '') {
                $inputs[$input_key] = null;
            } else {
                $inputs[$input_key] = $trimed_input;
            }
        } else {
            $inputs[$input_key] = null;
        }
    }

    return $inputs;
}

function format_size_unit(int $bytes) {
    $units = [
        'TB' => pow(1024, 4),
        'GB' => pow(1024, 3),
        'MB' => pow(1024, 2),
        'KB' => 1024,
    ];

    foreach ($units as $unit => $unit_value) {
        if ($bytes >= $unit_value) {
            $formatted_size = number_format($bytes / $unit_value, 1);
            return $formatted_size . $unit;
        }
    }
    return $bytes . 'B';
}

function get_file(string $name_attribute) {
    if (isset($_FILES[$name_attribute]) && is_uploaded_file($_FILES[$name_attribute]['tmp_name'])) {
        return $_FILES[$name_attribute];
    }

    return null;
}
