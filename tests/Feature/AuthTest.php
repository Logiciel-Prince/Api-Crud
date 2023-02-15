<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    //* <-----------------------This Route get all the User Information That create account ------------------------------>
    public function index()
    {
        $data=User::get();
        return response()->json([
            'user' => $data
        ],200);
        $response->assertStatus(200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
 //* <-----------------------This Route Create New User ------------------------------>
     public function create(Request $request)
     {
         $user = User::create([
             'name' => $request->name,
             'email' => $request->email,
             'password' => Hash::make($request->password)
         ]);
         return response()->json([
             'message' =>'User Created Successfully',
             'user' => $user
         ],201);
     }
 
     /**
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
 //* <-----------------------This Route Login User and Provide them Api Key ------------------------------>
     public function login(Request $request)
     {
         if(Auth::attempt($request->only('email','password')))
         {
             $user = auth()->user();
             $success['token']=$user->createToken('MyApp')->accessToken;
             $success['name']=$user->name;
             return response()->json([$success],200);
         }
         else{
             return response()->json([
                 'message'=>'Check login credentials and try again'
             ],401);
         }
     }
 
     /**
      * Display the specified resource.
      *
      * @param  int  $id
      * @return \Illuminate\Http\Response
      */
 //* <-----------------------This Route Show Only Selected User ------------------------------>
     public function show($id)
     {
         $data = User::where('id',$id)->get();
         return response()->json([
             'user' => $data
         ],200);
     }
 
     /**
      *
      * @param  int  $id
      * @return \Illuminate\Http\Response
      */
 //* <-----------------------This Route Provide The Information Of Login User------------------------------>
     public function userinfo()
     {
         $id = auth()->user()->id;
         $data = User::where('id',$id)->get();
         return response()->json([
             'user' => $data,
             'message' => 'Login User Credentials'
         ],200);
     }
     
     /**
      * Update the specified resource in storage.
      *
      * @param  \Illuminate\Http\Request  $request
      * @param  int  $id
      * @return \Illuminate\Http\Response
      */
     //* <-----------------------This Route Update The Existing User Name Email Passsword of Selected User ------------------------------>
     public function update(Request $request)
     {
         $id = auth()->user()->id;
         $data = User::where('id',$id);
         $user = [
             'name' => $request->name,
             'email' => $request->email,
             'password' => Hash::make($request->password)
         ];
         // $data -> name = $request->name;
         // $data -> email = $request->email;
         // $data -> password = Hash::make($request->password);
         $data -> update($user);
         return response()->json([
             'message'=>'User Upated Successful'
         ],201);
     }
     
     /**
      * Remove the specified resource from storage.
      *
      * @param  int  $id
      * @return \Illuminate\Http\Response
      */
     //* <-----------------------This Route Delete the Selected User ------------------------------>
     public function destroy()
     {
         $id = auth()->user()->id;
         $data = User::find($id);
         $data -> delete();
         return response()->json([
             'message'=>'User Deleted Successful'
         ],200);
     }
}
