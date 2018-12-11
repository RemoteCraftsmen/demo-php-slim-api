<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model{

    protected $fillable = [

        'email',
        'password',
        'username',
        'first_name',
        'last_name'
        ];

    public function todos()
    {
        return $this->hasMany('App\Models\Todo');
    }
}