<script src='https://api.tiles.mapbox.com/mapbox-gl-js/v0.51.0/mapbox-gl.js'></script>
<script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v2.3.0/mapbox-gl-geocoder.min.js'></script>
<script>
  var statusSaved = {{ status }};
  $(function() {

    // tabs
    var $tabs = $(".tabs a");
    var $tab_contents = $(".tab-content .tab");

    $tabs.click(function (e) {
      e.preventDefault();
      var index = $tabs.index(this);

      $tabs.removeClass("active-da");
      $tabs.eq(index).addClass("active-da");

      $tab_contents.removeClass("active-da");
      $tab_contents.eq(index).addClass("active-da");
    });



  });
  var addressesFeaturesAlpha = [];
  var addressesFeaturesBeta = [];
  // This will let you use the .remove() function later on
  if (!('remove' in Element.prototype)) {
    Element.prototype.remove = function () {
      if (this.parentNode) {
        this.parentNode.removeChild(this);
      }
    };
  }
  mapboxgl.accessToken = 'pk.eyJ1IjoiamVyYWxkZmVsbGVyIiwiYSI6ImNqcGJ2bHoydDBhNDIzcXAwODh5bndraHUifQ.y4Y4GACql1QSVRh4JBqqwQ';
  // This adds the map to your page

  getPoiDa().then(
    result => {
      console.log(addressesFeaturesAlpha);
      console.log(addressesFeaturesBeta);

      var map = new mapboxgl.Map({
        // container id specified in the HTML
        container: 'map',
        // style URL
        style: '{{ template }}',
        // initial position in [lon, lat] format
        center: [{{ center[0] }}, {{ center[1] }}],
        // initial zoom
        zoom: 9
      });

      var geocoder = new MapboxGeocoder({
        accessToken: mapboxgl.accessToken,
        zoom: 13,
        country: 'au',
        limit: 10
      });

      map.addControl(geocoder);
      map.addControl(new mapboxgl.FullscreenControl());

      map.on('load', function (e) {
        // add layer for addresses
        map.addLayer({
          id: 'locations-alpha',
          type: 'circle',
          // Add a GeoJSON source containing place coordinates and information.
          source: {
            type: 'geojson',
            data: addressesFeaturesAlpha
          },
          paint: {
            'circle-color': '#6773f1',
            'circle-radius': 3.5,
            'circle-opacity': 1,
            'circle-stroke-color': '#ffffff',
            'circle-stroke-width': 1
          }
        });

        map.addLayer({
          id: 'locations-beta',
          type: 'circle',
          // Add a GeoJSON source containing place coordinates and information.
          source: {
            type: 'geojson',
            data: addressesFeaturesBeta
          },
          paint: {
            'circle-color': '#2f343d',
            'circle-radius': 3.5,
            'circle-opacity': 1,
            'circle-stroke-color': '#ffffff',
            'circle-stroke-width': 1
          }
        });


        // single poing for geocode result
        map.addSource('single-point', {
          "type": "geojson",
          "data": {
            "type": "FeatureCollection",
            "features": []
          }
        });

        map.addLayer({
          "id": "point",
          "source": "single-point",
          "type": "circle",
          "paint": {
            "circle-radius": 10,
            "circle-color": "#9ed166",
            'circle-stroke-color': '#ffffff',
            'circle-stroke-width': 1
          }
        });


        geocoder.on('result', function (ev) {
          map.getSource('single-point').setData(ev.result.geometry);
        });



        buildLocationList();


        $('.geocoder-icon-search').click(function(){
          $('.mapboxgl-ctrl-geocoder').find('input[type=text]:eq(0)').focus();
        });


        // toggle app slider

        $('.mapbox-side-bar-toggle').click(function(){
          if($('.toggle-icon').hasClass('ion-ios7-arrow-back')){
            $(this).attr('title', 'toggle');
            $('#app-sidebar').addClass('side-bar-collapse');
            $('#app-message').addClass('map-container-full-width');
            $('.toggle-icon').removeClass('ion-ios7-arrow-back').addClass('ion-ios7-arrow-forward');
          }else{
            $(this).attr('title', 'collapse');
            $('#app-sidebar').removeClass('side-bar-collapse');
            $('#app-message').removeClass('map-container-full-width');
            $('.toggle-icon').removeClass('ion-ios7-arrow-forward').addClass('ion-ios7-arrow-back');
          }
          setTimeout(function(){
            map.resize();
          }, 200);
        });

      });



      // Add an event listener for when a user clicks on the map
      map.on('click', function (e) {
        // Query all the rendered points in the view
        var features = map.queryRenderedFeatures(e.point, {layers: ['locations-alpha', 'locations-beta']});
        if (features.length) {
          var clickedPoint = features[0];
          // 1. Fly to the point
//          flyToStore(clickedPoint);
          // 2. Close all other popups and display popup for clicked store
          createPopUp(clickedPoint);
          // 3. Highlight listing in sidebar (and remove highlight for all other listings)
          var activeItem = document.getElementsByClassName('active');
          if (activeItem[0]) {
            activeItem[0].classList.remove('active');
          }
          // Find the index of the store.features that corresponds to the clickedPoint that fired the event listener
          var selectedFeature = clickedPoint.properties.address;
          var selectedType = clickedPoint.properties.type;

        }
      });

      function flyToStore(currentFeature) {
        map.flyTo({
          center: currentFeature.geometry.coordinates,
          //zoom: 10
        });
      }

      function createPopUp(currentFeature) {
        var popUps = document.getElementsByClassName('mapboxgl-popup');
        // Check if there is already a popup on the map and if so, remove it
        if (popUps[0]) popUps[0].remove();
        var popup = new mapboxgl.Popup({closeOnClick: false})
          .setLngLat(currentFeature.geometry.coordinates)
          .setHTML('<h5 class="popover-title">' + currentFeature.properties.address + '</h5>'
            + '<div class="pop-body"><b>'+currentFeature.properties.councilReference+'</b><br>' + currentFeature.properties.description + '</div>')
          .addTo(map);
      }


      function buildLocationList() {
        // Iterate through the list of stores
        var dataSet = addressesFeaturesAlpha.features;
        for (i = 0; i < dataSet.length; i++) {
          var currentFeature = dataSet[i];
          // Shorten data.feature.properties to just `prop` so we're not
          // writing this long form over and over again.
          var prop = currentFeature.properties;
          var geo = currentFeature.geometry;
          // Select the listing container in the HTML and append a div
          // with the class 'item' for each store
          var listings = document.getElementById('listings-alpha');



          var listing = listings.appendChild(document.createElement('div'));
          listing.className = 'message clearfix';
          listing.id = 'listing-alpha' + i;


          // add start container
          var starContainer = listing.appendChild(document.createElement('div'));
          starContainer.className = 'mapbox-star-container col-sm-1';

          var starBox = starContainer.appendChild(document.createElement('div'));
          starBox.className = 'star-box';
          var star = starBox.appendChild(document.createElement('i'));
          var starred = (prop.status == 2 ? 'ion-ios7-star starred' : 'ion-ios7-star-outline');
          star.className = "saveDas star-icon  font-size-22 "+ starred;
          star.dataset.poiId = prop.poiId,
            star.dataset.dasId = prop.dasId

          var detailsContainer = listing.appendChild(document.createElement('div'));
          detailsContainer.className = 'col-sm-10';

          var address = detailsContainer.appendChild(document.createElement('div'));
          address.innerHTML = prop.address;

          // Create a new link with the class 'title' for each store
          // and fill it with the store address
          var link = detailsContainer.appendChild(document.createElement('a'));
          link.href = '{{ url() }}leads/'+prop.dasId+'/view?from=poi/alert'+(statusSaved == 2 ? '/saved' : '');
          link.className = 'title';
          link.dataPosition = i;
          link.innerHTML = prop.councilReference;
          // Create a new div with the class 'details' for each store
          // and fill it with the city and phone number
          var details = detailsContainer.appendChild(document.createElement('div'));
          details.innerHTML = prop.councilName;
        }

        // Iterate through the list of stores
        var dataSet = addressesFeaturesBeta.features;
        for (i = 0; i < dataSet.length; i++) {
          var currentFeature = dataSet[i];
          // Shorten data.feature.properties to just `prop` so we're not
          // writing this long form over and over again.
          var prop = currentFeature.properties;
          var geo = currentFeature.geometry;
          // Select the listing container in the HTML and append a div
          // with the class 'item' for each store
          var listings = document.getElementById('listings-beta');
          var listing = listings.appendChild(document.createElement('div'));
          listing.className = 'message clearfix';
          listing.id = 'listing-beta' + i;


          // add start container
          var starContainer = listing.appendChild(document.createElement('div'));
          starContainer.className = 'mapbox-star-container';
          var star = starContainer.appendChild(document.createElement('i'));
          var starred = (prop.status == 2 ? 'ion-ios7-star starred' : 'ion-ios7-star-outline');
          star.className = "saveDas star-icon  font-size-22 "+ starred;
          star.dataset.poiId = prop.poiId,
            star.dataset.dasId = prop.dasId


          var address = listing.appendChild(document.createElement('div'));
          address.innerHTML = prop.address;

          // Create a new link with the class 'title' for each store
          // and fill it with the store address
          var link = listing.appendChild(document.createElement('a'));

          link.href = '{{ url() }}leads/'+prop.dasId+'/view?from=poi/alert'+(statusSaved == 2 ? '/saved' : '');
          link.className = 'title';
          link.dataPosition = i;
          link.innerHTML = prop.councilReference;
          // Create a new div with the class 'details' for each store
          // and fill it with the city and phone number
          var details = listing.appendChild(document.createElement('div'));
          details.innerHTML = prop.councilName;
        }


        $('.saveDas').click(function(){
          $dasId = $(this).attr('data-das-id');
          $poiId = $(this).attr('data-poi-id');
          $status = $(this).hasClass('starred');
          console.log($dasId, $poiId, $status);

          if($status == false){
            $(this).removeClass('ion-ios7-star-outline').addClass('ion-ios7-star').addClass('starred');
          }else{
            $(this).removeClass('starred').removeClass('ion-ios7-star').addClass('ion-ios7-star-outline');
          }

          $.ajax({
            url: '{{ url('poi/saveDa?ajax=1') }}',
            type: 'POST',
            data: {
              dasId: $dasId,
              poiId: $poiId,
              status: $status
            },
            dataType: 'json'
          }).done(function (response) {
            console.log(response);
          });


        });

      }
    }
  );





  function getPoiDa(){
    return new Promise((resolve, reject) => {
      $.ajax({
        url: '{{ url('poi/getPoiAlerts?ajax=1') }}',
        type: 'POST',
        data: {
          address: '',
          type: [1,2],
          status: {{ status }}
        },
        dataType: 'json'
      }).done(function (response) {
        // seperate Alpha to Beta
        var addressesAlpha = [];
        var addressesBeta = [];
        for(var i = 0; i < response.length; i++){
          let data = {
            "type": "Feature",
            "geometry": {
              "type": "Point",
              "coordinates": [
                response[i].longitude,
                response[i].latitude
              ]
            },
            "properties": {
              "address": response[i].address,
              "councilReference": response[i].councilReference,
              "lodgeDate": response[i].lodgeDate,
              "councilName": response[i].councilName,
              "description": response[i].description,
              "dasId": response[i].dasId,
              "poiId": response[i].poiId,
              "type": (response[i].type == 1 ? 'alpha' : 'beta'),
              "status": response[i].status
            }
          }

          if(response[i].type == 1){
            addressesAlpha.push(data);
          }else{
            addressesBeta.push(data);
          }
        }

        addressesFeaturesAlpha = {
          "type": "FeatureCollection",
          "features": addressesAlpha
        };
        addressesFeaturesBeta = {
          "type": "FeatureCollection",
          "features": addressesBeta
        };
        resolve(response);

      });
    })
  }
</script>