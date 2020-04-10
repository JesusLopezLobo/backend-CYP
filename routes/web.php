<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Video;

Route::get('/', function () {
/*     $videos = Video::all();
    foreach ($videos as $video) {
        //var_dump($video);
        echo "<br>".$video->title."<br>";
        echo "<br>".$video->user->email."<br>";
        foreach ($video->comments as $comment) {
            echo $comment->body;
            echo "<hr>";

        }
    }  */
    //var_dump(Request::url()); die();
    return view('welcome');

});

Route::get('/princi', function(){
   return view('prueba'); 
});

Route::auth();

Route::get('/home', 'HomeController@index')->name('home');

// Users.
Route::post('/api/register', 'UserController@register');
Route::post('/api/login', 'UserController@login');

// Poesias.
Route::resource('/api/poesias', 'PoesiaController');
