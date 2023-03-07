<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Exception;
use App\Models\{
    FacebookPage,
    User,
    Post
};
use Illuminate\Support\Facades\{
    Http,
    Hash,
    Auth,
};

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function login_to_facebook(){
        return Http::get('http://localhost/Prince/OJT/Api%20Crud/public/auth/facebook?scope=public_profile,email,user_birthday,user_friends,user_posts,user_likes,pages_manage_posts,user_photos,publish_videos,pages_manage_cta,pages_shows_list,pages_messaging,publish_to_groups,pages_read_engagement,pages_manage_metadata,pages_read_user_content,pages_manage_ads,pages_manage_engagement');
    }

    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
    {
        try {

            $user = Socialite::driver('facebook')->user();
            
            $finduser = User::where('facebook_id', $user->id)->first();

            if($finduser){

                Auth::login($finduser);

                return response()->json([
                    'user' => $user
                ]);

            }

            $response = $this->extendToken($user);
            
            $newUser = User::updateOrCreate(['email' => $user->email],[
                    'name' => $user->name,
                    'facebook_id'=> $user->id,
                    'password' => Hash::make('123456'),
                    'token' => $response->json('access_token'),
                ]);
            $pageId = null;
            $this->addPages($newUser,$pageId);
            
            Auth::login($newUser);

            return response()->json([
                'user' => $user
            ]);


        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    public function addPages($newUser){

        $request = Http::get(env('GRAPH_API_URL').'me/accounts?access_token='.auth()->user()->token);
        
        foreach($request['data'] as $d)
        {
            $data = FacebookPage::updateOrCreate(['page_id' => $d['id']],[
                'user_id' => $newUser->id,
                'page_id' => $d['id'],
                'page_name' => $d['name'],
                'access_token' => $d['access_token']
            ]);
        }

        return $data;

    }

    public function refreshPageToken($pageId){
        $page = FacebookPage::where('id',$pageId)->with('user')->first();

            $d = Http::get(env('GRAPH_API_URL').$page['page_id'].'?fields=id,name,access_token&access_token='.$page->user['token']);

            $data = FacebookPage::updateOrCreate(['page_id' => $d['id']],[
                'access_token' => $d['access_token']
            ]);

            return $data;
    }

    public function extendToken($user){
        $response = Http::get(env('GRAPH_API_URL').'oauth/access_token?grant_type=fb_exchange_token&client_id='.env('FACEBOOK_CLIENT_ID').'&client_secret='.env('FACEBOOK_CLIENT_SECRET').'&fb_exchange_token='.$user->token);
        return $response;
    }

    public function faceBookPost(Request $request){  
        $data = Post::where('postfbid',$request->id)->first();
        if(empty($data))
        {
            $imageName = null;
            if(array_key_exists('full_picture',$request->toArray()))
            {
                $url = $request->full_picture;
                $rep = file_get_contents($url);
                $extension = explode('?',$url);
                $ext = explode('.',$extension[0]);
                $imageName = time().'.'.$ext[5];
                $new = 'storage/images/'.$imageName;
                $upload =file_put_contents($new, $rep);
                if(!array_key_exists('description',$request->toArray())){
                    $request['description'] = 'No Description';
                }
                Post::create([
                    'user_id' => auth()->user()->id,
                    'category_id' => 17,
                    'postfbid' => $request->id,
                    'title' => 'facebook',
                    'desc' => $request->description,
                    'image' => $imageName
                ]);
            }
        }

    }

}
