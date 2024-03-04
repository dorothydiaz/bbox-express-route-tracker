<?php

use App\Http\Controllers\pages\Page2;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\LMSG44RouteController;
use App\Http\Controllers\authentications\RegisterBasic;

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

// Main Page Route
Route::get('/', [HomePage::class, 'index'])->name('pages-home');
Route::get('/page-2', [Page2::class, 'index'])->name('pages-page-2');

// locale
Route::get('lang/{locale}', [LanguageController::class, 'swap']);

// pages
// Route Planner
Route::get('/create-route', function () {
  return view('content.pages.route-planner.pages-create-route');
});
Route::post('/save-route', [LMSG44RouteController::class, 'saveRoute']);
Route::get('/show-routes', [LMSG44RouteController::class, 'showRoutes']);