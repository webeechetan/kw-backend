<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    function setNameAttribute($val){
        $this->attributes['name'] = ucfirst($val);
    }

    public function getImageAttribute($value){
        if($value){
            return asset('clients/'.$value);
        }
        return asset('clients/default.png');
    }

    public function projects(){
        return $this->hasMany(Project::class);
    }
}
