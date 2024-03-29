<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentController;

global $notfound;

/* Orgination Registration */
Route::post('/organization/register', [OrganizationController::class, 'register']);

/* Orgination Login */
Route::post('/organization/login', [OrganizationController::class, 'login']);

/* Client Resource */

Route::apiResource('/clients', ClientController::class)
        ->middleware('auth:sanctum')
        ->missing(function(){
            return response()->json(["success"=>false, "message"=>"Client not found"],404);
        });

/* Users Resource */
Route::apiResource('/users', UserController::class)
        ->middleware('auth:sanctum')
        ->missing(function(){
            return response()->json(["success"=>false, "message"=>"User not found"],404);
        });

Route::post('/users/invite', [UserController::class, 'invite'])
        ->middleware('auth:sanctum');

Route::post('/users/acceptInvite', [UserController::class, 'acceptInvite']);

/* Teams Resource */
Route::apiResource('/teams', TeamController::class)
        ->middleware('auth:sanctum')
        ->missing(function(){
            return response()->json(["success"=>false, "message"=>"Team not found"],404);
        });

/* Assign team to user */

Route::get('/AssignTeamToUser/{team_id}/{user_id}', [TeamController::class, 'AssignTeamToUser'])
        ->middleware('auth:sanctum')
        ->missing(function(){
            return response()->json(["success"=>false, "message"=>"Team or User not found"],404);
        });

/* Project team resources */

Route::apiResource('/projects', ProjectController::class)
        ->middleware('auth:sanctum')
        ->missing(function(){
            return response()->json(["success"=>false, "message"=>"Project not found"],404);
        });

/* Task resources */

Route::apiResource('/tasks', TaskController::class)
        ->middleware('auth:sanctum')
        ->missing(function(){
            return response()->json(["success"=>false, "message"=>"Task not found"],404);
        });

Route::get('/changeTaskStatus/{task}/{status}', [TaskController::class, 'changeTaskStatus'])
        ->middleware('auth:sanctum')
        ->missing(function(){
            return response()->json(["success"=>false, "message"=>"Task not found"],404);
        });

Route::get('/tasks/{task}/comments', [CommentController::class, 'getCommentsByTask'])
->middleware('auth:sanctum')
->missing(function(){
        return response()->json(["success"=>false, "message"=>"Task not found"],404);
});

Route::post('/tasks/{task}/comments', [CommentController::class, 'store'])
->middleware('auth:sanctum')
->missing(function(){
        return response()->json(["success"=>false, "message"=>"Task not found"],404);
});
/* Task Filters Routes */

Route::get('/tasks/filter/priority/{priority}', [TaskController::class, 'filterByPriority'])
    ->middleware('auth:sanctum');

/* Task Com */

