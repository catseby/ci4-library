<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FromMetadata extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'table_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'column_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'column_title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'inline_title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'schema_type' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'form_type' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'form_tab' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'order_position' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true
            ],
            'accepted_files' => [
                'type' => 'JSONB',
                'null' => true
            ],
            'ref_table_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'ref_column_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'required' => [
                'type' => 'BOOL',
                'default' => false
            ],
            'hidden' => [
                'type' => 'BOOL',
                'default' => false
            ],
            'dynamic_fetch' => [
                'type' => 'BOOL',
                'default' => false
            ],
            'ref_fetch_key' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'ref_fetch_target' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'to_foreign' => [
                'type' => 'BOOL',
                'default' => false
            ],
            'f_table' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'f_primary_key' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('form_metadata');
    }

    public function down()
    {
        $this->forge->dropTable('form_metadata');
    }
}
