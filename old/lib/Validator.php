<?php
require_once('function.php');

class Validator
{
    protected $validate_rules;

    public function __construct($validate_rules)
    {
        $this->validate_rules = $validate_rules;
    }

    public function validate(array $inputs)
    {
        $validate_rules = $this->validate_rules;
        $error_messages = [];

        foreach ($validate_rules as $key => $validate_rule) {
            $is_empty = false;

            if (array_isset('required', $validate_rule) && $validate_rule['required']) {
                $error_message = $this->empty($inputs[$key], $key);
                if (!is_empty($error_message)) {
                    $error_messages[] = $error_message;
                    $is_empty         = true;
                }
            }

            if (isset($inputs[$key])) {
                if (array_isset('length', $validate_rule) && !$is_empty) {
                    $error_message = $this->length($inputs[$key], $key, $validate_rule);
                    if (!is_empty($error_message)) {
                        $error_messages[] = $error_message;
                    }
                }

                if (array_isset('file_extension', $validate_rule)) {
                    $error_message = $this->file_extension($inputs[$key], $key, $validate_rule);
                    if (!is_empty($error_message)) {
                        $error_messages[] = $error_message;
                    }
                }

                if (array_isset('file_size', $validate_rule)) {
                    $error_message = $this->file_size($inputs[$key], $key, $validate_rule);
                    if (!is_empty($error_message)) {
                        $error_messages[] = $error_message;
                    }
                }
            }
        }

        return $error_messages;
    }

    public function empty($input, $key)
    {
        return (is_empty($input)) ? $key . ' is empty' : null;
    }

    public function length($input, $key, $validate_rule)
    {
        if (array_isset('min', $validate_rule['length']) && array_isset('max', $validate_rule['length'])) {
            $input_length = mb_strlen($input);
            if ($input_length < $validate_rule['length']['min'] || $input_length > $validate_rule['length']['max']) {
                return "Your {$key} must be {$validate_rule['length']['max']} to {$validate_rule['length']['min']} characters long";
            }
        } elseif (!is_empty($input) && array_key_exists('fix', $validate_rule['length'])) {
            if (!ctype_digit($input) || mb_strlen($input) !== $validate_rule['length']['fix']) {
                return "Your password must be {$validate_rule['length']['fix']} digit number";
            }
        }

        return '';
    }

    public function file_extension($input, $key, $validate_rule)
    {
        $mime_type = mime_content_type($input['tmp_name']);

        if (strpos($mime_type, $key) !== false) {
            $ext = mb_strtolower(pathinfo($input['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $validate_rule['file_extension']) ) {
                $valid_extension = implode(', ', $validate_rule['file_extension']);
                return "Your {$key} is only valid {$valid_extension}";
            }

            return '';
        }

        return "type isn't corrected";
    }

    public function file_size($input, $key, $validate_rule)
    {
        if (array_isset('max_byte', $validate_rule['file_size'])) {
            if ($input['size'] > $validate_rule['file_size']['max_byte']) {
                $formatted_size_unit = format_size_unit($validate_rule['file_size']['max_byte']);
                return "Your {$key} is only valid {$formatted_size_unit} or less";
            }
        }

        return '';
    }
}
