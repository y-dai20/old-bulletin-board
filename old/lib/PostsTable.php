<?php

require_once('Database.php');

class PostsTable extends Database
{
    protected $table_name     = 'posts_list';
    protected $validate_rules = [
        'title' => [
            'required' => true,
            'length'   => ['max' => 32, 'min' => 10],
        ],
        'message' => [
            'required' => true,
            'length'   => ['max' => 200, 'min' => 10],
        ],
        'image' => [
            'required'       => false,
            'file_extension' => ['jpeg', 'jpg', 'png', 'gif'],
            'file_size'      => ['max_byte' => 1024 * 1024],
        ],
        'password' => [
            'required' => false,
            'length'   => ['fix' => 4],
        ],
    ];
}
