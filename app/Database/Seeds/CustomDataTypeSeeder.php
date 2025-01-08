<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CustomDataTypeSeeder extends Seeder
{
    protected $datatypes = [
        ['name' => 'image', 'refrences' => 'VARCHAR(255)'],
        ['name' => 'select_list', 'refrences' => 'JSONB'],
        ['name' => 'varchar_array', 'refrences' => 'JSONB']
    ];

    protected $affected_columns = [
        ['table' => 'images', 'column' => 'image', 'datatype' => 'image']
    ];


    public function run()
    {
        $db = db_connect();

        foreach ($this->datatypes as $datatype) {
            $sql1 = 'DROP DOMAIN IF EXISTS ' . $datatype['name'] . ' CASCADE;';
            $sql2 = 'CREATE DOMAIN ' . $datatype['name'] . ' AS ' . $datatype['refrences'] . ';';
            $db->query($sql1);
            $db->query($sql2);
        }

        foreach ($this->affected_columns as $affected) {
            $sql = 'ALTER TABLE ' . $affected['table'] . ' ALTER COLUMN ' . $affected['column'] . ' TYPE ' . $affected['datatype'] . ';';
            $db->query($sql);
        }
    }
}
