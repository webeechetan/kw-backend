<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Organization extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $hidden = ['password', 'created_at', 'updated_at'];


    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    public function users(){
        return $this->hasMany(User::class);
    }

    

}
