<?php

use App\DB\Migrations\Migration;

class Users extends Migration
{
    public function up(){
        $users = $this->table('users');
        $users->addColumn('username', 'string', ['limit' => 30])
            ->addColumn('password', 'string')
            ->addColumn('email', 'string', ['limit' => 100])
            ->addColumn('first_name', 'string', ['limit' => 30])
            ->addColumn('last_name', 'string', ['limit' => 30])
            ->addColumn('created_at', 'datetime', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->save();
    }

    public function down(){
        $this->table('users')->drop()->save();
    }
}
