<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TypeMetadata extends Migration
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
            'schema_type' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'form_type' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('type_metadata');
    }

    public function down()
    {
        $this->forge->dropTable('type_metadata');
    }
}
