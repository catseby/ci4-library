<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FormTemplateModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\FormModel;

class FormController extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $table_sql = 'SELECT table_name FROM public.table_metadata;';
        $table_names = $db->query($table_sql)->getResultArray();

        $data = [];

        foreach ($table_names as $i => $table_name_row) {
            $table_name = $table_name_row['table_name'];

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

            $sql = 'SELECT ' . implode(", ", $select_values) . ' FROM ' . $table_name . ' ' . $alias . ' ' . implode(" ", $select_joins) . ' GROUP BY ' . implode(", ", $select_groups) . ' ORDER BY ' . $alias . '.id ASC;';
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
            $data['tables'][$table_name]['data'] = $values;
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
        ;

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

        // $asc = ($asc == null || $asc['dir'] == 'asc') ? "ASC" : "DESC";
        // $offset = ($offset == null) ? 0 : $offset;
        // $limit = ($limit == null) ? 'NULL' : $limit;

        log_message("debug", json_encode($this->request->getPost()));
        // log_message("info", json_encode($this->request->getPost()));


        // $offset = 0;
        // if ($limit != "NULL") {
        //     $offset = ((int) $pagination - 1) * (int) $limit;
        // }

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
        ;

        $searchSql = " WHERE to_jsonb(". $alias . ")::text ILIKE '%" . $searchValue . "%'";

        $sql = 'SELECT ' . implode(", ", $select_values) . ' FROM ' . $table_name . ' ' . $alias . ' ' . implode(" ", $select_joins) . $searchSql . ' GROUP BY ' . implode(", ", $select_groups) . ' ORDER BY ' . $alias . '.' . $column . ' ' . $asc . ' LIMIT ' . $limit . ' OFFSET ' . $offset . ';';
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
        $data["recordsTotal"] = count($values);
        $data['draw'] = intval($draw);

        $count = $db->query("SELECT count(*) as count FROM public." . $table_name)->getResultArray();

        $data["recordsFiltered"] = $count[0]["count"];

        // $data['tables'] = json_encode($data['tables']);

        log_message('debug', json_encode($data));

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

    public function create($name)
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

        $joins = $this->getJoins($template);


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
            }


            $sql = 'UPDATE public.' . $name . ' SET ' . implode(',', $keys) . ' WHERE ' . $column . ' = ' . $index;
            log_message("debug", json_encode($data));
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
            'links' => '{}',
            'type' => 'get',
            'values' => '{}'
        ];

        return view("form_test", $data);

    }

    public function destroy_with_files($name, $index, $column)
    {
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

    private function getForm($results, $button_title)
    {
        $form = [];
        $tabs = [];

        foreach ($results as $index => $result) {

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

    private function getJoins($results)
    {
        $joins = [];
        foreach ($results as $key => $result) {
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
