<?php

use App\DB\Migrations\Migration;

class Todo extends Migration
{
    public function up(){
        $users = $this->table('todos');
        $users->addColumn('name', 'string', ['limit' => 200])
            ->addColumn('completed', 'boolean' ,['default' => false])
            ->addColumn('user_id', 'integer', ['null' => true])
            ->addColumn('creator_id', 'integer', ['null' => true])
            ->addColumn('created_at', 'datetime', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->save();
    }

    public function down(){
        $this->table('todos')->drop()->save();
    }
}
