<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Project;

class Team extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function getImageAttribute($val){
        return env('APP_URL').'/teams/'.$val;
    }

    public function setNameAttribute($val){
        $this->attributes['name'] = ucwords($val);
    }

    public function users(){
        return $this->belongsToMany(User::class);
    }

    public function projects(){
        return $this->belongsToMany(Project::class);
    }
}
