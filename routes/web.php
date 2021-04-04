<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Process\Exception\InvalidArgumentException;


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

Route::get('/', function () {
    return view('welcome');
});
Route::get("/login", function(Request $request){
    $state = Str::random(40);
    Session::put("state",$state);


    $query = http_build_query([
        "client_id" => "931b066b-4468-4501-b0fa-6d8daddd64bc",
        "redirect_uri" => "http://127.0.0.1:8080/callback",
        "response_type" => "code",
        "scope" => "view-user",
        "state" => $state,
    ]);
    return redirect("http://127.0.0.1:8000/oauth/authorize?". $query);


});

Route::get("/callback", function(Request $request){
    $state = $request->session()->pull('_token');

    throw_unless(strlen($state) > 0 , InvalidArgumentException::class);
    $reponse = Http::asForm()->post("http://127.0.0.1:8000/oauth/token",[

        "grant_type" => "authorization_code",
        "client_id" => "931b066b-4468-4501-b0fa-6d8daddd64bc",
        "client_secret" => "Y42KGOvyEWQd1lG8aKPAja3kKTRbRNnTyadUyjAo",
        "redirect_uri" => "http://127.0.0.1:8080/callback",
        "code" => $request->code
    ]);
    $request->session()->put($reponse->json());
    return redirect("/authuser");
});

Route::get("/authuser", function(Request $request){
    $access_token = $request->session()->get("access_token");
    $reponse  = Http::withHeaders([
        "Accept" => "application/json",
        "Authorization" => "Bearer ". $access_token
    ])->get("http://127.0.0.1:8000/api/user");
    return $reponse->json();


});
