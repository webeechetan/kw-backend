<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\User;
use App\Notifications\NewTaskAssign;
use App\Notifications\TaskCompleted;


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
        $task->status = "in_progress";
        try {
            if($task->save()){
                $task->users()->syncWithoutDetaching($user_id);
            }
            return $this->sendResponse($task, "Task status changed to in progress");
        } catch (\Throwable $th) {
            return $this->sendError("Something went wrong", $th->getMessage());
        }
    }

    public function changeTaskStatusToCompleted(Request $request, Task $task){
        $user_id = $request->user()->id;
        $task->status = "completed";
        $task->completed_by = $user_id;
        try {
            if($task->save()){
                $task->users()->syncWithoutDetaching($user_id);
                $task->assignedBy->notify(new TaskCompleted($task));
            }
            return $this->sendResponse($task, "Task status changed to completed");
        } catch (\Throwable $th) {
            return $this->sendError("Something went wrong", $th->getMessage());
        }
    }

    // move task to next stage 

    public function moveTaskToNextStage(Request $request,Task $task){
        $user_id = $request->user()->id;
        $task->status = $this->getCurrentTaskNextStage($task->id);
        $task->completed_by = $user_id;
        try {
            if($task->save()){
                $task->users()->syncWithoutDetaching($user_id);
                // $task->assignedBy->notify(new TaskCompleted($task));
            }
            return $this->sendResponse($task, "Task status changed to accepted");
        } catch (\Throwable $th) {
            return $this->sendError("Something went wrong", $th->getMessage());
        }
    }

    public function getCurrentTaskNextStage($id){
        $task = Task::find($id);
        if($task->status == 'assigned'){
            return 'accepted';
        }
        if($task->status == 'accepted'){
            return 'in_progress';
        }
        if($task->status == 'in_progress'){
            return 'in_review';
        }
        if($task->status == 'in_review'){
            return 'completed';
        }
    }
}
