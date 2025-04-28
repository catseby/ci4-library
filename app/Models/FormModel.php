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

    private function setFiles($files, $filenames, $old_filenames = null)
    {
        if ($old_filenames != null) {
            foreach ($old_filenames as $old_file) {
                unlink("./uploads/" . $old_file);
            }
        }

        foreach ($filenames as $filename) {
            $file = $this->getFileByName($files, $filename);
            $file->move('uploads', $file->getName());
        }
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
        $raw_data = json_decode($post["data"], true);
        $data = [];

        foreach ($raw_data as $row) {
            if ($row["value"] != 'undefined') {
                if (isset($row["files"]) && $row["files"] == true) {
                    $this->setFiles($files, $row["files"]);
                }
                $data[$row["key"]] = $row["value"];
            }
        }

        $user = auth()->user();
        $data['created_user_id'] = $user->id ?? null;
        $data['created_at'] = date('Y-m-d H:i:s');

        $query = 'INSERT INTO public.' . $table_name . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_fill(0, count($data), '?')) . ');';
        $query = $this->db->query($query, array_values($data));

        return $this->db->insertID();
    }

    // public function insert($table_name, $post)
    // {
    //     $data = [];

    //     foreach ($post as $key => $value) {
    //         if ($value != 'undefined') {
    //             $data[$key] = $value;
    //         }
    //     }

    //     $user = auth()->user();
    //     $userId = $user->id ?? null;
    //     $timestamp = date('Y-m-d H:i:s');

    //     $data['created_user_id'] = $userId;
    //     $data['created_at'] = $timestamp;

    //     $query = 'INSERT INTO public.' . $table_name . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_fill(0, count($data), '?')) . ');';
    //     $query = $this->db->query($query, array_values($data));

    //     return $this->db->insertID();
    // }

    // public function insertFiles($table_name, $post, $files)
    // {
    //     foreach ($files['files'] as $file) {
    //         $filename = $file->getName();
    //         $file->move('uploads', $filename);

    //         $key = key($file);

    //         $data = [];
    //         foreach ($post as $key => $value) {
    //             if ($value == "?filename") {
    //                 $data[$key] = $filename;
    //             } else if ($value != 'undefined') {
    //                 $data[$key] = $value;
    //             }
    //         }

    //         $user = auth()->user();
    //         $userId = $user->id ?? null;
    //         $timestamp = date('Y-m-d H:i:s');

    //         $data['created_user_id'] = $userId;
    //         $data['created_at'] = $timestamp;

    //         $query = 'INSERT INTO public.' . $table_name . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_fill(0, count($data), '?')) . ');';
    //         $this->db->query($query, array_values($data));
    //         return $this->db->insertID();
    //     }
    // }

    public function update($table_name, $column_name, $index, $post)
    {
        $data = [];
        $keys = [];
        foreach ($post as $key => $value) {
            if ($value != 'undefined') {
                $data[$key] = $value;
                $keys[] = $key . " = ?";
            }
        }

        $user = auth()->user();
        $userId = $user->id ?? null;
        $timestamp = date('Y-m-d H:i:s');

        $data['updated_user_id'] = $userId;
        $data['updated_at'] = $timestamp;

        $keys[] = 'updated_user_id = ?';
        $keys[] = 'updated_at = ?';

        $sql = 'UPDATE public.' . $table_name . ' SET ' . implode(',', $keys) . ' WHERE ' . $column_name . ' = ' . $index;
        $this->db->query($sql, array_values($data));
    }

    public function updateFiles($table_name, $post, $files)
    {
        foreach ($files['files'] as $file) {
            $filename = $file->getName();
            $file->move('uploads', $filename);

            $key = key($file);

            $data = [];
            $keys = [];
            foreach ($post as $key => $value) {
                if ($value == "?filename") {
                    $data[$key] = $filename;
                } else if ($value != 'undefined') {
                    $data[$key] = $value;
                }
                $keys[] = $key . " = ?";
            }

            $user = auth()->user();
            $userId = $user->id ?? null;
            $timestamp = date('Y-m-d H:i:s');

            $data['updated_user_id'] = $userId;
            $data['updated_at'] = $timestamp;

            $keys[] = 'updated_user_id = ?';
            $keys[] = 'updated_at = ?';

            $query = 'INSERT INTO public.' . $table_name . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(', ', array_fill(0, count($data), '?')) . ');';
            $this->db->query($query, array_values($data));
        }
    }

    public function delete($table_name, $column_name, $index)
    {
        $this->db->query('DELETE FROM public.' . $table_name . ' WHERE ' . $column_name . ' = ' . $index);
    }

    public function deleteFiles($table_name, $column_name, $index)
    {
        $sql1 = "SELECT column_name " .
            "FROM information_schema.columns " .
            "WHERE table_schema = 'public' " .
            "AND table_name = '" . $table_name . "' " .
            "AND domain_name IN ('image') " .
            "ORDER BY table_name, ordinal_position;";

        $valid_column_arr = $this->db->query($sql1)->getResultArray();
        $results = $this->db->query('SELECT * FROM public.' . $table_name . ' WHERE ' . $column_name . ' = ' . $index)->getResultArray();

        foreach ($valid_column_arr as $valid_column_key => $valid_column) {
            $column_name = $valid_column['column_name'];
            foreach ($results as $key => $result) {
                unlink("./uploads/" . $result[$column_name]);
            }
        }

        $this->delete($table_name, $column_name, $index);
    }
}
