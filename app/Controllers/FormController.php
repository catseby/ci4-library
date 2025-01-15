<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FormTemplateModel;
use CodeIgniter\HTTP\ResponseInterface;

class FormController extends BaseController
{

    public function fetch($name)
    {

        $db = db_connect();

        $sql = 'SELECT * FROM public.' . $name;
        $result = $db->query($sql)->getResultArray();

        return json_encode($result);
    }

    public function index($name)
    {
        $ftm = new FormTemplateModel();
        $template = $ftm->getForm($name);

        $link = $name . '/add';

        $data = [
            'name' => $template['name'],
            'schema' => $template['schema'],
            'form' => $template['form'],
            'link' => $link,
            'type' => 'post',
            'values' => '{}'
        ];

        return view("form_test", $data);
    }

    public function add($name)
    {
        $files = $this->request->getFiles();
        $post = $this->request->getPost();

        $db = db_connect();

        if (count($files) > 0) {
            foreach ($files['files'] as $file) {
                $filename = $file->getName();
                $file->move('uploads', $filename);

                $key = key($file);

                $data = [];
                foreach ($post as $key => $value) {
                    if ($value == "?filename") {
                        $data[$key] = $filename;
                    } else {
                        $data[$key] = $value;
                    }
                }

                $query = 'INSERT INTO public.' . $name . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_fill(0, count($data), '?')) . ');';
                $db->query($query, array_values($data));
            }
        } else {
            $data = [];
            foreach ($post as $key => $value) {
                $data[$key] = $value;
            }


            $query = 'INSERT INTO public.' . $name . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_fill(0, count($data), '?')) . ');';
            $db->query($query, array_values($data));
        }
    }

    public function edit($name, $index, $column)
    {
        $ftm = new FormTemplateModel();
        $template = $ftm->getForm($name);

        $db = db_connect();

        $sql = 'SELECT * FROM public.' . $name . ' WHERE ' . $column . ' = ' . $index;
        $query = $db->query($sql);
        $result = $query->getResultArray();

        foreach ($result[0] as $key => $value) {
            $decoded = json_decode($value, true);
            if ($decoded != null) {
                $result[0][$key] = json_decode($value, true);
            }
        }

        $link = $name . '/edit/' . $index . '/' . $column;
        $data = [
            'name' => $template['name'],
            'schema' => $template['schema'],
            'form' => $template['form'],
            'link' => $link,
            'type' => 'post',
            'values' => json_encode($result)
        ];


        return view("form_test", $data);
    }

    public function update($name, $index, $column)
    {
        $files = $this->request->getFiles();
        $post = $this->request->getPost();

        $db = db_connect();

        if (count($files) > 0) {
            $this->destroy_with_files($name,$index, $column);

            foreach ($files['files'] as $file) {
                $filename = $file->getName();
                $file->move('uploads', $filename);

                $key = key($file);

                $data = [];
                $keys = [];
                foreach ($post as $key => $value) {
                    if ($value == "?filename") {
                        $data[$key] = $filename;
                    } else {
                        $data[$key] = $value;
                    }
                    $keys[] = $key . " = ?";
                }
                $query = 'INSERT INTO public.' . $name . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_fill(0, count($data), '?')) . ');';
                $db->query($query, array_values($data));
            }
        } else {
            $data = [];
            $keys = [];
            foreach ($post as $key => $value) {
                $data[$key] = $value;
                $keys[] = $key . " = ?";
            }


            $sql = 'UPDATE public.' . $name . ' SET ' . implode(',', $keys) . ' WHERE ' . $column . ' = ' . $index;
            $db->query($sql, array_values($data));
        }
    }

    public function destroy($name, $index, $column)
    {
        $db = db_connect();

        $query = 'DELETE FROM public.' . $name . ' WHERE '. $column . ' = ' . $index;
        $db->query($query);

        $data = [
            'name' => "Entry from " . $name . ' was deleted.',
            'schema' => '{}',
            'form' => '{}',
            'link' => "",
            'type' => 'get',
            'values' => '{}'
        ];

        return view("form_test", $data);

    }

    public function destroy_with_files($name, $index, $column) {
        $db = db_connect();

        $sql1 = "SELECT column_name ". 
        "FROM information_schema.columns ". 
        "WHERE table_schema = 'public' ". 
        "AND table_name = '" . $name . "' ". 
        "AND domain_name IN ('image') ". 
        "ORDER BY table_name, ordinal_position;";

        $sql2 = 'SELECT * FROM public.' . $name . ' WHERE ' . $column . ' = ' . $index;

        $sql3 = 'DELETE FROM public.' . $name . ' WHERE '. $column . ' = ' . $index;

        $valid_column_arr = $db->query($sql1)->getResultArray();
        $results = $db->query($sql2)->getResultArray();

        
        foreach($valid_column_arr as $valid_column_key => $valid_column) 
        {
            $column_name = $valid_column['column_name'];
            log_message("debug", $column_name);
            foreach($results as $key => $result) {
                unlink("./uploads/" . $result[$column_name]);
            }
        }

        $db->query($sql3);
    }
}
