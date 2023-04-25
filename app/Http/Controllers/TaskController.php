<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\User;
use App\Notifications\NewTaskAssign;


class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tasks = Task::with('users','assignedBy')->get();
        return $this->sendResponse($tasks);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTaskRequest $request)
    {
        $task = new Task();
        $task->name = $request->name;
        $task->org_id = $request->user()->id;
        $task->client_id = $request->client_id;
        $task->project_id = $request->project_id;
        $task->team_id = $request->team_id;
        $task->assigned_by = $request->user()->id;
        $task->description = $request->description;
        if($request->has('status'))
            $task->status = $request->status;
        if($request->has('priority'))
            $task->priority = $request->priority;
        $task->due_date = $request->due_date;
        try {
            if($task->save()){
                if($request->has('users') && is_array($request->users)){
                    $task->users()->attach($request->users);
                    foreach($request->users as $user){
                        $user = User::find($user);
                        $user->notify(new NewTaskAssign($task));
                    }
                }
            }
            return $this->sendResponse($task, "Task created");
        } catch (\Throwable $th) {
            return $this->sendError("Something went wrong", $th->getMessage());
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        return $this->sendResponse($task);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task->name = $request->name;
        $task->client_id = $request->client_id;
        $task->project_id = $request->project_id;
        $task->team_id = $request->team_id;
        $task->assigned_by = $request->user()->id;
        $task->description = $request->description;
        if($request->has('status'))
            $task->status = $request->status;
        if($request->has('priority'))
            $task->priority = $request->priority;
        $task->due_date = $request->due_date;
        try {
            if($task->save()){
                if($request->has('users') && is_array(json_decode($request->users))){
                    $task->users()->sync(json_decode($request->users));
                }
            }
            return $this->sendResponse($task, "Task updated");
        } catch (\Throwable $th) {
            return $this->sendError("Something went wrong", $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        try {
            $task->delete();
            return $this->sendResponse($task, "Task deleted");
        } catch (\Throwable $th) {
            return $this->sendError("Something went wrong", $th->getMessage());
        }
    }

    public function changeTaskStatusToInProgress(Request $request, Task $task){
        $user_id = $request->user()->id;
        $task->users()->updateExistingPivot($user_id, ['status' => 'in-progress', 'started_at' => now()]);
        try {
            if($task->save()){
                return $this->sendResponse($task, "Task status changed to in progress");
            }
        } catch (\Throwable $th) {
            return $this->sendError("Something went wrong", $th->getMessage());
        }
    }
}