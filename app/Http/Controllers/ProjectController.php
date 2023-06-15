<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\project\UpdateProjectRequest;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $org_id = $request->user()->id;
        $projects = Project::with(['users','teams','client'])->where('org_id', $org_id)->get();
        return $this->sendResponse($projects);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProjectRequest $request)
    {
        $project = new Project();
        $project->client_id = $request->client_id;
        $project->org_id = $request->user()->id;
        $project->name = $request->name;
        $project->description = $request->description;
        if($project->save()){
            if($request->has('teams')){
                $project->teams()->attach($request->teams);
            }
            if($request->has('users')){
                $project->users()->attach($request->users);
            }
            return $this->sendResponse($project,"Project created");
        }
        return $this->sendError("Something went wrong");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        return $this->sendResponse($project);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        // $rules = [
        //     'name' => 'required|string',
        //     'description' => 'nullable|string'
        // ];

        // $validator = Validator::make($request->all(), $rules);
        // if($validator->fails()){
        //     return $this->sendError("Validation Error",$validator->errors(),'400');
        // }
        
        $project->name = $request->name;
        $project->description = $request->description;
        if($project->save()){
            return $this->sendResponse($project,"Project updated");
        }
        return $this->sendError("Something went wrong");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        if($project->delete()){
            return $this->sendResponse($project,"Project deleted");
        }
        return $this->sendError("Something went wrong");
    }
}
