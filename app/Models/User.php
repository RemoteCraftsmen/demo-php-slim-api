<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'email',
        'password',
        'username',
        'first_name',
        'last_name'
    ];

    protected $hidden = ['password'];

    public function todos()
    {
        return $this->hasMany(Todo::class);
    }
}
