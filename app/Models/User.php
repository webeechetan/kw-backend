<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Team;
use App\Models\Project;

class User extends Authenticatable 
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getNameAttribute($value){
        return ucfirst($value);
    }

    public function getImageAttribute($value){
        if($value){
            return asset('members/'.$value);
        }
        return asset('members/default.png');
    }

    public function teams(){
        return $this->belongsToMany(Team::class);
    }

    public function projects(){
        return $this->belongsToMany(Project::class);
    }
    
}
