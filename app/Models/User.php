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
use Illuminate\Notifications\Notification;

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

    public function routeNotificationForSlack(Notification $notification): string
    {
        return env('SLACK_TASKS_WEBHOOK_URL');
    }

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
        return $this->belongsToMany(Task::class);
    }

    // static methods

    public static function getUsersWithTaskCount($org_id){
        return User::verified()->with('teams', 'tasks')
                ->withCount(['tasks as assigned_tasks_count' => function ($query) {
                    $query->where('tasks.status', 'assigned');
                }])
                ->withCount(['tasks as accepted_tasks_count' => function ($query) {
                    $query->where('tasks.status', 'accepted');
                }])
                ->withCount(['tasks as in_progress_tasks_count' => function ($query) {
                    $query->where('tasks.status', 'in_progress');
                }])
                ->withCount(['tasks as in_review_tasks_count' => function ($query) {
                    $query->where('tasks.status', 'in_review');
                }])
                ->withCount(['tasks as completed_tasks_count' => function ($query) {
                    $query->where('tasks.status', 'completed');
                }])
                ->where('org_id', $org_id)
                ->get();
    }

    // scopes

    public function scopeVerified($query){
        return $query->where('is_verified', 1);
    }
}
