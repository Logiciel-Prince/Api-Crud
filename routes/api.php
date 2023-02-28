<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    UserController,
    PostController,
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


Route::group(['middleware'=>['auth:api']],function () {

    Route::group(['middleware' => ['user-access:Admin,SuperAdmin']],function () {
        //* <-----------------------This Route get all the User Information That create account ------------------------------>
        Route::Get('Getuser',[UserController::class,'index']);
    
        //* <-----------------------This Route Create New User ------------------------------>
    
        Route::Post('Create',[UserController::class,'create']);
    
        //* <-----------------------This Route Update The Existing User Name Email Passsword of Active User ------------------------------>
    
        Route::Post('update',[UserController::class,'update']);

    });

    // Route::Get('Getuser',[UserController::class,'index']);
    Route::group(['middleware'=>['user-access:SuperAdmin']],function () {
    
        //* <-----------------------This Route Update The Existing User Name Email Passsword of Selected User ------------------------------>
    
        Route::Post('update/{id}',[UserController::class,'updateAdminUser']);
    
        //* <-----------------------This Route Delete the Active User ------------------------------>
    
        Route::Delete('deleteuser/{id}',[UserController::class,'destroyAdminUser']);
    });

    Route::group(['prefix' => 'v1'], function()  
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
    //* <-----------------------This Route Delete the Active User ------------------------------>
    
    Route::Delete('delete',[UserController::class,'destroy']);
    
    //* <-----------------------This Route Provide The Information Of Login User------------------------------>
    
    Route::get('userinfo',[UserController::class,'userInfo']);
    
    //* <-----------------------This Route Logout the Active User  and Delete Their Api's------------------------------>
    
    Route::Delete('logout',[UserController::class,'logout']);
    
    //* <-----------------------This Route Upload the Image on database------------------------------>
    
    Route::Post('upload',[PostController::class,'upload']);
    
    //* <-----------------------This Route get all the Images of user from database------------------------------>
    
    Route::Get('upload',[PostController::class,'getUpload']);

    //* <-----------------------This Route delete the Image on database------------------------------>
    
    Route::Delete('upload/{id}',[PostController::class,'deletePost']);
    
    //* <-----------------------This Route Upload the Image on database------------------------------>
    
    Route::Post('upload/{id}',[PostController::class,'updatePost']);
    
     //* <-----------------------This Route Search the Image on database------------------------------>
    
     Route::Post('search',[PostController::class,'search']);


     //-----------------------------This Route get all the comment ---------------------------//
     
     Route::get('comment',[CommentController::class,'index']);
     
     //-----------------------------This Route create and post the comment on facebook posts---------------------------//
     
     Route::post('comment',[CommentController::class,'create']);

     //-----------------------------This Route Update and post the comment on facebook posts---------------------------//
     
     Route::post('comment/{id}',[CommentController::class,'update']);

     //-----------------------------This Route delete the comments on facebook posts---------------------------//
     
     Route::delete('comment/{id}',[CommentController::class,'destroy']);
});


//* <-----------------------This Route Login User and Provide them Api Key ------------------------------>

Route::Post('login',[UserController::class,'login']);

//* <-----------------------This Route Show Only Selected User ------------------------------>

Route::Post('getuser/{id}',[UserController::class,'show']);












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
