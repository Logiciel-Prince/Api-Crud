<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    Public $token;


    Public Function login_to_facebook(){
        return Http::get('http://localhost/Prince/demo/Api%20Crud/public/auth/facebook?scope=public_profile,email,user_birthday,user_friends,user_posts,user_likes,pages_manage_posts,publish_actions');
    }

    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
    {
        try {

            $user = Socialite::driver('facebook')->user();
            
            // $this->token = $user->token;

            $finduser = User::where('facebook_id', $user->id)->first();

            if($finduser){

                Auth::login($finduser);

                return redirect()->intended('/');

            }else{
                $newUser = User::updateOrCreate(['email' => $user->email],[
                        'name' => $user->name,
                        'facebook_id'=> $user->id,
                        'password' => Hash::make('123456'),
                        'token' => $user->token,
                    ]);

                Auth::login($newUser);

                return response()->json([
                    'user' => $user
                ]);

            }

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    Public Function getData(){
        dd('hello');
            //     return Http::get("https://graph.facebook.com/{page-id}/subscribed_apps
            // ?subscribed_fields=feed
            // &access_token={page-access-token}");
    }
}
