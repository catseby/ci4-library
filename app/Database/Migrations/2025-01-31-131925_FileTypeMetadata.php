<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FileTypeMetadata extends Migration
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
            'file_type' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('file_type_metadata');
    }

    public function down()
    {
        $this->forge->dropTable('file_type_metadata');
    }
}
