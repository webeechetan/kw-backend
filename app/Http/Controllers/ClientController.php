<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;


class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $org_id = $request->user()->id;
        $clients = Client::where('org_id', $org_id)->with('projects')->get();
        return response()->json(["success"=>true, "data"=>$clients],200);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreClientRequest $request)
    {
        $client = new Client();
        $client->org_id = $request->user()->id;
        $client->name = $request->name;
        $client->description = $request->description;
        if($request->hasFile('image')){
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/clients');
            $image->move($destinationPath, $name);
            $client->image = $name;
        }
        if($client->save()){
            return $this->sendResponse($client,"Client created");
        }
        return $this->sendError("Something went wrong");

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show(Client $client)
    {
        return $this->sendResponse($client);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        $client->name = $request->name;
        $client->description = $request->description;
        if($request->hasFile('image')){
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/clients');
            $image->move($destinationPath, $name);
            $client->image = $name;
        }
        if($client->save()){
            return $this->sendResponse($client,"Client updated");
        }
        return $this->sendError("Something went wrong");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client, Request $request)
    {
        if($request->user()->id != $client->org_id){
            return $this->sendError("You are not authorized to delete this client");
        }

        if($client->delete()){
            return $this->sendResponse($client,"Client deleted");
        }
        return $this->sendError("Something went wrong");
    }
}
