<?php

require_once('function.php');

class UploadedFile
{
    protected $file_dir_path;

    public function __construct($file_dir_path)
    {
        $this->file_dir_path = rtrim($file_dir_path, '/') . '/';
    }

    public function save($uploaded_file, $file_name = null)
    {
        $ext = mb_strtolower(pathinfo($uploaded_file['name'], PATHINFO_EXTENSION));

        if (is_empty($file_name)) {
            $save_file = $this->generateFileName($ext);
        } else {
            $save_file = $file_name . '.' . $ext;
        }

        if (!file_exists($this->file_dir_path)) {
            if (!mkdir($this->file_dir_path)) {
                throw new Exception("The directory creation failed");
            }
        }

        $save_file_path = $this->file_dir_path . $save_file;

        if (move_uploaded_file($uploaded_file['tmp_name'], $save_file_path)) {
            return $save_file_path;
        } else {
            throw new Exception("Couldn't preseve file.");
        }

        return null;
    }

    public function delete($file_path)
    {
        if (!unlink($file_path)) {
            throw new Exception("file couldn't deleted");
        }
    }

    protected function generateFileName($ext = null)
    {
        if (is_empty($ext)) {
            return uniqid(mt_rand());
        }

        $ext = trim($ext, '.');
        return uniqid(mt_rand()) . '.' . $ext;
    }
}
