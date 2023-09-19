<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\Team;
use App\Notifications\NewTaskAssign;
use App\Notifications\TaskCompleted;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\ErrorHandler\Debug;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tasks = Task::with('users', 'assignedBy')->get();
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
        if ($request->has('status'))
            $task->status = $request->status;
        if ($request->has('priority'))
            $task->priority = $request->priority;
        $task->due_date = Carbon::parse($request->due_date);
        try {
            if ($task->save()) {
                if ($request->has('users') && is_array($request->users)) {
                    $task->users()->attach($request->users);
                    // foreach($request->users as $user){
                    //     $user = User::find($user);
                    //     $user->notify(new NewTaskAssign($task));
                    // }
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
        $task->load('users', 'assignedBy', 'completedBy', 'whenCompletedNotify', 'project');
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

        if ($request->has('status'))
            $task->status = $request->status;
        if ($request->has('priority'))
            $task->priority = $request->priority;
        $task->due_date = Carbon::parse($request->due_date)->format('Y-m-d H:i:s');
        try {
            if ($task->save()) {
                if ($request->has('users')) {
                    $task->users()->sync($request->users);
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

    public function getCurrentTaskNextStage($id)
    {
        $task = Task::find($id);
        if ($task->status == 'assigned') {
            return 'accepted';
        }
        if ($task->status == 'accepted') {
            return 'in_progress';
        }
        if ($task->status == 'in_progress') {
            return 'in_review';
        }
        if ($task->status == 'in_review') {
            return 'completed';
        }
    }

    public function changeTaskStatus(Request $request, Task $task, $status)
    {
        $user_id = $request->user()->id;
        $task->status = $status;
        try {
            if ($task->save()) {
                $task->users()->syncWithoutDetaching($user_id);
            }
            return $this->sendResponse($task, "Task status changed to $status");
        } catch (\Throwable $th) {
            return $this->sendError("Something went wrong", $th->getMessage());
        }
    }

    // filters 

    public function filterByStatus(Request $request)
    {
        $status = $request->input('status');
        $tasks = Task::where('status', $status)->with('users', 'assignedBy')->get();
        return $this->sendResponse($tasks);
    }

    public function filterByPriority(Request $request)
    {
        $priority = $request->input('priority');
        $tasks = Task::where('priority', $priority)->with('users', 'assignedBy')->get();
        return $this->sendResponse($tasks);
    }

    public function filterByUser(Request $request)
    {
        $userId = $request->input('user_id');
        $tasks = Task::whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })->with('users', 'assignedBy')->get();
        return $this->sendResponse($tasks);
    }

    // suneditor upload image handler

    public function uploadImage(Request $request)
    {
        $request = json_decode($request->getContent());
        $file = $request->file;
        Log::info($file);
    }
}