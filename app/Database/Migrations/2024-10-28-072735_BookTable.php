<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BookTable extends Migration
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
            'isbn' => [
                'type' => 'INT',
                'constraint' => 13,
                'unique' => true,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'author' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'categories' => [
                'type' => 'JSONB',
            ],
            'tags' => [
                'type' => 'JSONB',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('books');
    }

    public function down()
    {
        $this->forge->dropTable('books');
    }
}
