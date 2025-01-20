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
                'constraint' => 11
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
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('form_metadata');
    }

    public function down()
    {
        $this->forge->dropTable('form_metadata');
    }
}
