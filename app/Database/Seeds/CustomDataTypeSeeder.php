<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CustomDataTypeSeeder extends Seeder
{
    protected $domains = [
        ['name' => 'image', 'data_type' => 'VARCHAR(255)'],
        ['name' => 'select list', 'data_type' => 'JSONB'],
        ['name' => 'varchar array', 'data_type' => 'JSONB']
    ];

    protected $affected_columns = [
        ['table' => 'images', 'column' => 'image', 'domain' => 'image'],
        ['table' => 'books', 'column' => 'categories', 'domain' => 'select list']
    ];


    public function run()
    {
        $db = db_connect();

        foreach ($this->domains as $domain) {
            $sql1 = 'DROP DOMAIN IF EXISTS "' . $domain['name'] . '" CASCADE;';
            $sql2 = 'CREATE DOMAIN "' . $domain['name'] . '" AS ' . $domain['data_type'] . ';';
            $db->query($sql1);
            $db->query($sql2);
        }

        foreach ($this->affected_columns as $affected) {
            $sql = 'ALTER TABLE ' . $affected['table'] . ' ALTER COLUMN ' . $affected['column'] . ' TYPE "' . $affected['domain'] . '";';
            $db->query($sql);
        }
    }
}
