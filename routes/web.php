<?php

use App\Http\Controllers\pages\Page2;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\RoleController;
use App\Http\Controllers\pages\UserController;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\LMSG44RouteController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
//Redirect to login page
Route::redirect(uri: '/', destination: 'login');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

  // Main Page Route
  Route::get('/', [HomePage::class, 'index'])->name('pages-home');
  Route::get('/page-2', [Page2::class, 'index'])->name('pages-page-2');

  // locale
  Route::get('lang/{locale}', [LanguageController::class, 'swap']);

  // Route Planner
  Route::get('/create-route', function () {
    return view('content.pages.route-planner.pages-create-route');
  });
  Route::post('/save-route', [LMSG44RouteController::class, 'saveRoute']);
  Route::get('/show-routes', [LMSG44RouteController::class, 'showRoutes']);

  Route::resources([
    'roles' => RoleController::class,
    'users' => UserController::class,
  ]);
});