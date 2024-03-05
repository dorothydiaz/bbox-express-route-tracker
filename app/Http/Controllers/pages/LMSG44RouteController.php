<?php

namespace App\Http\Controllers\pages;

use App\Models\LmsG44Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LMSG44RouteController extends Controller
{
  public function showRoutes()
  {
    $routes = LmsG44Route::paginate(5);
    return view('content.pages.route-planner.pages-optimized-routes', ['routes' => $routes]);
  }

  public function saveRoute(Request $request)
  {
    $route = new LmsG44Route();
    $route->route_name = $request->input('route-name');
    $route->start_point = $request->input('start-point');
    $route->end_point = $request->input('end-point');
    $route->waypoints = json_encode($request->input('waypoints'));
    $route->save();

    return response()->json(['message' => 'Route saved successfully'], 200);
  }
}