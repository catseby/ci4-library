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

    public function getForm($table_name)
    {
        return $this->db->query("SELECT * FROM " . $table_name . " ORDER BY order_position ASC");
    }

    public function fetch($table_name, $column_name, $index) {
        return $this->db->query("SELECT * FROM public." . $table_name . " WHERE " . $column_name . " = " . $index)->getRowArray();
    }

    public function insert($table_name, $post)
    {
        $data = [];

        foreach ($post as $key => $value) {
            if ($value != 'undefined') {
                $data[$key] = $value;
            }
        }

        $user = auth()->user();
        $userId = $user->id ?? null;
        $timestamp = date('Y-m-d H:i:s');

        $data['created_user_id'] = $userId;
        $data['created_at'] = $timestamp;

        $query = 'INSERT INTO public.' . $table_name . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_fill(0, count($data), '?')) . ');';
        $query = $this->db->query($query, array_values($data));

        return $this->db->insertID();
    }

    public function insertFiles($table_name, $post, $files)
    {
        foreach ($files['files'] as $file) {
            $filename = $file->getName();
            $file->move('uploads', $filename);

            $key = key($file);

            $data = [];
            foreach ($post as $key => $value) {
                if ($value == "?filename") {
                    $data[$key] = $filename;
                } else if ($value != 'undefined') {
                    $data[$key] = $value;
                }
            }

            $user = auth()->user();
            $userId = $user->id ?? null;
            $timestamp = date('Y-m-d H:i:s');

            $data['created_user_id'] = $userId;
            $data['created_at'] = $timestamp;

            $query = 'INSERT INTO public.' . $table_name . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_fill(0, count($data), '?')) . ');';
            $this->db->query($query, array_values($data));
            return $this->db->insertID();
        }
    }

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
}
