<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\ProjectController;

global $notfound;

/* Orgination Registration */
Route::post('/organization/register', [OrganizationController::class, 'register']);

/* Orgination Login */
Route::post('/organization/login', [OrganizationController::class, 'login']);

/* Users Resource */
Route::apiResource('/users', UserController::class)
        ->middleware('auth:sanctum')
        ->missing(function(){
            return response()->json(["success"=>false, "message"=>"User not found"],404);
        });

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