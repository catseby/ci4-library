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

        log_message("debug", $column);
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

    public function update($name, $id)
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
                $keys = [];
                foreach ($post as $key => $value) {
                    if ($value == "?filename") {
                        $data[$key] = $filename;
                    } else {
                        $data[$key] = $value;
                    }
                    $keys[] = $key . " = ?";
                }
                $sql = 'UPDATE public.' . $name . ' SET ' . implode(',', $keys) . ' WHERE id = ' . $id;
                $db->query($sql, array_values($data));
            }
        } else {
            $data = [];
            $keys = [];
            foreach ($post as $key => $value) {
                $data[$key] = $value;
                $keys[] = $key . " = ?";
            }


            $sql = 'UPDATE public.' . $name . ' SET ' . implode(',', $keys) . ' WHERE id = ' . $id;
            $db->query($sql, array_values($data));
        }
    }

    public function destroy($name, $id)
    {
        $db = db_connect();

        $query = 'DELETE FROM public.' . $name . ' WHERE id = ' . $id;
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
}
