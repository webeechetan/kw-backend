<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Organization;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class OrganizationController extends Controller
{
    // Organization registration
    public function register(Request $request){
        $rules = [
            'name' => 'required|unique:organizations|max:255',
            'email' => 'required|email|unique:organizations',
            'password' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
 
        if ($validator->fails()) {
            return $this->sendError("Validation Error",$validator->errors(),'400');
        }

        $organization = new Organization();
        $organization->name = $request->name;
        $organization->email = $request->email;
        $organization->password = Hash::make($request->password);
        if($organization->save()){
            return $this->sendResponse($organization,"Orginations created");
        }
        return $this->sendError("Something went wrong");
    }

    // Organization Login
    public function login(Request $request){
        $rules = [
            'email' => 'required',
            'password' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
 
        if ($validator->fails()) {
            return $this->sendError("Validation Error",$validator->errors(),'400');
        }

        $credentials = ['email'=>$request->email,'password'=>$request->password];
        $login = Auth::guard('organization')->attempt($credentials);

        if($login){    
            $token = auth()->guard('organization')->user()->createToken('organization_login_token', ['admin:all'])->plainTextToken;
            $response = ["organization"=>auth()->guard('organization')->user(),'token'=>$token];
            return $this->sendResponse($response,'Login Success');
        }
        return $this->sendError('Invalid Credentials');
    }

    // Organization Logout
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return $this->sendResponse('','Logout Success');
    }

    // Organization Profile
    public function profile(Request $request){
        $organization = $request->user();
        return $this->sendResponse($organization);
    }
    
}
