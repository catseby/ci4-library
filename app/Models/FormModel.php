<?php

namespace App\Models;

use CodeIgniter\Model;

class FormModel
{

    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    private function getFileByName($files, $filename)
    {
        foreach ($files as $inputName => $fileGroup) {
            foreach ($fileGroup as $file) {
                if ($file->getName() === $filename) {
                    return $file; // Return the file if found
                }
            }
        }
        return null; // Return null if file not found
    }

    private function setFiles($files, $filenames = null, $old_filenames = null)
    {

        if ($old_filenames != null) {
            foreach ($old_filenames as $old_file) {
                if (file_exists("./uploads/" . $old_file)) {
                    unlink("./uploads/" . $old_file);
                }
            }
        }

        if ($filenames != null) {
            foreach ($filenames as $filename) {
                $file = $this->getFileByName($files, $filename);
                $file->move('uploads', $file->getName());
            }
        }
    }

    public function getValue($table_name, $column_name, $index, $value_name)
    {
        return $this->db->query("SELECT " . $value_name . " FROM public." . $table_name . " WHERE " . $column_name . " = " . $index)->getRowArray()[$value_name];
    }

    public function getForm($table_name)
    {
        return $this->db->query("SELECT * FROM form_metadata WHERE table_name =  '" . $table_name . "' ORDER BY order_position ASC")->getResultArray();
    }

    public function fetch($table_name, $column_name, $index)
    {
        return $this->db->query("SELECT * FROM public." . $table_name . " WHERE " . $column_name . " = " . $index)->getRowArray();
    }

    public function insert($table_name, $post, $files)
    {
        $data = [];

        foreach ($post as $row) {
            if (isset($row["value"])) {
                if (isset($row["files"]) && $row["files"] == true) {
                    $this->setFiles($files, $row["value"]);
                    $data[$row["key"]] = json_encode($row["value"]);
                } else {
                    $data[$row["key"]] = $row["value"];
                }
            }
        }

        log_message("debug", json_encode($data));

        $user = auth()->user();
        $data['created_user_id'] = $user->id ?? null;
        $data['created_at'] = date('Y-m-d H:i:s');

        $query = 'INSERT INTO public.' . $table_name . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_fill(0, count($data), '?')) . ');';
        $query = $this->db->query($query, array_values($data));

        return $this->db->insertID();
    }

    public function update($table_name, $column_name, $index, $post, $files)
    {
        $data = [];
        $keys = [];

        foreach ($post as $row) {
            if (isset($row["value"])) {

                if (isset($row["files"]) && $row["files"] == true) {
                    $old_filenames = json_decode($this->getValue($table_name, $column_name, $index, $row["key"]));
                    $this->setFiles($files, $row["value"], $old_filenames);
                    $data[$row["key"]] = json_encode($row["value"]);
                } else {
                    $data[$row["key"]] = $row["value"];
                }
                $keys[] = $row["key"] . " = ?";
            }
        }

        $user = auth()->user();
        $data['updated_user_id'] = $user->id ?? null;
        $data['updated_at'] = date('Y-m-d H:i:s');

        $keys[] = 'updated_user_id = ?';
        $keys[] = 'updated_at = ?';

        $sql = 'UPDATE public.' . $table_name . ' SET ' . implode(',', $keys) . ' WHERE ' . $column_name . ' = ' . $index;
        $this->db->query($sql, array_values($data));
    }

    public function delete($table_name, $column_name, $index, $file_columns)
    {
        foreach ($file_columns as $key) {
            $old_filenames = json_decode($this->getValue($table_name, $column_name, $index, $key));
            $this->setFiles(null, null, $old_filenames);
        }

        $this->db->query('DELETE FROM public.' . $table_name . ' WHERE ' . $column_name . ' = ' . $index);
    }
}