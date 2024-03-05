@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Optimized Routes')

@section('content')
<div class="container mt-5">
  <h4>Optimized Routes</h4>
  <div class="card">
    <div class="card-header text-white d-flex justify-content-between align-items-center">
      <h5 class="card-title text-black">Routes List</h5>
        <input type="text" id="routeSearch" class="form-control form-control-sm" placeholder="Search"
        style="width: 250px;">
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="routesTable" class="table table-striped">
          <thead>
            <tr>
              <th>Route Name</th>
              <th>Start Point</th>
              <th>End Point</th>
              <th>Waypoints</th>
            </tr>
          </thead>
          <tbody>
            @foreach($routes as $route)
            <tr>
              <td>{{ $route->route_name }}</td>
              <td>{{ $route->start_point }}</td>
              <td>{{ $route->end_point }}</td>
              <td>
                @php
                $colors = ['primary', 'success', 'danger', 'warning'];
                $colorIndex = 0;
                @endphp
                @foreach(json_decode($route->waypoints) as $waypoint)
                <span class="badge rounded-pill bg-label-{{ $colors[$colorIndex] }} mb-2">{{ $waypoint
                  }}</span><br>
                @php
                $colorIndex = ($colorIndex + 1) % count($colors);
                @endphp
                @endforeach
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
        <!-- Pagination -->
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-end mt-3">
        <li class="page-item first">
            <a class="page-link" href="#"><i class="ti ti-chevrons-left ti-xs"></i></a>
        </li>
        <li class="page-item prev">
            <a class="page-link" href="#"><i class="ti ti-chevron-left ti-xs"></i></a>
        </li>
        <!-- Display Pagination Links -->
        @for ($i = 1; $i <= $routes->lastPage(); $i++)
            <li class="page-item {{ $i == $routes->currentPage() ? 'active' : '' }}">
                <a class="page-link" href="{{ $routes->url($i) }}">{{ $i }}</a>
            </li>
        @endfor
        <li class="page-item next">
            <a class="page-link" href="#"><i class="ti ti-chevron-right ti-xs"></i></a>
        </li>
        <li class="page-item last">
            <a class="page-link" href="#"><i class="ti ti-chevrons-right ti-xs"></i></a>
        </li>
    </ul>
</nav>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $(document).ready(function() {
            $('#routeSearch').on('keyup', function() {
                var searchText = $(this).val().toLowerCase();
                $('#routesTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(searchText) > -1)
                });
            });
        });
</script>
@endsection
