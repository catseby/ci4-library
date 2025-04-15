<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Filters\FormFilter;
use App\Models\FormTemplateModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\FormModel;
use App\Models\TableModel;
use App\Controllers\TableController;
use CodeIgniter\Shield\Authentication\Auth;
use CodeIgniter\Shield\Exceptions\AccessDeniedException;

class FormController extends BaseController
{
    public function add($table)
    {

        if ($this->premissionDenied($table, 'add')) {
            return $this->response->setStatusCode(403)->setBody('Access Denied');
        }

        $formModel = new FormModel();
        $results = $formModel->getForm($table);

        $allowed_columns = $this->getAllowedColumns($table);
        $schema = $this->getSchema($table, $results, $allowed_columns);
        $form = $this->getForm($results, "Create", $allowed_columns);
        $links = $this->getLinks($results, "add", $allowed_columns);

        $data = [
            'name' => $table,
            'schema' => json_encode($schema),
            'form' => json_encode($form),
            'links' => json_encode($links),
            'type' => 'post',
            'values' => '{}'
        ];

        return view("form", $data);
    }
    public function create($name)
    {

        if ($this->premissionDenied($name, 'add')) {
            return $this->response->setStatusCode(403)->setBody('Access Denied');
        }


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

                $user = auth()->user();
                $userId = $user->id ?? null;
                $timestamp = date('Y-m-d H:i:s');

                $data['created_user_id'] = $userId;
                $data['created_at'] = $timestamp;

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

            $user = auth()->user();
            $userId = $user->id ?? null;
            $timestamp = date('Y-m-d H:i:s');

            $data['created_user_id'] = $userId;
            $data['created_at'] = $timestamp;

            $query = 'INSERT INTO public.' . $name . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_fill(0, count($data), '?')) . ');';
            $query = $db->query($query, array_values($data));
            $id = $db->insertID();

            if ($name == "table_metadata") {
                $tableController = new TableModel();
                $tableController->addTable($post["table_name"]);

            } else if ($name == "column_metadata") {
                $insertedID = $db->insertID();
                $inserted_column = $db->query("SELECT * FROM column_metadata WHERE id = " . $insertedID . ";")->getResultArray()[0];

                $tableController = new TableModel();
                $tableController->addColumn($inserted_column);
            }
        }

        return json_encode(['id' => $id]);
    }

    public function edit($table, $index, $column)
    {
        if ($this->premissionDenied($table, 'edit', $index)) {
            return $this->response->setStatusCode(403)->setBody('Access Denied');
        }


        $formModel = new FormModel();
        $template = $formModel->getForm($table);

        $allowed_columns = $this->getAllowedColumns($table);
        $schema = $this->getSchema($table, $template, $allowed_columns);
        $form = $this->getForm($template, "Save", $allowed_columns);
        $links = $this->getLinks($template, "edit", $allowed_columns, $index);

        $joins = $this->getJoins($template, $allowed_columns);


        $db = db_connect();

        $sql = 'SELECT * FROM public.' . $table . " ";
        foreach ($joins as $i => $join) {
            $sql = $sql . "LEFT JOIN public." . $join["table"] . " ON " . $join["table"] . "." . $join["key"] . " = " . $table . ".id ";
        }
        $sql = $sql . 'WHERE ' . $table . "." . $column . ' = ' . $index . ";";

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


        return view("form", $data);
    }

    public function update($name, $index, $column)
    {
        if ($this->premissionDenied($name, 'edit', $index)) {
            return $this->response->setStatusCode(403)->setBody('Access Denied');
        }


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

                $user = auth()->user();
                $userId = $user->id ?? null;
                $timestamp = date('Y-m-d H:i:s');

                $data['updated_user_id'] = $userId;
                $data['updated_at'] = $timestamp;

                $keys[] = 'updated_user_id = ?';
                $keys[] = 'updated_at = ?';

                $query = 'INSERT INTO public.' . $name . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(', ', array_fill(0, count($data), '?')) . ');';
                $db->query($query, array_values($data));

            }
        } else {
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

            $beforeValues = $db->query("SELECT * FROM public." . $name . " WHERE " . $column . " = " . $index)->getResultArray()[0];

            $sql = 'UPDATE public.' . $name . ' SET ' . implode(',', $keys) . ' WHERE ' . $column . ' = ' . $index;
            $db->query($sql, array_values($data));

            if ($name == "table_metadata") {
                $tableModel = new TableModel();
                $tableModel->alterTable($beforeValues["table_name"], $post["table_name"]);
            }
            else if ($name == "column_metadata") {
                $inserted_column = $db->query("SELECT * FROM column_metadata WHERE id = " . $index . ";")->getResultArray()[0];

                $tableController = new TableModel();
                $tableController->alterColumn($inserted_column, $beforeValues);
            }
        }

        return json_encode(['id' => $index]);
    }

    public function destroy($name, $index, $column)
    {

        if ($this->premissionDenied($name, 'edit', $index)) {
            return $this->response->setStatusCode(403)->setBody('Access Denied');
        }

        $db = db_connect();

        $beforeValues = $db->query("SELECT * FROM public." . $name . " WHERE " . $column . " = " . $index)->getResultArray()[0];

        $query = 'DELETE FROM public.' . $name . ' WHERE ' . $column . ' = ' . $index;
        $db->query($query);

        if ($name == "table_metadata") {
            $tableModel = new TableModel();
            $tableModel->deleteTable($beforeValues["table_name"]);
        }
        else if ($name == "column_metadata") {
            $tableController = new TableModel();
            $tableController->deleteColumn($beforeValues);
        }

        $data = [
            'name' => "Entry from " . $name . ' was deleted.',
            'schema' => '{}',
            'form' => '{}',
            'links' => '{}',
            'type' => 'get',
            'values' => '{}'
        ];

        return view("form", $data);

    }

    public function destroy_with_files($name, $index, $column)
    {

        if ($this->premissionDenied($name, 'edit')) {
            return $this->response->setStatusCode(403)->setBody('Access Denied');
        }

        $db = db_connect();

        $sql1 = "SELECT column_name " .
            "FROM information_schema.columns " .
            "WHERE table_schema = 'public' " .
            "AND table_name = '" . $name . "' " .
            "AND domain_name IN ('image') " .
            "ORDER BY table_name, ordinal_position;";

        $sql2 = 'SELECT * FROM public.' . $name . ' WHERE ' . $column . ' = ' . $index;

        $sql3 = 'DELETE FROM public.' . $name . ' WHERE ' . $column . ' = ' . $index;

        $valid_column_arr = $db->query($sql1)->getResultArray();
        $results = $db->query($sql2)->getResultArray();


        foreach ($valid_column_arr as $valid_column_key => $valid_column) {
            $column_name = $valid_column['column_name'];
            foreach ($results as $key => $result) {
                unlink("./uploads/" . $result[$column_name]);
            }
        }

        $db->query($sql3);
    }

    private function getSchema($table, $results, $allowed_columns)
    {
        $schema = [
            "type" => "object",
            "title" => ucfirst($table),
            "properties" => []
        ];

        foreach ($results as $index => $result) {

            if (!in_array($result['column_name'], $allowed_columns)) {
                continue;
            }

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
                case 'file':
                    $property = [
                        'type' => 'file',
                        'title' => $result['column_title'],
                        'file' => true,
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

    private function getForm($results, $button_title, $allowed_columns)
    {
        $form = [];
        $tabs = [];

        foreach ($results as $index => $result) {

            if (!in_array($result['column_name'], $allowed_columns)) {
                continue;
            }

            $field = [];
            $extraField = [];

            switch ($result['form_type']) {
                case "file":
                    $field = [
                        'key' => $result['column_name'],
                        'file' => []
                    ];
                    $extraField = [
                        [
                            "id" => "file-display",
                            "type" => "section"
                        ],
                        [
                            "id" => "image-display",
                            "type" => "section"
                        ]
                    ];
                    break;
                case "file-multiple":

                    $db = db_connect();
                    $file_types = [];

                    foreach (json_decode($result["accepted_files"]) as $index => $id) {
                        $sql = 'SELECT file_type FROM public.file_type_metadata WHERE id = ' . $id;
                        $file_type = $db->query($sql)->getResultArray();

                        array_push($file_types, $file_type[0]['file_type']);
                    }


                    $field = [
                        'key' => $result['column_name'],
                        'accept' => implode(',', $file_types),
                        'file' => [
                                "multiple" => true
                            ]
                    ];
                    $extraField = [
                        [
                            "id" => "file-display",
                            "type" => "section"
                        ],
                        [
                            "id" => "image-display",
                            "type" => "section"
                        ]
                    ];
                    break;
                case "image":
                    $field = [
                        'key' => $result['column_name'],
                        'accept' => '.png,.jpg',
                        'image' => []
                    ];
                    $extraField = [
                        [
                            "id" => "file-display",
                            "type" => "section"
                        ],
                        [
                            "id" => "image-display",
                            "type" => "section"
                        ]
                    ];
                    break;
                case "image-multiple":
                    $field = [
                        'key' => $result['column_name'],
                        'accept' => '.png,.jpg',
                        'image' => [
                                'multiple' => true
                            ]
                    ];
                    $extraField = [
                        [
                            "id" => "file-display",
                            "type" => "section"
                        ],
                        [
                            "id" => "image-display",
                            "type" => "section"
                        ]
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
                    array_push($form, $extraField[0], $extraField[1]);
                }
            } else {

                if (!array_key_exists($result["form_tab"], $tabs)) {
                    $tabs[$result['form_tab']] = [];
                }

                array_push($tabs[$result['form_tab']], $field);

                if ($extraField) {
                    array_push($tabs[$result['form_tab']], $extraField[0], $extraField[1]);
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

            // log_message("debug", json_encode($fieldset));

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

    private function getAllowedColumns($table_name)
    {
        $db = db_connect();

        $column_sql = "SELECT column_name, required FROM public.form_metadata WHERE table_name = '" . $table_name . "' ORDER BY order_position ASC;";
        $columns_entries = $db->query($column_sql)->getResultArray();

        $auth = service('auth');
        $user = $auth->user();

        $filter = new FormFilter();

        $allowed = [];

        foreach ($columns_entries as $j => $column_entry) {
            $column_metadata = $db->query("SELECT required_permissions FROM public.column_metadata WHERE column_name = '" . $column_entry["column_name"] . "';")->getResultArray()[0];

            if ($column_entry["required"] == "t" || !$filter->columnPremissionDenied($column_metadata["required_permissions"], $user)) {
                array_push($allowed, $column_entry['column_name']);
            }
        }

        return $allowed;
    }

    private function getLinks($results, $type, $allowed_columns, $id = null)
    {
        $t_links = [];
        foreach ($results as $index => $result) {

            if (!in_array($result['column_name'], $allowed_columns)) {
                continue;
            }

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

    private function getJoins($results, $allowed_columns)
    {
        $joins = [];
        foreach ($results as $key => $result) {

            if (!in_array($result['column_name'], $allowed_columns)) {
                continue;
            }

            if ($result["to_foreign"] == "t") {
                array_push($joins, [
                    "table" => $result["f_table"],
                    "key" => $result["f_primary_key"]
                ]);
            }
        }
        return $joins;
    }

    private function premissionDenied($table_name, $type, $index = -1)
    {
        $db = db_connect();
        $table = $db->query("SELECT * FROM public.table_metadata WHERE table_name = '" . $table_name . "';")->getResultArray()[0];

        $filter = new FormFilter();

        $auth = service('auth');
        $user = $auth->user();

        $premissions = $filter->getPremissions($table_name, $user);

        if ($premissions[$type]) {
            return false;
        }

        if ($index != -1) {
            $row = $db->query("SELECT created_user_id FROM public." . $table_name . " WHERE id = " . $index . ";")->getResultArray()[0];

            if ($premissions["show_created"] && $type == "show" && $user->id == $row["created_user_id"]) {
                return false;
                // } else if ($premissions["add_created"] && $type == "add" && $user->id == $row["created_user_id"]) {
                //     return false;
            } else if ($premissions["edit_created"] && $type == "edit" && $user->id == $row["created_user_id"]) {
                return false;
            }
        }

        return true; // User doesn't have permission
    }
}
