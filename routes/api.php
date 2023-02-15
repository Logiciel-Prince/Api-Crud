<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    Usercontroller,
    Postcontroller,
    CategoryController,
    CommentController
};
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['middleware'=>['auth:api'],['user-access:Admin,SuperAdmin']],function () {

    //* <-----------------------This Route get all the User Information That create account ------------------------------>
    Route::Get('Getuser',[Usercontroller::class,'index']);

    //* <-----------------------This Route Create New User ------------------------------>

    Route::Post('Create',[Usercontroller::class,'create']);

    //* <-----------------------This Route Update The Existing User Name Email Passsword of Active User ------------------------------>

    Route::Post('update',[Usercontroller::class,'update']);


});

// Route::Get('Getuser',[Usercontroller::class,'index']);
Route::group(['middleware'=>['auth:api'],['user-access:SuperAdmin']],function () {

    //* <-----------------------This Route Update The Existing User Name Email Passsword of Selected User ------------------------------>

    Route::Post('update/{id}',[Usercontroller::class,'updateAdminUser']);

    //* <-----------------------This Route Delete the Active User ------------------------------>

    Route::Delete('DeleteUser/{id}',[Usercontroller::class,'destroyAdminUser']);
});

//* <-----------------------This Route Login User and Provide them Api Key ------------------------------>

Route::Post('login',[Usercontroller::class,'login']);

//* <-----------------------This Route Show Only Selected User ------------------------------>

Route::Post('Getuser/{id}',[Usercontroller::class,'show']);



// Route::Get('Delete',[Usercontroller::class,'destroy']);
Route::middleware('auth:api')->group(function () {
    //* <-----------------------This Route Delete the Active User ------------------------------>

    Route::Delete('delete',[Usercontroller::class,'destroy']);

    //* <-----------------------This Route Provide The Information Of Login User------------------------------>

    Route::get('userinfo',[Usercontroller::class,'userinfo']);

    //* <-----------------------This Route Logout the Active User  and Delete Their Api's------------------------------>

    Route::Delete('logout',[Usercontroller::class,'logout']);

    //* <-----------------------This Route Upload the Image on database------------------------------>

    Route::Post('upload',[Postcontroller::class,'upload']);

    //* <-----------------------This Route get all the Images of user from database------------------------------>

    Route::Get('upload',[Postcontroller::class,'getupload']);

    //* <-----------------------This Route Upload the Image on database------------------------------>

    Route::Post('upload/{id}',[Postcontroller::class,'updatepost']);

     //* <-----------------------This Route Search the Image on database------------------------------>

     Route::Post('search',[Postcontroller::class,'search']);

    //  Route::controller(Usercontroller::class)->group(function(){

    //  });
});

Route::group(['prefix' => 'v1','middleware'=>'auth:api'], function()  
{  
    //---------------------This Route show the Categories In Hierarchical form--------------------------//
    
    Route::get('category',[CategoryController::class,'manageCategory'])->name('category-tree-view');
    
    //---------------------This Route add The new Categories Inside root or other Categories--------------------------//
    
    Route::Post('category',[CategoryController::class,'addCategory']);
    
    //---------------------This Route delete The selected Category--------------------------//
    
    Route::Delete('category/{id}',[CategoryController::class,'deleteCategory']);
    
    //---------------------This Route update The existing Categories --------------------------//
    
    Route::put('category/{id}',[CategoryController::class,'updateCategory']);

});

Route::middleware('auth:api')->group(function () {

    //-----------------------------This Route get all the comment ---------------------------//

    Route::get('comment',[CommentController::class,'index']);

    //-----------------------------This Route create and post the comment on facebook posts---------------------------//

    Route::post('comment',[CommentController::class,'create']);

});





//* <-----------------------This Route Is For Error Handling of Wrong Url ----------------------------->
Route::fallback(function(){
    return response()->json([
        'message' => 'Page Not Found. Check Your URL and Try again'], 404);
});


//* <-----------------------This Route Is For Error Handling of Wrong Api Key ----------------------------->
Route::get('error', function () {
    return response()->json([
        'message' => 'Please Check Your Api Key and Try again'
    ],401);
})->name('login');
// Route::middleware('auth:api')->group(function () {
//     Route::get('userinfo',[Icontroller::class,'userinfo']);
// });
