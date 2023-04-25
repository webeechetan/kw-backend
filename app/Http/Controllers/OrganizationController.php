<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Organization;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Organization\OrganizationRegisterRequest;
use App\Http\Requests\Organization\OrganizationLoginRequest;

class OrganizationController extends Controller
{
    // Organization registration
    public function register(OrganizationRegisterRequest $request){

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
    public function login(OrganizationLoginRequest $request){

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
