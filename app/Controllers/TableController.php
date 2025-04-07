<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Filters\FormFilter;
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

        $filter = new FormFilter();

        $auth = service('auth');
        $user = $auth->user();

        $data = [];

        foreach ($table_names as $i => $table_name_row) {

            $table_name = $table_name_row['table_name'];

            $premissions = $filter->getPremissions($table_name, $user);

            if ($premissions["fetch"] != true) {
                continue;
            }

            $table_limit = intval($table_name_row['maximum_data']);

            $column_sql = "SELECT * FROM public.column_metadata WHERE table_name = '" . $table_name . "' ORDER BY id ASC;";
            $columns_entries = $db->query($column_sql)->getResultArray();

            $countArray = $db->query("SELECT count(*) as count FROM public." . $table_name)->getResultArray();
            $count = intval($countArray[0]["count"]);

            $column_names = [["data" => "id", "title" => "id"]];
            foreach ($columns_entries as $j => $column_entry) {
                if ($filter->columnPremissionDenied($column_entry, $user)) {
                    continue;
                }
                array_push($column_names, ["data" => $column_entry['column_name'], "title" => $column_entry['column_name']]);
            }

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $add_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/forms/" . $table_name . "/add";

            $add_link = $premissions["add"] ? '<a href="' . $add_url . '">Add</a>' : "";

            array_push($column_names, ["data" => "data_table_tools", "title" => $add_link]);

            $data['tables'][$table_name]["columns"] = $column_names;
            $data['tables'][$table_name]["server_side"] = ($count > $table_limit) ? true : false;
            $data['tables'][$table_name]['add'] = $premissions["add"];
            $data['tables'][$table_name]['edit'] = $premissions["edit"];
        }

        $data['tables'] = json_encode($data['tables']);

        return view('datatables', $data);
    }

    public function fetch($table)
    {
        $filter = new FormFilter();

        $auth = service('auth');
        $user = $auth->user();

        $db = db_connect();

        $asc = $this->request->getPost('order')[0]['dir'] ?? 'asc';
        $columnIndex = $this->request->getPost('order')[0]['column'] ?? 0;
        $columnsArray = $this->request->getPost('columns');
        $columns = array_column($columnsArray, 'data');
        $column = $columns[intval($columnIndex)];
        $offset = $this->request->getPost('start') ?? 0;
        $limit = $this->request->getPost('length') ?? 'NULL';
        $searchValue = $this->request->getPost('search')['value'] ?? "";
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

            if ($filter->columnPremissionDenied($column_entry, $user)) {
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

        $values = $this->premissionFilter($values, $filter->getPremissions($table_name, $user), $user, $table_name);

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

    public function add(){
        return view("table_create.php");
    }

    public function update(){

    }

    public  function destroy() {
        
    }

    private function premissionFilter($rows, $premissions, $user, $table)
    {
        $filtered = [];

        foreach ($rows as $index => &$row) {

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $prefix = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/forms/" . $table;
            $edit_url = $prefix . "/edit/" . $rows[$index]["id"];
            $delete_url = $prefix . "/delete/" . $rows[$index]["id"];
            $row["data_table_tools"] = ($premissions["edit"] || ($premissions["edit_created"] && $row["created_user_id"] == $user->id)) ? '<a href="' . $edit_url . '">Edit</a><br><a href="' . $delete_url . '">Delete</a>' : "";

            if ($premissions["fetch"] || ($premissions["edit_created"] && $row["created_user_id"] == $user->id)) {
                array_push($filtered, $row);
            }
        }

        return $filtered;
    }

}
