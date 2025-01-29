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
        $links = $this->getLinks($results, "add");

        log_message("debug", json_encode($links));

        $data = [
            'name' => $table,
            'schema' => json_encode($schema),
            'form' => json_encode($form),
            'links' => json_encode($links),
            'type' => 'post',
            'values' => '{}'
        ];

        return view("form_test", $data);
    }

    public function add($name)
    {
        $files = $this->request->getFiles();
        $post = $this->request->getPost();
        $id = null;


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
                    } else if ($value != 'undefined') {
                        $data[$key] = $value;
                    }
                }

                $query = 'INSERT INTO public.' . $name . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_fill(0, count($data), '?')) . ');';
                $db->query($query, array_values($data));
                $id = $db->insertID();
            }
        } else {
            $data = [];
            foreach ($post as $key => $value) {
                if ($value != 'undefined') {
                    $data[$key] = $value;
                }
            }


            $query = 'INSERT INTO public.' . $name . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_fill(0, count($data), '?')) . ');';
            $query = $db->query($query, array_values($data));
            $id = $db->insertID();
        }

        log_message("debug", $id);
        return json_encode(['id' => $id]);
    }

    public function edit($table, $index, $column)
    {
        $formModel = new FormModel();
        $template = $formModel->getForm($table);

        $schema = $this->getSchema($table, $template);
        $form = $this->getForm($template, "Save");
        $links = $this->getLinks($template, "edit", $index);


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

        $data = [
            'name' => $table,
            'schema' => json_encode($schema),
            'form' => json_encode($form),
            'links' => json_encode($links),
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
                    } else if ($value != 'undefined') {
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
                if ($value != 'undefined') {
                    $data[$key] = $value;
                }
                $keys[] = $key . " = ?";
            }


            $sql = 'UPDATE public.' . $name . ' SET ' . implode(',', $keys) . ' WHERE ' . $column . ' = ' . $index;
            $db->query($sql, array_values($data));

        }

        return json_encode(['id' => $index]);
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

    private function getSchema($table, $results)
    {
        $schema = [
            "type" => "object",
            "title" => ucfirst($table),
            "properties" => []
        ];

        foreach ($results as $index => $result) {

            $property;
            $required;

            if ($result['required'] == "t") {
                $required = true;
            } else {
                $required = false;
            }

            switch ($result['schema_type']) {
                case 'array':
                    $property = [
                        'type' => $result['schema_type'],
                        'title' => $result['column_title'],
                        "items" => [
                            "type" => "string",
                            'required' => $required
                        ]
                    ];
                    break;
                case 'image':
                    $property = [
                        'type' => 'file',
                        'title' => $result['column_title'],
                        'image' => true,
                        'required' => $required
                    ];
                    break;
                default:
                    $property = [
                        'type' => $result['schema_type'],
                        'title' => $result['column_title'],
                        'required' => $required
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
        $tabs = [];

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

            if ($result["form_tab"] == null) {
                array_push($form, $field);

                if ($extraField) {
                    array_push($form, $extraField);
                }
            } else {

                if (!array_key_exists($result["form_tab"], $tabs)) {
                    $tabs[$result['form_tab']] = [];
                }

                array_push($tabs[$result['form_tab']], $field);

                if ($extraField) {
                    array_push($tabs[$result['form_tab']], $extraField);
                }
            }
        }

        if ($tabs) {

            $fieldset = [
                "type" => "fieldset",
                "items" => [
                    [
                        "type" => "tabs",
                        'id' => "navtabs",
                        "items" => []
                    ]
                ]
            ];

            foreach ($tabs as $key => $tab) {
                $newTab = [
                    "type" => "tab",
                    "title" => $key,
                    "items" => $tab
                ];

                array_push($fieldset["items"][0]["items"], $newTab);
            }

            log_message("debug", json_encode($fieldset));

            array_push($form, $fieldset);

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

    private function getLinks($results, $type, $id = null)
    {
        $t_links = [];
        foreach ($results as $index => $result) {

            if ($result["to_foreign"] == 't') {

                if (!array_key_exists($result['f_table'], $t_links)) {
                    $t_links[$result["f_table"]] = [
                        'table' => $result['f_table'],
                        'type' => $type,
                        'keys' => [$result['column_name']],
                        'param' => $result['f_primary_key'],
                        'index' => $id
                    ];
                } else {
                    array_push($t_links[$result['f_table']]['keys'], $result['column_name']);
                }

            } else {
                if (!array_key_exists($result['table_name'], $t_links)) {
                    $t_links[$result["table_name"]] = [
                        'table' => $result['table_name'],
                        'type' => $type,
                        'keys' => [$result['column_name']],
                        'param' => 'id',
                        'index' => $id
                    ];
                } else {
                    array_push($t_links[$result['table_name']]['keys'], $result['column_name']);
                }
            }
        }

        $links = [];
        foreach ($t_links as $key => $value) {
            array_push($links, $value);
        }

        return $links;
    }
}
