<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FormTemplateModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\FormModel;
use CodeIgniter\Shield\Authentication\Auth;
use CodeIgniter\Shield\Exceptions\AccessDeniedException;

class TableController extends BaseController
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

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $add_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/forms/" . $table_name . "/add";

            $add_link = $premissions["add_roles"] ? '<a href="' . $add_url . '">Add</a>' : "";

            array_push($column_names, ["data" => "data_table_tools", "title" => $add_link]);

            $data['tables'][$table_name]["columns"] = $column_names;
            $data['tables'][$table_name]["server_side"] = ($count > $table_limit) ? true : false;
            $data['tables'][$table_name]['add'] = $premissions["add_roles"];
            $data['tables'][$table_name]['edit'] = $premissions["edit_roles"];
        }

        $data['tables'] = json_encode($data['tables']);

        return view('datatables', $data);
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

    private function premissionDenied($table, $type, $index = -1)
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

        if ($index != -1) {
            $sql1 = "SELECT rls_level FROM public.table_metadata WHERE table_name = '" . $table . "';";
            $sql2 = "SELECT created_user_id FROM " . $table . " WHERE id = " . $index . ";";
            $row = $db->query($sql2)->getResultArray()[0];
            $table = $db->query($sql1)->getResultArray()[0];

            if (!$this->rlsAllowed($table['rls_level'], $user, $row)) {
                return true;
            }
        }


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
        $editBlacklist = [];

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
            case 3:
                $db = db_connect();

                $uSql = "SELECT * FROM public.auth_groups_users WHERE user_id = " . $user->id . ";";
                $group = $db->query($uSql)->getResultArray()[0]['group'];

                $sql = "SELECT * FROM public.auth_groups_metadata WHERE group_name = '" . $group . "';";
                $result = $db->query($sql)->getResultArray()[0];

                switch ($result['access_level']) {
                    case 1:
                        break;
                    default:
                        foreach ($rows as $index => $row) {
                            if ($row['created_user_id'] != $user->id) {
                                array_push($editBlacklist, $row["id"]);
                            }
                        }
                }
                break;
            default:
                break;
        }

        foreach ($rows as $index => &$row) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $prefix = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/forms/" . $table;
            $edit_url = $prefix . "/edit/" . $rows[$index]["id"];
            $delete_url = $prefix . "/delete/" . $rows[$index]["id"];
            $row["data_table_tools"] = ($canEdit && !in_array($row['id'], $editBlacklist)) ? '<a href="' . $edit_url . '">Edit</a><br><a href="' . $delete_url . '">Delete</a>' : "";
        }

        return $rows;
    }

    private function rlsAllowed($rls_level, $user, $row)
    {

        $db = db_connect();

        $uSql = "SELECT * FROM public.auth_groups_users WHERE user_id = " . $user->id . ";";
        $group = $db->query($uSql)->getResultArray()[0]['group'];

        $sql = "SELECT * FROM public.auth_groups_metadata WHERE group_name = '" . $group . "';";
        $result = $db->query($sql)->getResultArray()[0];


        switch ($rls_level) {
            case 0:
                return true;
            case 1:
                return ($row['created_user_id'] == $user->id);
            case 2:
                return ($result["access_level"] == 1);
            case 3:
                return ($row['created_user_id'] == $user->id);
        }
    }

}
