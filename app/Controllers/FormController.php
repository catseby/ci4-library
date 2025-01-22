<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FormTemplateModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\FormModel;

class FormController extends BaseController
{
    public function fetch($table, $column)
    {
        $sql = 'SELECT id, ' . $column . ' AS item FROM public.' . $table . ';';

        $db = db_connect();

        $result = $db->query($sql)->getResultArray();

        return json_encode($result);
    }

    public function fetchWhere($table, $column, $target, $value)
    {
        $sql = 'SELECT id, ' . $column . ' AS item FROM public.' . $table . ' WHERE ' . $target . " = '" . $value . "';";

        $db = db_connect();

        $result = $db->query($sql)->getResultArray();

        return json_encode($result);
    }

    public function index($table)
    {
        $formModel = new FormModel();
        $results = $formModel->getForm($table);

        $schema = $this->getSchema($table, $results);
        $form = $this->getForm($results, "Create");

        $link = $table . '/add/';
        $data = [
            'name' => $table,
            'schema' => json_encode($schema),
            'form' => json_encode($form),
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

    public function edit($table, $index, $column)
    {
        $formModel = new FormModel();
        $template = $formModel->getForm($table);

        $schema = $this->getSchema($table, $template);
        $form = $this->getForm($template, "Save");

        $db = db_connect();

        $sql = 'SELECT * FROM public.' . $table . ' WHERE ' . $column . ' = ' . $index;
        $query = $db->query($sql);
        $result = $query->getResultArray();

        foreach ($result[0] as $key => $value) {
            $decoded = json_decode($value, true);
            if ($decoded != null) {
                $result[0][$key] = json_decode($value, true);
            }
        }

        $link = $table . '/edit/' . $index . '/' . $column;
        $data = [
            'name' => $table,
            'schema' => json_encode($schema),
            'form' => json_encode($form),
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
            $this->destroy_with_files($name, $index, $column);

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

        $query = 'DELETE FROM public.' . $name . ' WHERE ' . $column . ' = ' . $index;
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

    private function getSchema($table, $results)
    {
        $schema = [
            "type" => "object",
            "title" => ucfirst($table),
            "properties" => []
        ];

        foreach ($results as $index => $result) {

            $property;

            switch ($result['schema_type']) {
                case 'array':
                    $property = [
                        'type' => $result['schema_type'],
                        'title' => $result['column_title'],
                        "items" => [
                            "type" => "string"
                        ],
                        'required' => true
                    ];
                    break;
                case 'image':
                    $property = [
                        'type' => 'file',
                        'title' => $result['column_title'],
                        'image' => true
                    ];
                    break;
                default:
                    $property = [
                        'type' => $result['schema_type'],
                        'title' => $result['column_title'],
                        'required' => true
                    ];
                    break;
            }

            $schema['properties'][$result['column_name']] = $property;
        }

        return $schema;
    }

    private function getForm($results, $button_title)
    {
        $form = [];

        foreach ($results as $index => $result) {

            $field = [];
            $extraField = [];

            switch ($result['form_type']) {
                case "image":
                    $field = [
                        'key' => $result['column_name'],
                        'image' => []
                    ];
                    $extraField = [
                        "id" => "image-display",
                        "type" => "section"
                    ];
                    break;
                case "image-multiple":
                    $field = [
                        'key' => $result['column_name'],
                        'image' => [
                            'multiple' => true
                        ]
                    ];
                    $extraField = [
                        "id" => "image-display",
                        "type" => "section"
                    ];
                    break;
                case "select":
                    $field = [
                        'key' => $result['column_name'],
                        'select' => [
                            'table' => $result['ref_table_name'],
                            'column' => $result['ref_column_name']
                        ]
                    ];
                    
                    if ($result['dynamic_fetch'] == 't') {
                        $field['select']['ref_fetch_key'] = $result['ref_fetch_key'];
                        $field['select']['ref_fetch_target'] = $result['ref_fetch_target'];
                        $field['select']['dynamic_fetch'] = true;
                    }
                    break;
                case "select-multiple":
                    $field = [
                        'key' => $result['column_name'],
                        'select' => [
                            'multiple' => true,
                            'table' => $result['ref_table_name'],
                            'column' => $result['ref_column_name']
                        ]
                    ];
                    break;
                default:
                    $field = [
                        'key' => $result['column_name'],
                        'placeholder' => $result['column_title']
                    ];
            }

            array_push($form, $field);

            if ($extraField) {
                array_push($form, $extraField);
            }
        }

        array_push(
            $form,
            [
                "type" => "submit",
                "title" => $button_title
            ]
        );

        return $form;
    }
}
