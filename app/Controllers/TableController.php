<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Filters\FormFilter;
use App\Models\FormTemplateModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TableModel;
use CodeIgniter\Shield\Authentication\Auth;
use CodeIgniter\Shield\Exceptions\AccessDeniedException;

class TableController extends BaseController
{
    public function index()
    {
        $tableModel = new TableModel();
        $filter = new FormFilter();

        $auth = service('auth');
        $user = $auth->user();

        $data = [];
        $tables = $tableModel->getTables();

        foreach ($tables as $i => $table) {

            $table_name = $table['table_name'];

            $premissions = $filter->getPremissions($table_name, $user);

            if ($premissions["fetch"] != true) {
                continue;
            }

            $table_limit = intval($table['maximum_data']);

            $columns = $tableModel->getColumns($table_name);
            $count = $tableModel->rowCount($table_name);

            $column_names = [["data" => "id", "title" => "id"]];
            foreach ($columns as $j => $column) {
                if ($filter->columnPremissionDenied($column["required_permissions"], $user)) {
                    continue;
                }
                array_push($column_names, ["data" => $column['column_name'], "title" => $column['column_name']]);
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

    public function fetch($table_name)
    {
        $tableModel = new TableModel();
        $filter = new FormFilter();

        $auth = service('auth');
        $user = $auth->user();

        $asc = $this->request->getPost('order')[0]['dir'] ?? 'asc';
        $columnIndex = $this->request->getPost('order')[0]['column'] ?? 0;
        $columnsArray = $this->request->getPost('columns');

        $columns = array_column($columnsArray, 'data');
        $column_name = $columns[intval($columnIndex)];

        $offset = $this->request->getPost('start') ?? 0;
        $limit = $this->request->getPost('length') ?? 'NULL';
        $searchValue = $this->request->getPost('search')['value'] ?? "";
        $draw = $this->request->getPost('draw');

        $data = [];

        // $table = $tableModel->fetch($table_name);

        $columns = $tableModel->getColumns($table_name);

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

        foreach ($columns as $j => $column) {

            if ($filter->columnPremissionDenied($column["required_permissions"], $user)) {
                continue;
            }

            if ($column['foreign_key'] != null) {
                $sub_alias = '';
                $sub_alias_words = [];
                $sub_alias_words = explode('_', $column["foreign_table"]);

                foreach ($sub_alias_words as $word) {
                    $sub_alias .= strtoupper($word[0]);
                }

                $v = "JSON_AGG(" . $sub_alias . "." . $column['foreign_column'] . ") AS " . $column['column_name'];
                array_push($select_values, $v);

                $join = 'LEFT JOIN ' . $column['foreign_table'] . ' ' . $sub_alias . ' ON ' . $alias . '.id = ' . $sub_alias . '.' . $column['foreign_key'];

                if (in_array($join, $select_joins) == false) {
                    array_push($select_joins, $join);
                }

            } else if ($column['foreign_table']) {

                $sub_alias = '';
                $sub_alias_words = [];
                $sub_alias_words = explode('_', $column["foreign_table"]);

                foreach ($sub_alias_words as $word) {
                    $sub_alias .= strtoupper($word[0]);
                }

                $v = "JSON_AGG(" . $sub_alias . "." . $column['foreign_column'] . ") AS " . $column['column_name'];
                array_push($select_values, $v);

                $join1 = "LEFT JOIN LATERAL jsonb_array_elements_text(" . $alias . "." . $column["column_name"] . ") AS " . $column["column_name"] . "_id ON TRUE";

                $join2 = 'LEFT JOIN ' . $column['foreign_table'] . ' ' . $sub_alias . ' ON ' . $sub_alias . '.id = ' . $column["column_name"] . "_id::INTEGER";

                if (in_array($join1, $select_joins) == false) {
                    array_push($select_joins, $join1);
                }

                if (in_array($join2, $select_joins) == false) {
                    array_push($select_joins, $join2);
                }

            } else {

                array_push($select_values, $alias . "." . $column['column_name']);
                array_push($select_groups, $alias . "." . $column['column_name']);
            }
        }

        $asc = ($asc === 'asc') ? 'ASC' : 'DESC';

        $searchSql = " WHERE to_jsonb(" . $alias . ")::text ILIKE '%" . $searchValue . "%'";

        $sql = 'SELECT ' . implode(", ", $select_values) . ' FROM ' . $table_name . ' ' . $alias . ' ' . implode(" ", $select_joins) . $searchSql . ' GROUP BY ' . implode(", ", $select_groups) . ' ORDER BY ' . $alias . '.' . $column_name . ' ' . $asc . ' LIMIT ' . $limit . ' OFFSET ' . $offset . ';';
        $values = $tableModel->getValues($sql);

        $values = $this->premissionFilter($values, $filter->getPremissions($table_name, $user), $user, $table_name);

        foreach ($values as &$array) {
            foreach ($array as $key => &$value) {
                $decoded = json_decode($value, true);
                if ($decoded != null) {
                    $value = json_decode($value, true);
                }
            }
        }

        $data["data"] = $values;
        $data["recordsTotal"] = count($values);
        $data['draw'] = intval($draw);

        $data["recordsFiltered"] = $tableModel->rowCount($table_name);

        return $this->response->setJSON($data);
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
