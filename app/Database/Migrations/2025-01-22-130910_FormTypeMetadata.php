<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FormTypeMetadata extends Migration
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
            'form_type' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('form_type_metadata');
    }

    public function down()
    {
        $this->forge->dropTable('form_type_metadata');
    }
}
