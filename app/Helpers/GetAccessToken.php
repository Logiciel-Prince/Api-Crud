<?php

namespace App\Helpers;

use App\Models\{
    FacebookPage,
    User,
};

class GetAccessToken
{

    public function getPageAccessToken($pageName){
        $token = FacebookPage::where('page_name',$pageName)->first();
        return $token['access_token'];
    }

    public function getUserAccessToken($userId){
        $token = User::where('id',$userId)->first();
        return $token['access_token'];
    }

}

?>