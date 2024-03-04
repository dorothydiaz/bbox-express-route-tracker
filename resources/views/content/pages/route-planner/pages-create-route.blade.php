@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Route Planner')

@section('page-style')

<link rel="stylesheet" href="assets/vendor/libs/animate-css/animate.css" />
<link rel="stylesheet" href="assets/vendor/libs/sweetalert2/sweetalert2.css" />

<style>
  #map {
    height: 433px;
    width: auto;
    overflow: hidden;
    position: relative;
    margin-right: 10px;
    padding-right: 15px;
    padding-left: 15px;
    margin-bottom: 10px;
  }

  .tooltip {
    display: inline;
    position: relative;
  }

  .tooltip:hover:after {
    background: #333;
    background: rgba(0, 0, 0, .8);
    border-radius: 5px;
    bottom: 26px;
    color: #fff;
    content: attr(title);
    left: 20%;
    padding: 5px 15px;
    position: absolute;
    z-index: 98;
    width: 220px;
  }

  .tooltip:hover:before {
    border: solid;
    border-color: #333 transparent;
    border-width: 6px 6px 0 6px;
    bottom: 20px;
    content: "";
    left: 50%;
    position: absolute;
    z-index: 99;
  }
</style>
@endsection

@section('content')
<h4>Route Planner</h4>
<div class="container">
  <div class="row">
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">Create Route</div>
        <div class="card-body">
          <form id="routeForm" action="/save-route" method="POST" class="browser-default-validation">
            @csrf
            <div id="placeInfo">
              <div class="mb-3">
                <label for="route-name">Route Name:</label>
                <input type="text" name="route-name" id="route-name" class="form-control"
                  placeholder="Enter a route name" required>
              </div>

              <div class="searchInput mb-3">
                <label for="loc1">Start:</label>
                <input type="text" name="searchbox" class="form-control" placeholder="Set start location" id="loc1" required>
              </div>

              <div class="searchInput mb-3">
                <label for="loc2">Waypoints: (Required)</label>
                <input type="text" name="searchbox" class="form-control" placeholder="Enter some waypoints" id="loc2">
                <div>
                  <ul id="waypointsInfo" class="list-group list-group-timeline"></ul>
                </div>
              </div>

              <div class="searchInput mb-3">
                <label for="loc3">End:</label>
                <input type="text" name="searchbox" class="form-control" placeholder="Set end location" id="loc3" required>
              </div>

            </div>
            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary" id="saveRouteButton">Save Route</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div id="map"></div>
    </div>
  </div>
</div>
<script src="assets/vendor/libs/sweetalert2/sweetalert2.js" ></script>
<script>
  var map;
            var markers = []; //store the location markers we add tinyurl.com/gmproj5
            var directionsDisplay;
            var directionsService;

            var start; // start place
            var end; // end place
            var waypoint = []; // array for holding places objects of each travel stopping point (between start and stop)

            // https://developers.google.com/maps/documentation/javascript/directions#waypoint-limits
            var MAX_WAYPOINTS = 25; // max number of waypoints allowed by API (25 max as of Jan 27, 2020)

            document.getElementById("loc2").placeholder =
                "Enter up to " + MAX_WAYPOINTS + " waypoints";

            //called after the google maps api is loaded
            function initMap() {
                //create map object
                map = new google.maps.Map(document.getElementById("map"), {
                    center: {
                        lat: 14.5995,
                        lng: 120.9842
                    },
                    zoom: 4,
                    mapId: "cb6a74085847d4de",
                });
                const trafficLayer = new google.maps.TrafficLayer();
                trafficLayer.setMap(map);

                // attempt to get user location with W3C Geolocation (Preferred). see: tinyurl.com/gmproj3
                var initialLocation;
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        initialLocation = new google.maps.LatLng(
                            position.coords.latitude,
                            position.coords.longitude
                        );
                        map.setCenter(initialLocation);
                        map.setZoom(11);
                    });
                }

                //DIRECTIONS based on directions-panel.html from tinyurl.com/gmproj2
                //automatically updated when a new route is set
                directionsService = new google.maps.DirectionsService();
                directionsDisplay = new google.maps.DirectionsRenderer();
                directionsDisplay.setMap(map);
                directionsDisplay.setPanel(document.getElementById("directionsPanel"));

                // Create the searchBoxes and link them to the UI element. from: tinyurl.com/gmproj1
                var searchBox0 = new google.maps.places.SearchBox(
                    document.getElementById("loc1")
                );
                var searchBox1 = new google.maps.places.SearchBox(
                    document.getElementById("loc2")
                );
                var searchBox2 = new google.maps.places.SearchBox(
                    document.getElementById("loc3")
                );

                // Bias the SearchBox results towards current map's viewport.
                map.addListener("bounds_changed", function() {
                    searchBox0.setBounds(map.getBounds());
                    searchBox1.setBounds(map.getBounds());
                    searchBox2.setBounds(map.getBounds());
                });

                //if searchBox0 is used
                searchBox0.addListener("places_changed", function() {
                    document.getElementById("loc1").value = ""; //clear searchbox
                    addPoint(searchBox0.getPlaces()[0], "start");
                });

                //if searchBox1 is used
                searchBox1.addListener("places_changed", function() {
                    document.getElementById("loc2").value = "";
                    addPoint(searchBox1.getPlaces()[0], "waypoint");
                });

                //if searchBox2 is used
                searchBox2.addListener("places_changed", function() {
                    addPoint(searchBox2.getPlaces()[0], "end");
                });
                toggleSearchBoxes(true);

                // now that google APIs are loaded:
                //const placesService = new google.maps.places.PlacesService(map);
                const geocoder = new google.maps.Geocoder();
                loadFromUrl(geocoder);
            }

            function calcRoute(routeStart) {
                updateUrl(); // update URL (because start/end/waypoint state just changed)

                if (
                    typeof start == "undefined" ||
                    typeof end == "undefined" ||
                    typeof waypoint[0] == "undefined"
                ) {
                    var pan = document.getElementById("directionsPanel");
                    if ((" " + pan.className + " ").indexOf(" disabled ") == -1) {
                        pan.className += " disabled";
                        document.getElementById("ham").src = "images/grey-hamburger.png";
                    }

                    directionsDisplay.setMap(null); //in case the map was previously drawn
                    for (var i = 0; i < markers.length; i++)
                        if (typeof markers[i] != "undefined") markers[i].setMap(
                            map); //redraw the points that were previously turned off
                    return; //don't calculate route if all needed points aren't set
                }
                //    printLocations();
                directionsDisplay.setMap(map);
                const actualWaypoints = waypoint.map((w) => ({
                    location: w.geometry.location, //latlng object
                    stopover: true,
                }));

                // console.log("***calculating route");
                // https://developers.google.com/maps/documentation/javascript/directions
                const request = {
                    origin: start.geometry.location, //latlng object
                    destination: end.geometry.location,
                    waypoints: actualWaypoints,
                    optimizeWaypoints: true, ///VERY IMPORTANT!!! WOW example: tinyurl.com/gmproj6
                    travelMode: google.maps.TravelMode.DRIVING,
                };

                directionsService.route(request, function(result, status) {
                    if (status == google.maps.DirectionsStatus.OK) {
                        clearMarkers();
                        directionsDisplay.setDirections(result);
                    }
                });

                var pan = document.getElementById("directionsPanel");
                if ((" " + pan.className + " ").indexOf(" disabled ") != -1) {
                    pan.className = ""; //make panel visible
                    document.getElementById("ham").src = "images/hamburger.png";
                }
            }

            function setMarker(n, plc) {
                //sets markers[n] to the latlng object loc, creates a new marker if it doesn't exist

                if (n == 0) var link = "http://www.googlemapsmarkers.com/v1/00FF00";
                if (n == 1) var link = "http://www.googlemapsmarkers.com/v1/FF0000";
                if (n > 1) var link = "http://www.googlemapsmarkers.com/v1/FFA500";

                if (typeof markers[n] == "undefined") {
                    //if it doesn't exist
                    markers[n] = new google.maps.Marker({
                        //create new marker
                        position: plc.geometry.location, //a latlng object
                        map: map,
                        //            label: n.toString(),
                        animation: google.maps.Animation.DROP,
                        icon: link,
                        title: n.toString(),
                        draggable: false, //make true later if the loc is retrieved from the marker
                    });
                } else {
                    markers[n].setPosition(plc.geometry.location);
                }
            }

            function clearMarkers() {
                for (var i = 0; i < markers.length; i++)
                    if (typeof markers[i] != "undefined") markers[i].setMap(
                        null); //turn markers off but don't delete in case directionsDisplay is turned off
                // console.log("***markers cleared");
            }

            async function loadFromUrl(geocoder) {
                console.log("loading from url");
                toggleSearchBoxes(false); // disable searchboxes
                const queryParams = new URLSearchParams(window.location.search);
                let request;

                const startPlaceId = queryParams.get("start");
                const endPlaceId = queryParams.get("end");
                const waypointIds = queryParams.get("waypoint") ?
                    queryParams.get("waypoint").split(",") : [];
                console.log("waypointIds = ");
                console.log(waypointIds);

                let promises = [];

                if (startPlaceId) {
                    promises.push(
                        expandPlaceId(geocoder, startPlaceId, (place) => {
                            addPoint(place, "start", false);
                        })
                    );
                }
                if (endPlaceId) {
                    promises.push(
                        expandPlaceId(geocoder, endPlaceId, (place) => {
                            addPoint(place, "end", false);
                        })
                    );
                }

                for (const waypointId of waypointIds) {
                    promises.push(
                        expandPlaceId(geocoder, waypointId, (place) => {
                            addPoint(place, "waypoint", false);
                        })
                    );
                }

                console.log(`waiting for ${promises.length} promises...`);
                await Promise.all(promises);
                if (promises.length > 0) {
                    console.log("recalculating route");
                    calcRoute();
                }

                toggleSearchBoxes(true); // enable searchboxes
            }

            /**
             * given a google placeId, convert it to a place object and pass it to the provided callback.
             * returns a promise.
             * based on https://developers.google.com/maps/documentation/javascript/examples/geocoding-place-id#maps_geocoding_place_id-javascript
             *   and https://developers.google.com/maps/documentation/javascript/geocoding
             */
            function expandPlaceId(geocoder, placeId, callback) {
                return geocoder
                    .geocode({
                        placeId: placeId
                    })
                    .then(({
                        results
                    }) => {
                        if (!results[0]) {
                            console.warn(`unable to find result for place_id '${placeId}'`);
                            return;
                        }
                        const res = results[0]; //should have fields res.geometry.location and res.formatted_address;
                        callback(res);
                    })
                    .catch((e) => console.error("Geocoder failed due to: " + e));

                // note that the result won't have the 'name' field
                // we could lookup the 'name' for this place with an additional API call, but not sure its worth it:
                // or we could store the lat/lng in the URL instead so we can just query the places API below (skipping the geocode step)
                /*
                // https://developers.google.com/maps/documentation/javascript/reference/places-service#PlacesService.findPlaceFromQuery
                request = { query: queryParams.get('start'), fields: ['geometry.location', 'name', 'formatted_address'] };
                placesService.findPlaceFromQuery(request, function(result, status) {
                    console.log('result = '); console.log(result);
                    console.log('status='); console.log(status);
                });
                */
            }

            /**
             * update URL to store waypoints/start/stop locations.
             */
            function updateUrl() {
                console.log("updating url");
                let params = {
                    waypoint: waypoint.map((p) => p.place_id)
                };
                if (start) params.start = start.place_id;
                if (end) params.end = end.place_id;

                params = new URLSearchParams(params);
                window.history.pushState({}, "", `${window.location.pathname}?${params}`);
            }

            /**
             * add a place as the start, end, or a waypoint on the route.
             *
             * @param place the place to be added
             * @param pointType (str) 'start' | 'end' | 'waypoint'
             * @param computeDirections (bool) whether to call calcRoute() after adding the point (default true)
             */
            function addPoint(place, pointType, computeDirections = true) {
                if (exists(place, false)) return; // prevent adding a duplicate place
                // if this place came from geocode lookup, it won't have a 'name' field:
                const placeName = place["name"] || place["formatted_address"];

                if (pointType === "start") {
                    start = place;
                    // Update the input box value instead of startInfo's innerHTML
                    document.getElementById("loc1").value = place[
                        "formatted_address"]; // Use formatted_address for the input box value
                    // Remove or comment out the line that updates startInfo's innerHTML
                    // document.getElementById("startInfo").innerHTML = "<br>" + placeName;
                    setMarker(0, start);
                    calcRoute();
                } else if (pointType === "end") {
                    end = place;
                    // Update the input box value instead of endInfo's innerHTML
                    document.getElementById("loc3").value = place[
                        "formatted_address"]; // Use formatted_address for the input box value
                    // Remove or comment out the line that updates endInfo's innerHTML
                    // document.getElementById("endInfo").innerHTML = "<br>" + placeName;
                    setMarker(1, end);
                    calcRoute();
                } else if (pointType === "waypoint") {
                    if (waypoint.length >= MAX_WAYPOINTS) {
                        // check against max number of waypoints
                        Swal.fire({
                        icon: 'warning',
                        title: 'Too many waypoints',
                        text: "Only " + MAX_WAYPOINTS + " waypoints are allowed. Please remove a waypoint before adding a new one.",
                        showConfirmButton: false,
                        timer: 1500 // Close the modal after 1.5 seconds
                        });
                        return;
                    }
                    waypoint.push(place); // add place to end of array
                    //console.log('added new waypoint, markers = '); console.log(markers);
                    const i = waypoint.length - 1;
                    setMarker(i + 2, waypoint[i]);

                    document.getElementById("waypointsInfo").innerHTML +=
                        "<li class='list-unstyled list-group-item list-group-timeline-secondary' id='point" +
                        i +
                        "'>" +
                        "<span title='" +
                        place["formatted_address"] +
                        "'>" +
                        placeName +
                        "</span><a href='javascript:void(0)' onclick='deletePoint(this)'><img src='{{ asset('assets\img\customs\delete.png') }}' height='10' hspace='10'></a>\
                          <a href='javascript:void(0)'>"; // [X]
                    //            console.log("waypoint=" + waypoint + '\n');
                    calcRoute();
                } else {
                    console.error(`invalid pointType '${pointType}' for addPoint()`);
                    return;
                }

                if (computeDirections) {
                    calcRoute();
                }
            }

            function deletePoint(elem) {
                //tinyurl.com/gmproj8
                elem = elem
                    .parentNode; //a ul element with id="pointn" where n is sum number. elem started as the <a> element that was clicked
                var i = parseInt(elem.id.substring(5));

                waypoint.splice(i, 1); //location i, remove 1 element
                markers[i + 2].setMap(null);
                markers.splice(i + 2, 1); //i is offset by 2 bc start and end are in front

                elem.parentNode.removeChild(document.getElementById("point" + i)); //delete element

                for (var t = i + 1; document.getElementById("point" + t) != null; t++) {
                    document.getElementById("point" + t).id = "point" + (t - 1);
                }

                //    console.log("***removed waypoint[" + i + "]");
                //    console.log("waypoint=" + waypoint);
                calcRoute();
            }

            function printLocations() {
                console.log("Printing geometry.location of all locations");
                if (typeof start != "undefined")
                    console.log("start=" + start.geometry.location);
                else console.log("start=UNDEFINED");
                if (typeof end != "undefined") console.log("end=" + end.geometry.location);
                else console.log("end=UNDEFINED");

                console.log("waypoint.length=" + waypoint.length);
                for (var i = 0; i < waypoint.length; i++)
                    console.log(
                        "waypoint[" +
                        i +
                        "].geometry.location=" +
                        waypoint[i].geometry.location
                    );
            }

            /**
             * check if a place is already in use (as a start/end spot or waypoint) to prevent duplicates.
             * @param plc: place
             * @param isEndpoint: boolean indicator if this place will be the start or stop.
             */
            function exists(plc, isEndpoint) {
                for (var i = 0; i < waypoint.length; i++) {
                    //loop through waypoints
                    if (waypoint[i]["formatted_address"] == plc["formatted_address"]) {
                        Swal.fire({
                          icon: 'warning',
                          title: 'Address is already a waypoint!',
                          text: "Address:\n'" + waypoint[i]["formatted_address"] + "'\nis already a waypoint!",
                          showConfirmButton: false,
                          timer: 1500  // Close the modal after 1.5 seconds
                      });
                        return true;
                    }
                }

                //check that the potential waypoint isn't the same as the start or end
                if (
                    !isEndpoint &&
                    ((typeof start != "undefined" &&
                            start["formatted_address"] == plc["formatted_address"]) ||
                        (typeof end != "undefined" &&
                            end["formatted_address"] == plc["formatted_address"]))
                ) {
                    Swal.fire({
                      icon: 'warning',
                      title: 'Address is your start or end point!',
                      text: "Address:\n'" + plc["formatted_address"] + "'\nis your start or end point!",
                      showConfirmButton: false,
                      timer: 1500  // Close the modal after 1.5 seconds
                  });
                    return true;
                }
                return false; //working :D!
            }

            // Define a function to handle form submission
            function handleFormSubmit(event) {
                // Prevent the default form submission behavior
                event.preventDefault();

                // Call saveRouteToDatabase() with the required parameters
                saveRouteToDatabase(
                    document.getElementById('route-name').value,
                    start["formatted_address"],
                    end["formatted_address"],
                    waypoint.map(w => w["formatted_address"])
                );
            }

            // Find the form element and attach the handleFormSubmit function to its submit event
            document.getElementById('routeForm').addEventListener('submit', handleFormSubmit);

            // AJAX request to save route data
            function saveRouteToDatabase(routeName, start, end, waypoints) {
                $.ajax({
                    url: '/save-route',
                    type: 'POST',
                    data: {
                        '_token': '{{ csrf_token() }}',
                        'route-name': routeName,
                        'start-point': start,
                        'end-point': end,
                        'waypoints': waypoints
                    },
                    success: function(response) {
                        Swal.fire({
                        icon: 'success',
                        title: 'Route saved successfully!',
                        showConfirmButton: false,
                        timer: 1500 // Close the modal after 1.5 seconds
                        });
                    },
                    error: function(response) {
                        Swal.fire({
                        icon: 'error',
                        title: 'Failed to save the route.',
                        showConfirmButton: false,
                        timer: 1500 // Close the modal after 1.5 seconds
                        });
                    }
                });
            }
</script>
<!-- Google Maps API -->
<script
  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDQ7i1-nzWroLzQFBJlFvSypqXhXoc3kGU&callback=initMap&libraries=places"
  async defer></script>

<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-6LNBE7SQ3G"></script>
<script>
  window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', 'G-6LNBE7SQ3G');
</script>
@endsection
