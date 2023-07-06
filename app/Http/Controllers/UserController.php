<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Hamcrest\Arrays\IsArray;
use App\Notifications\InviteUser;
use App\Models\Scopes\VerifiedUserScope;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $org_id = $request->user()->id;
        $users = User::getUsersWithTaskCount($org_id);
        return $this->sendResponse($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'image' => 'nullable|mimes:jpg,jpeg,png|max:2048'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendError("Validation Error", $validator->errors(), '400');
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->is_verified = 1;
        $user->org_id = $request->user()->id;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/members');
            $image->move($destinationPath, $name);
            $user->image = $name;
        }
        if ($user->save()) {
            if ($request->has('teams') && is_array(json_decode($request->teams))) {
                $user->teams()->attach(json_decode($request->teams));
            }
            return $this->sendResponse($user, "User created");
        }
        return $this->sendError("Something went wrong");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $user->load('teams');
        return $this->sendResponse($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable',
            'image' => 'nullable|mimes:jpg,jpeg,png|max:2048'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendError("Validation Error", $validator->errors(), '400');
        }

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/members');
            $image->move($destinationPath, $name);
            $user->image = $name;
        }
        if ($user->save()) {
            if ($request->has('teams') && is_array(json_decode($request->teams))) {
                $user->teams()->sync(json_decode($request->teams));
            }
            return $this->sendResponse($user, "User updated");
        }
        return $this->sendError("Something went wrong");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user, Request $request)
    {
        if ($user && $user->org_id == $request->user()->id) {
            if ($user->delete()) {
                return $this->sendResponse(null, 'User deleted successfully');
            }
            return $this->sendError('Something went wrong', [], 500);
        }
        return $this->sendError('User does not exist', [], 404);
    }

    public function invite(Request $request)
    {
        $rules = [
            'email' => 'required|email|unique:users',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendError("Validation Error", $validator->errors(), '400');
        }

        $user = new User();
        $user->name = $request->email;
        $user->email = $request->email;
        $user->org_id = $request->user()->id;
        $user->password = Hash::make('12345678');
        if ($user->save()) {
            $user->notify(new InviteUser($user));
            return $this->sendResponse($user, "User invited");
        }
        return $this->sendError("Something went wrong");
    }

    public function acceptInvite(Request $request)
    {
        $rules = [
            'name' => 'required|max:255',
            'email' => 'required|email',
            'password' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendError("Validation Error", $validator->errors(), '400');
        }

       try {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->name = $request->name;
            $user->password = Hash::make($request->password);
            $user->is_verified = 1;
            if ($user->save()) {
                return $this->sendResponse($user, "User updated");
            }
            return $this->sendError("Something went wrong");
        }
        return $this->sendError("User not found");
       } catch (\Throwable $th) {
           return $this->sendError("Something went wrong");
       }
    }
}
