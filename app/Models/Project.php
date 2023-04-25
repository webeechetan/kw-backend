<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Team;
use App\Models\Client;

class Project extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function users(){
        return $this->belongsToMany(User::class);
    }

    public function teams(){
        return $this->belongsToMany(Team::class);
    }

    public function client(){
        return $this->belongsTo(Client::class);
    }
}
