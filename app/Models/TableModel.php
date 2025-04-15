<?php

namespace App\Models;

class TableModel
{

    protected $db;

    public function __construct() {
        $this->db  = db_connect();
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

    public function alterTable($table_name, $new_name) {
        $sql = "ALTER TABLE " . $table_name . " RENAME TO " . $new_name . ";";
        $this->db->query($sql);

        $sql2 = "UPDATE public.column_metadata SET table_name = '" . $new_name . "' WHERE table_name = '" . $table_name . "';";
        $this->db->query($sql2);

        $sql3 = "UPDATE public.form_metadata SET table_name = '" . $new_name . "' WHERE table_name = '" . $table_name . "';";
        $this->db->query($sql3);
    }

    public function deleteTable($table_name) {
        $sql = "DROP TABLE " . $table_name  .";";
        $this->db->query($sql);

        $sql2 = "DELETE FROM public.column_metadata WHERE table_name = '" . $table_name . "';";
        $this->db->query($sql2);

        $sql3 = "DELETE FROM public.form_metadata WHERE table_name = '" . $table_name . "';";
        $this->db->query($sql3);
    }

    public function addColumn($table_name, $column) {
        $existing_columns = $this->db->query("SELECT * FROM public.column_metadata WHERE table_name = '" . $table_name . "_new' ORDER BY ordinal_position;")->getResultArray();
        $new_sql = "CREATE TABLE " . $table_name . "_new (id SERIAL PRIMARY KEY, ";
        $values = [];

        foreach($existing_columns as $index => $existing_column) {
            $s = $existing_column["column_name"] . " ";
            $s .= str_replace("()", "(" . $existing_column["max_char_length"] . ")", $existing_column["data_type"]) . " ";
            $s .= ($existing_column["required"] == "t") ? "NOT NULL, " : ", ";

            array_push($values, $existing_column["column_name"]);

            $new_sql .= $s;
        }

        $ns = $column["column_name"] . " ";
        $ns .= str_replace("()", "(" . $column["max_char_length"] . ")", $column["data_type"]) . " ";
        $ns .= ($column["required"] == "t") ? "NOT NULL, " : ", ";

        // array_push($values, $column["column_name"]);

        $new_sql .= $ns;

        $new_sql .= "created_at TIMESTAMP DEFAULT NOW(), updated_at TIMESTAMP DEFAULT NOW(), created_user_id INT, updated_user_id INT);";

        array_push($values, "created_at", "updated_at", "created_user_id", "updated_user_id");

        $this->db->query($new_sql);

        $this->db->query("INSERT INTO " . $table_name . "_new (" . implode(", ",$values) . ") SELECT " . implode(", ",$values) . " FROM " . $table_name . ";");

        $this->db->query("DROP TABLE " . $table_name . ";");

        $this->db->query("ALTER TABLE " . $table_name . "_new RENAME TO " . $table_name . ";");
    }

    public function alterColumn($table_name, $column) {

    }

    public function removeColumn($table_name, $column) {

    }

}
