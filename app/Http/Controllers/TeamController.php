<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use File;


class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $org_id = $request->user()->id;
        $teams = Team::with(['users','projects'])->where('org_id', $org_id)->get();
        return $this->sendResponse($teams);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $request;
        $rules = [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|mimes:jpg,jpeg,png|max:2048'
        ];

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            return $this->sendError("Validation Error",$validator->errors(),'400');
        }
        $team = new Team();
        $team->org_id = $request->user()->id;
        $team->name = $request->name;
        $team->description = $request->description;
        if($request->hasFile('image')){
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/teams');
            $image->move($destinationPath, $name);
            $team->image = $name;
        }
        if($team->save()){
            return $this->sendResponse($team,"Team created");
        }
        return $this->sendError("Something went wrong");

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function show(Team $team)
    {
        return $this->sendResponse($team);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function update(Team $team, Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|mimes:jpg,jpeg,png|max:2048'
        ];

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            return $this->sendError("Validation Error",$validator->errors(),'400');
        }
        $team->name = $request->name;
        $team->description = $request->description;

        if($request->hasFile('image')){
            $image_path = public_path('/teams/'.$team->image);
            if(File::exists($image_path)){
                File::delete($image_path);
            }
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/teams');
            $image->move($destinationPath, $name);
            $team->image = $name;
        }
        if($team->save()){
            return $this->sendResponse($team,"Team updated");
        }
        return $this->sendError("Something went wrong");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function destroy(Team $team, Request $request)
    {
        $org_id = $request->user()->id;
        if($team->org_id == $org_id){
            $team->delete();
            return $this->sendResponse($team,"Team deleted");
        }
        return $this->sendError("You are not authorized to delete this team");
    }


    public function AssignTeamToUser($team_id, $user_id, Request $request){
        $org_id = $request->user()->id;
        $team = Team::where('id', $team_id)->where('org_id', $org_id)->first();
        $user = User::where('id', $user_id)->where('org_id', $org_id)->first();
        if($team && $user){
            $team->users()->attach($user_id);
            return $this->sendResponse($team,"Team assigned to user");
        }
        return $this->sendError("Team or User not found");
    }

}
