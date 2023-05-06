<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Team;
use App\Models\Project;
use App\Models\Task;

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
        'created_at',
        'updated_at',
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

    public function tasks(){
        return $this->belongsToMany(Task::class)->withPivot('status');
    }

    // static methods

    public static function getUsersWithTaskCount($org_id){
        return User::with('teams', 'tasks')
                ->withCount(['tasks as pending_tasks_count' => function ($query) {
                    $query->where('tasks.status', 'pending');
                }])
                ->withCount(['tasks as completed_tasks_count' => function ($query) {
                    $query->where('tasks.status', 'completed');
                }])
                ->withCount(['tasks as in_progress_tasks_count' => function ($query) {
                    $query->where('tasks.status', 'in-progress');
                }])
                ->where('org_id', $org_id)
                ->get();
    }

}
