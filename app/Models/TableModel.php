<?php

namespace App\Models;

class TableModel
{

    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function getTables()
    {
        return $this->db->query("SELECT * FROM public.table_metadata;")->getResultArray();
    }

    public function getColumns($table_name)
    {
        return $this->db->query("SELECT * FROM public.column_metadata WHERE table_name = '" . $table_name . "';")->getResultArray();
    }

    public function rowCount($table_name)
    {
        $count = $this->db->query("SELECT count(*) as count FROM public." . $table_name)->getRowArray();
        return intval($count["count"]);
    }

    public function fetch($table_name)
    {
        return $this->db->query("SELECT * FROM public.table_metadata WHERE table_name = '" . $table_name . "' LIMIT 1;")->getRowArray();
    }

    public function getValues($sql)
    {
        return $this->db->query($sql)->getResultArray();
    }

    public function addTable($table_name)
    {
        $sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
        id SERIAL PRIMARY KEY,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW(),
        created_user_id INT,
        updated_user_id INT);";

        $this->db->query($sql);
    }

    public function alterTable($table_name, $new_name)
    {
        $sql = "ALTER TABLE " . $table_name . " RENAME TO " . $new_name . ";";
        $this->db->query($sql);

        $sql2 = "UPDATE public.column_metadata SET table_name = '" . $new_name . "' WHERE table_name = '" . $table_name . "';";
        $this->db->query($sql2);

        $sql3 = "UPDATE public.form_metadata SET table_name = '" . $new_name . "' WHERE table_name = '" . $table_name . "';";
        $this->db->query($sql3);
    }

    public function deleteTable($table_name)
    {
        $sql = "DROP TABLE " . $table_name . ";";
        $this->db->query($sql);

        $sql2 = "DELETE FROM public.column_metadata WHERE table_name = '" . $table_name . "';";
        $this->db->query($sql2);

        $sql3 = "DELETE FROM public.form_metadata WHERE table_name = '" . $table_name . "';";
        $this->db->query($sql3);
    }

    public function addColumn($id)
    {
        $column = $this->db->query("SELECT * FROM column_metadata WHERE id = " . $id . " LIMIT 1;")->getRowArray();

        $ns = $column["column_name"] . " ";
        $ns .= str_replace("()", "(" . $column["max_char_length"] . ")", $column["data_type"]) . " ";
        $ns .= ($column["required"] == "t") ? "NOT NULL;" : ";";

        $sql = "ALTER TABLE " . $column["table_name"] . " ADD COLUMN " . $ns;

        $this->db->query($sql);
    }

    public function alterColumn($id, $old_column)
    {
        $column = $this->db->query("SELECT * FROM column_metadata WHERE id = " . $id . " LIMIT 1;")->getRowArray();

        $changes = [];

        if ($column["column_name"] != $old_column["column_name"]) {
            $rename = " RENAME COLUMN " . $old_column["column_name"] . " TO " . $column["column_name"];
            $this->db->query("ALTER TABLE " . $column["table_name"] . $rename);
        }

        if ($column["data_type"] != $old_column["data_type"] || $column["max_char_length"] != $old_column["max_char_length"]) {
            $type = "ALTER COLUMN " . $column["column_name"] . " TYPE " . str_replace("()", "(" . $column["max_char_length"] . ")", $column["data_type"]);
            $this->db->query("ALTER TABLE " . $column["table_name"] . $type);
        }

        if ($column["required"] != $old_column["required"]) {
            $required = ($column["required"] == "t") ? "ALTER COLUMN " . $column["column_name"] . " SET NOT NULL" : "ALTER COLUMN " . $column["column_name"] . " DROP NOT NULL";
            $this->db->query("ALTER TABLE " . $column["table_name"] . $required);
        }
    }

    public function deleteColumn($old_column)
    {
        $this->db->query("ALTER TABLE " . $old_column["table_name"] . " DROP COLUMN " . $old_column["column_name"]);
    }

}
