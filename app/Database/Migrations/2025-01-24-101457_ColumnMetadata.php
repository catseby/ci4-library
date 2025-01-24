<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ColumnMetadata extends Migration
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
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('column_metadata');
    }

    public function down()
    {
        $this->forge->dropTable('column_metadata');
    }
}
