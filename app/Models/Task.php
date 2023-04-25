<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Notifications\NewTaskAssign;

class Task extends Model
{
    use HasFactory, Notifiable;

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function users(){
        return $this->belongsToMany(User::class)->withPivot('status');
    }

    public function assignedBy(){
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
