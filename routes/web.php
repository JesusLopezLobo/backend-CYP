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

// Cargando clases.
use App\Http\Middleware\ApiAuthMiddleware;

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
Route::put('/api/user/update', 'UserController@update');
Route::get('/api/user/avatar/{filename}', 'UserController@getImage');
Route::get('/api/user/detail/{id}', 'UserController@detail');
Route::get('/api/user/show', 'UserController@show');
//Imagenes.
Route::post('/api/user/upload','UserController@upload')->middleware(App\Http\Middleware\ApiAuthMiddleware::class);

// Poesias.
Route::resource('/api/poesias', 'PoesiaController');

// Notas.
Route::resource('/api/notas', 'NotaController');

// Category.
Route::resource('/api/category', 'CategoryController');

// Posts.
Route::resource('/api/post', 'PostController');
Route::get('/api/post/image/{filename}', 'PostController@getImage');
Route::get('/api/post/category/{id}', 'PostController@getPostsByCategory');
Route::get('/api/post/user/{id}', 'PostController@getPostsByUser');
// Imagenes.
Route::post('/api/post/upload','PostController@upload');

// Comment.
Route::resource('/api/comment', 'ComentController');