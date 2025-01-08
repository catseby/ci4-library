<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FormTemplate extends Migration
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
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'schema' => [
                'type' => "JSONB"
            ],
            'form' => [
                'type' => "JSONB"
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('form_templates');
    }

    public function down()
    {
        $this->forge->dropTable('form_templates');
    }
}
