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
    public function fetch($table, $column)
    {
        $db = db_connect();

        $result = $db->query('SELECT id, ' . $column . ' AS item FROM public.' . $table . ';')->getResultArray();

        return json_encode($result);
    }

    public function fetchWhere($table, $column, $target, $value)
    {
        $db = db_connect();

        $result = $db->query('SELECT id, ' . $column . ' AS item FROM public.' . $table . ' WHERE ' . $target . " = '" . $value . "';")->getResultArray();

        return json_encode($result);
    }

    public function add($table)
    {
        $formModel = new FormModel();
        $results = $formModel->getForm($table);

        $formFilter = new FormFilter();
        $allowed_columns = $formFilter->getAllowedColumns($table);

        $schema = $this->getSchema($table, $results, $allowed_columns);
        $form = $this->getForm($results, "Create", $allowed_columns);

        $data = [
            'name' => $table,
            'message' => "",
            'schema' => json_encode($schema),
            'form' => json_encode($form),
            'link' => "http://$_SERVER[HTTP_HOST]/forms/" . $table . "/add",
            'type' => 'post',
            'values' => '{}'
        ];

        return view("form", $data);
    }
    public function create($table_name)
    {
        $files = $this->request->getFiles();
        $post = json_decode($this->request->getPost()["data"], true);

        $formModel = new FormModel();
        $id = $formModel->insert($table_name, $post, $files);

        if ($table_name == "table_metadata") {
            $tableController = new TableModel();
            $tableController->addTable($post[0]["value"]);

        } else if ($table_name == "column_metadata") {
            $tableController = new TableModel();
            $tableController->addColumn($id);
        }

        return json_encode(['id' => $id, 'message' => "Entry created succsessfully."]);
    }

    public function edit($table, $index, $column)
    {
        $formModel = new FormModel();
        $template = $formModel->getForm($table);

        $formFilter = new FormFilter();
        $allowed_columns = $formFilter->getAllowedColumns($table);

        $schema = $this->getSchema($table, $template, $allowed_columns);
        $form = $this->getForm($template, "Save", $allowed_columns);

        $values = $formModel->fetch($table, $column, $index);

        foreach ($values as $key => $value) {
            $decoded = json_decode($value, true);
            if ($decoded != null) {
                $values[$key] = json_decode($value, true);
            }
        }

        $data = [
            'name' => $table,
            'message' => "",
            'schema' => json_encode($schema),
            'form' => json_encode($form),
            'link' => "http://$_SERVER[HTTP_HOST]/forms/" . $table . "/edit/" . $index . "/" . $column,
            'type' => 'post',
            'values' => json_encode($values)
        ];

        return view("form", $data);
    }

    public function update($table, $index, $column)
    {
        $files = $this->request->getFiles();
        $post = json_decode($this->request->getPost()["data"], true);

        $formModel = new FormModel();

        $row = $formModel->fetch($table, $column, $index);
        $formModel->update($table, $column, $index, $post, $files);

        if ($table == "table_metadata") {
            $tableModel = new TableModel();
            $tableModel->alterTable($row["table_name"], $post[0]["table_name"]);

        } else if ($table == "column_metadata") {
            $tableModel = new TableModel();
            $tableModel->alterColumn($index, $row);
        }

        return json_encode(['id' => $index, 'message' => "Entry updated succsessfully."]);
    }

    public function destroy($name, $index, $column)
    {
        $formModel = new FormModel();

        $columns = $formModel->getForm($name);
        $file_columns = [];

        foreach ($columns as $col) {
            if ($col["schema_type"] == "file") {
                array_push($file_columns, $col["column_name"]);
            }
        }

        log_message("debug", json_encode($file_columns));

        $row = $formModel->fetch($name, $column, $index);

        $formModel->delete($name, $column, $index, $file_columns);

        if ($name == "table_metadata") {

            $tableModel = new TableModel();
            $tableModel->deleteTable($row["table_name"]);

        } else if ($name == "column_metadata") {

            $tableModel = new TableModel();
            $tableModel->deleteColumn($row);

        }

        $data = [
            'name' => $name,
            'message' => "Entry was deleted succsessfully.",
            'schema' => '{}',
            'form' => '{}',
            'link' => '',
            'type' => 'get',
            'values' => '{}'
        ];

        return view("form", $data);
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

                    if ($result["accepted_files"] != null) {
                        foreach (json_decode($result["accepted_files"]) as $index => $id) {
                            $sql = 'SELECT file_type FROM public.file_type_metadata WHERE id = ' . $id;
                            $file_type = $db->query($sql)->getResultArray();

                            array_push($file_types, $file_type[0]['file_type']);
                        }
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
}
