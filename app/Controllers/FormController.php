<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FormTemplateModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\FormModel;
use CodeIgniter\Shield\Authentication\Auth;
use CodeIgniter\Shield\Exceptions\AccessDeniedException;

class FormController extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $table_sql = 'SELECT * FROM public.table_metadata;';
        $table_names = $db->query($table_sql)->getResultArray();

        $data = [];

        foreach ($table_names as $i => $table_name_row) {

            $table_name = $table_name_row['table_name'];

            if ($this->premissionDenied($table_name, 'show_roles')) {
                continue;
            }

            $premissions = $this->getPremissions($table_name);

            $table_limit = intval($table_name_row['maximum_data']);

            $column_sql = "SELECT column_name FROM public.column_metadata WHERE table_name = '" . $table_name . "' ORDER BY id ASC;";
            $columns_entries = $db->query($column_sql)->getResultArray();

            $countArray = $db->query("SELECT count(*) as count FROM public." . $table_name)->getResultArray();
            $count = intval($countArray[0]["count"]);

            $column_names = [["data" => "id", "title" => "id"]];
            foreach ($columns_entries as $j => $column_entry) {
                if ($this->columnPremissionDenied($table_name, $column_entry['column_name'])) {
                    continue;
                }
                array_push($column_names, ["data" => $column_entry['column_name'], "title" => $column_entry['column_name']]);
            }

            array_push($column_names, ["data" => "data_table_tools", "title" => "Add"]);

            $data['tables'][$table_name]["columns"] = $column_names;
            $data['tables'][$table_name]["server_side"] = ($count > $table_limit) ? true : false;
            $data['tables'][$table_name]['add'] = $premissions["add_roles"];
            $data['tables'][$table_name]['edit'] = $premissions["edit_roles"];
        }

        $data['tables'] = json_encode($data['tables']);

        return view('forms_datatables', $data);
    }

    public function fetchAll($table, $column, $limit, $asc, $pagination)
    {
        $db = db_connect();

        $offset = 0;
        if ($limit != "NULL") {
            $offset = ((int) $pagination - 1) * (int) $limit;
        }

        $data = [];

        $table_name = $table;


        $column_sql = "SELECT * FROM public.column_metadata WHERE table_name = '" . $table_name . "' ORDER BY id ASC;";
        $columns_entries = $db->query($column_sql)->getResultArray();

        $alias_words = [];

        $alias_words = explode('_', $table_name);

        $alias = '';
        foreach ($alias_words as $word) {
            $alias .= strtoupper($word[0]);
        }

        $select_values = [];
        $select_joins = [];
        $select_groups = [];

        array_push($select_values, $alias . ".id");
        array_push($select_groups, $alias . ".id");

        foreach ($columns_entries as $j => $column_entry) {

            if ($column_entry['foreign_key'] != null) {
                $sub_alias = '';
                $sub_alias_words = [];
                $sub_alias_words = explode('_', $column_entry["foreign_table"]);

                foreach ($sub_alias_words as $word) {
                    $sub_alias .= strtoupper($word[0]);
                }

                $v = "JSON_AGG(" . $sub_alias . "." . $column_entry['foreign_column'] . ") AS " . $column_entry['column_name'];
                array_push($select_values, $v);

                $join = 'LEFT JOIN ' . $column_entry['foreign_table'] . ' ' . $sub_alias . ' ON ' . $alias . '.id = ' . $sub_alias . '.' . $column_entry['foreign_key'];

                if (in_array($join, $select_joins) == false) {
                    array_push($select_joins, $join);
                }

            } else if ($column_entry['foreign_table']) {

                $sub_alias = '';
                $sub_alias_words = [];
                $sub_alias_words = explode('_', $column_entry["foreign_table"]);

                foreach ($sub_alias_words as $word) {
                    $sub_alias .= strtoupper($word[0]);
                }

                $v = "JSON_AGG(" . $sub_alias . "." . $column_entry['foreign_column'] . ") AS " . $column_entry['column_name'];
                array_push($select_values, $v);

                $join1 = "LEFT JOIN LATERAL jsonb_array_elements_text(" . $alias . "." . $column_entry["column_name"] . ") AS " . $column_entry["column_name"] . "_id ON TRUE";

                $join2 = 'LEFT JOIN ' . $column_entry['foreign_table'] . ' ' . $sub_alias . ' ON ' . $sub_alias . '.id = ' . $column_entry["column_name"] . "_id::INTEGER";

                if (in_array($join1, $select_joins) == false) {
                    array_push($select_joins, $join1);
                }

                if (in_array($join2, $select_joins) == false) {
                    array_push($select_joins, $join2);
                }

            } else {

                array_push($select_values, $alias . "." . $column_entry['column_name']);
                array_push($select_groups, $alias . "." . $column_entry['column_name']);
            }
        }

        $asc = ($asc === 'asc') ? 'ASC' : 'DESC';

        $sql = 'SELECT ' . implode(", ", $select_values) . ' FROM ' . $table_name . ' ' . $alias . ' ' . implode(" ", $select_joins) . ' GROUP BY ' . implode(", ", $select_groups) . ' ORDER BY ' . $alias . '.' . $column . ' ' . $asc . ' LIMIT ' . $limit . ' OFFSET ' . $offset . ';';
        $values = $db->query($sql)->getResultArray();


        foreach ($values as &$array) {
            foreach ($array as $key => &$value) {
                $decoded = json_decode($value, true);
                if ($decoded != null) {
                    $value = json_decode($value, true);
                }
            }
        }



        // $data['tables'][$table_name]['table'] = $table_name;
        $data["data"] = $values;

        $count = $db->query("SELECT count(*) as count FROM public." . $table_name)->getResultArray();

        $data["count"] = $count[0]["count"];

        // $data['tables'] = json_encode($data['tables']);

        log_message('debug', json_encode($data));

        return json_encode($data);
    }

    public function fetchDatatables($table)
    {
        $db = db_connect();

        $asc = $this->request->getPost('order')[0]['dir'] ?? 'asc';

        $columnIndex = $this->request->getPost('order')[0]['column'] ?? 0;
        $columnsArray = $this->request->getPost('columns');

        $columns = array_column($columnsArray, 'data');
        $column = $columns[intval($columnIndex)];

        $offset = $this->request->getPost('start') ?? 0;
        $limit = $this->request->getPost('length') ?? 'NULL';

        $searchValue = $this->request->getPost('search')['value'] ?? "";

        $table_name = $table;

        $draw = $this->request->getPost('draw');


        $data = [];

        $table_name = $table;


        $table_sql = "SELECT * FROM public.table_metadata WHERE table_name = '" . $table_name . "';";
        $table_entry = $db->query($table_sql)->getResultArray()[0];

        $column_sql = "SELECT * FROM public.column_metadata WHERE table_name = '" . $table_name . "' ORDER BY id ASC;";
        $columns_entries = $db->query($column_sql)->getResultArray();

        $alias_words = [];

        $alias_words = explode('_', $table_name);

        $alias = '';
        foreach ($alias_words as $word) {
            $alias .= strtoupper($word[0]);
        }

        $select_values = [];
        $select_joins = [];
        $select_groups = [];

        array_push($select_values, $alias . ".id");
        array_push($select_groups, $alias . ".id");

        foreach ($columns_entries as $j => $column_entry) {

            if ($this->columnPremissionDenied($table_name, $column_entry["column_name"])) {
                continue;
            }

            if ($column_entry['foreign_key'] != null) {
                $sub_alias = '';
                $sub_alias_words = [];
                $sub_alias_words = explode('_', $column_entry["foreign_table"]);

                foreach ($sub_alias_words as $word) {
                    $sub_alias .= strtoupper($word[0]);
                }

                $v = "JSON_AGG(" . $sub_alias . "." . $column_entry['foreign_column'] . ") AS " . $column_entry['column_name'];
                array_push($select_values, $v);

                $join = 'LEFT JOIN ' . $column_entry['foreign_table'] . ' ' . $sub_alias . ' ON ' . $alias . '.id = ' . $sub_alias . '.' . $column_entry['foreign_key'];

                if (in_array($join, $select_joins) == false) {
                    array_push($select_joins, $join);
                }

            } else if ($column_entry['foreign_table']) {

                $sub_alias = '';
                $sub_alias_words = [];
                $sub_alias_words = explode('_', $column_entry["foreign_table"]);

                foreach ($sub_alias_words as $word) {
                    $sub_alias .= strtoupper($word[0]);
                }

                $v = "JSON_AGG(" . $sub_alias . "." . $column_entry['foreign_column'] . ") AS " . $column_entry['column_name'];
                array_push($select_values, $v);

                $join1 = "LEFT JOIN LATERAL jsonb_array_elements_text(" . $alias . "." . $column_entry["column_name"] . ") AS " . $column_entry["column_name"] . "_id ON TRUE";

                $join2 = 'LEFT JOIN ' . $column_entry['foreign_table'] . ' ' . $sub_alias . ' ON ' . $sub_alias . '.id = ' . $column_entry["column_name"] . "_id::INTEGER";

                if (in_array($join1, $select_joins) == false) {
                    array_push($select_joins, $join1);
                }

                if (in_array($join2, $select_joins) == false) {
                    array_push($select_joins, $join2);
                }

            } else {

                array_push($select_values, $alias . "." . $column_entry['column_name']);
                array_push($select_groups, $alias . "." . $column_entry['column_name']);
            }
        }

        $asc = ($asc === 'asc') ? 'ASC' : 'DESC';
        ;

        $searchSql = " WHERE to_jsonb(" . $alias . ")::text ILIKE '%" . $searchValue . "%'";

        $sql = 'SELECT ' . implode(", ", $select_values) . ' FROM ' . $table_name . ' ' . $alias . ' ' . implode(" ", $select_joins) . $searchSql . ' GROUP BY ' . implode(", ", $select_groups) . ' ORDER BY ' . $alias . '.' . $column . ' ' . $asc . ' LIMIT ' . $limit . ' OFFSET ' . $offset . ';';
        $values = $db->query($sql)->getResultArray();

        $values = $this->rlsFilter($values, $table_entry["rls_level"], $table_name);

        foreach ($values as &$array) {
            foreach ($array as $key => &$value) {
                $decoded = json_decode($value, true);
                if ($decoded != null) {
                    $value = json_decode($value, true);
                }
            }
        }




        // $data['tables'][$table_name]['table'] = $table_name;
        $data["data"] = $values;
        $data["recordsTotal"] = count($values);
        $data['draw'] = intval($draw);

        $count = $db->query("SELECT count(*) as count FROM public." . $table_name)->getResultArray();

        $data["recordsFiltered"] = $count[0]["count"];

        // $data['tables'] = json_encode($data['tables']);


        return $this->response->setJSON($data);
    }

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

    public function add($table)
    {

        if ($this->premissionDenied($table, 'add_roles')) {
            return $this->response->setStatusCode(403)->setBody('Access Denied');
        }

        $formModel = new FormModel();
        $results = $formModel->getForm($table);

        $allowed_columns = $this->getAllowedColumns($table);
        $schema = $this->getSchema($table, $results, $allowed_columns);
        $form = $this->getForm($results, "Create", $allowed_columns);
        $links = $this->getLinks($results, "add", $allowed_columns);

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

    public function create($name)
    {

        if ($this->premissionDenied($name, 'add_roles')) {
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
        }

        log_message("debug", $id);
        return json_encode(['id' => $id]);
    }

    public function edit($table, $index, $column)
    {
        if ($this->premissionDenied($table, 'edit_roles')) {
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

        // log_message("debug", $sql);
        // log_message("debug", json_encode($result));

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
        if ($this->premissionDenied($name, 'edit_roles')) {
            return $this->response->setStatusCode(403)->setBody('Access Denied');
        }


        $files = $this->request->getFiles();
        $post = $this->request->getPost();

        log_message("debug", json_encode($post));

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

                $query = 'INSERT INTO public.' . $name . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_fill(0, count($data), '?')) . ');';
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
                // else if ($value == 'true') {
                //     $data[$key] = true;
                // }
                // else if ($value == 'false') {
                //     $data[$key] = false;
                // }
                // $keys[] = $key . " = ?";

                $user = auth()->user();
                $userId = $user->id ?? null;
                $timestamp = date('Y-m-d H:i:s');

                $data['updated_user_id'] = $userId;
                $data['updated_at'] = $timestamp;

                $keys[] = 'updated_user_id = ?';
                $keys[] = 'updated_at = ?';

            }


            $sql = 'UPDATE public.' . $name . ' SET ' . implode(',', $keys) . ' WHERE ' . $column . ' = ' . $index;
            log_message("debug", json_encode($data));
            $db->query($sql, array_values($data));

        }

        return json_encode(['id' => $index]);
    }

    public function destroy($name, $index, $column)
    {

        if ($this->premissionDenied($name, 'edit_roles')) {
            return $this->response->setStatusCode(403)->setBody('Access Denied');
        }


        $db = db_connect();

        $query = 'DELETE FROM public.' . $name . ' WHERE ' . $column . ' = ' . $index;
        $db->query($query);

        $data = [
            'name' => "Entry from " . $name . ' was deleted.',
            'schema' => '{}',
            'form' => '{}',
            'links' => '{}',
            'type' => 'get',
            'values' => '{}'
        ];

        return view("form_test", $data);

    }

    public function destroy_with_files($name, $index, $column)
    {

        if ($this->premissionDenied($name, 'edit_roles')) {
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
            log_message("debug", $column_name);
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


        $allowed = [];

        foreach ($columns_entries as $j => $column_entry) {
            if ($column_entry["required"] == "t" || !$this->columnPremissionDenied($table_name, $column_entry['column_name'])) {
                array_push($allowed, $column_entry['column_name']);
            }
        }

        log_message("debug", json_encode($allowed));

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



    private function premissionDenied($table, $type)
    {

        $db = db_connect();

        $sql = "SELECT 
    jsonb_agg(DISTINCT ag.group_name) AS add_roles,
    jsonb_agg(DISTINCT eg.group_name) AS edit_roles,
    jsonb_agg(DISTINCT sg.group_name) AS show_roles
FROM table_metadata t

JOIN LATERAL jsonb_array_elements(t.add_roles) AS add_role_name ON true
JOIN auth_groups_metadata ag ON ag.id = (add_role_name::text)::int

JOIN LATERAL jsonb_array_elements(t.edit_roles) AS edit_role_name ON true
JOIN auth_groups_metadata eg ON eg.id = (edit_role_name::text)::int

JOIN LATERAL jsonb_array_elements(t.show_roles) AS show_role_name ON true
JOIN auth_groups_metadata sg ON sg.id = (show_role_name::text)::int

WHERE t.table_name = '" . $table . "';";


        $result = $db->query($sql)->getResultArray();

        $auth = service('auth');
        $user = $auth->user();

        if ($result[0][$type] == null) {
            return false;
        }


        $decodedArray = json_decode($result[0][$type], true); // Decode JSON as an array

        foreach ($decodedArray as $group) {
            if ($user->inGroup($group)) {
                return false; // User has permission
            }
        }


        return true; // User doesn't have permission
    }

    private function columnPremissionDenied($table, $column)
    {
        $db = db_connect();

        $sql = "SELECT 
    jsonb_agg(DISTINCT ag.group_name) AS roles

FROM column_metadata t

JOIN LATERAL jsonb_array_elements(t.allowed_roles) AS add_role_name ON true
JOIN auth_groups_metadata ag ON ag.id = (add_role_name::text)::int


WHERE t.table_name = '" . $table . "' AND t.column_name = '" . $column . "';";


        $result = $db->query($sql)->getResultArray();

        $auth = service('auth');
        $user = $auth->user();

        if ($result[0]['roles'] == null) {
            return false;
        }

        $decodedArray = json_decode($result[0]['roles'], true); // Decode JSON as an array

        // log_message("debug", json_encode($result));


        foreach ($decodedArray as $group) {
            if ($user->inGroup($group)) {
                return false; // User has permission
            }
        }

        return true; // User doesn't have permission
    }

    private function getPremissions($table)
    {
        $db = db_connect();

        $sql = "SELECT 
    jsonb_agg(DISTINCT ag.group_name) AS add_roles,
    jsonb_agg(DISTINCT eg.group_name) AS edit_roles,
    jsonb_agg(DISTINCT sg.group_name) AS show_roles
FROM table_metadata t

JOIN LATERAL jsonb_array_elements(t.add_roles) AS add_role_name ON true
JOIN auth_groups_metadata ag ON ag.id = (add_role_name::text)::int

JOIN LATERAL jsonb_array_elements(t.edit_roles) AS edit_role_name ON true
JOIN auth_groups_metadata eg ON eg.id = (edit_role_name::text)::int

JOIN LATERAL jsonb_array_elements(t.show_roles) AS show_role_name ON true
JOIN auth_groups_metadata sg ON sg.id = (show_role_name::text)::int

WHERE t.table_name = '" . $table . "';";


        $result = $db->query($sql)->getResultArray();

        $auth = service('auth');
        $user = $auth->user();

        $premissions = [];

        foreach ($result[0] as $key => $roles) {

            if ($roles == null) {
                $premissions[$key] = true;
                continue;
            }

            $decodedArray = json_decode($roles, true); // Decode JSON as an array
            $premissions[$key] = false;

            foreach ($decodedArray as $group) {
                if ($user->inGroup($group)) {
                    $premissions[$key] = true;
                }
            }
        }

        return $premissions;
    }

    private function rlsFilter($rows, $rls_level, $table)
    {
        $auth = service('auth');
        $user = $auth->user();
        $canEdit = true;

        switch ($rls_level) {

            case 1:
                $filtered = [];
                foreach ($rows as $index => $row) {
                    if ($row['created_user_id'] == $user->id) {
                        array_push($filtered, $row);
                    }
                }
                $rows = $filtered;
                break;

            case 2:
                $db = db_connect();

                $uSql = "SELECT * FROM public.auth_groups_users WHERE user_id = " . $user->id . ";";
                $group = $db->query($uSql)->getResultArray()[0]['group'];

                $sql = "SELECT * FROM public.auth_groups_metadata WHERE group_name = '" . $group . "';";
                $result = $db->query($sql)->getResultArray()[0];

                switch ($result['access_level']) {
                    case 1:
                        break;
                    case 2:
                        $canEdit = false;
                        break;
                    default:
                        return [];
                }
                break;
            default:
                break;
        }

        foreach ($rows as $index => &$row) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $prefix =  $protocol . "://" . $_SERVER['HTTP_HOST'] . "/forms/" . $table;
            $edit_url = $prefix . "/edit/" . $rows[$index]["id"];
            $delete_url = $prefix . "/delete/" . $rows[$index]["id"];
            $row["data_table_tools"] = $canEdit ? '<a href="' . $edit_url . '">Edit</a><br><a href="' . $delete_url . '">Delete</a>' : "";
        }

        return $rows;
    }


}
