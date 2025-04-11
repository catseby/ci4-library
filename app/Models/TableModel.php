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
    }

    public function deleteTable($table_name) {
        $sql = "DROP TABLE " . $table_name  .";";
        $this->db->query($sql);

        $sql2 = "DELETE FROM public.column_metadata WHERE table_name = '" . $table_name . "';";
        $this->db->query($sql2);
    }

    public function addColumn($table_name, $column) {

    }

    public function alterColumn($table_name, $column) {

    }

    public function removeColumn($table_name, $column) {

    }

}
