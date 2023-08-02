<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Requests\Comment\CommentStoreRequest;
use App\Models\Task;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $comments = Comment::with('user')->where('task_id', $request->task_id)->get();
    }

    /**
     * Show the comments by task.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCommentsByTask(Task $task)
    {
        $comments = Comment::with('user')->where('task_id', $task->id)->get();
        return $this->sendResponse($comments, 'Comments Retrieved Successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CommentStoreRequest $request, Task $task)
    {
        $comment = new Comment();
        $comment->user_id = $request->user()->id;
        $comment->task_id = $task->id;
        $comment->comment = $request->comment;
        if ($comment->save()) {
            return $this->sendResponse($comment, 'Comment Added Successfully');
        }
        return $this->sendError('Error in adding Comment', ['Error in adding Comment'], 422);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Comment $comment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        //
    }
}
