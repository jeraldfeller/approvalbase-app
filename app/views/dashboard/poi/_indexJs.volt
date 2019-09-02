<script src="{{ url("dashboard_assets/js/vendor/moment/min/moment.min.js") }}"></script>
<script src="{{ url("dashboard_assets/js/vendor/bootstrap-daterangepicker/daterangepicker.js") }}"></script>
<script src='https://api.tiles.mapbox.com/mapbox-gl-js/v0.51.0/mapbox-gl.js'></script>
<script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v2.3.0/mapbox-gl-geocoder.min.js'></script>
<script>
  var formMode = 'add';
  var hasSelectedFromGeocoder = false;
  var currentSelectedAddress = '';
  var features = [];
  var addressesFeatures = [];
  var data = [];
  var poiType = {{ type }};
  var table = null;
  var map = {};
  $(document).on('click', '#showModal', function () {


    $metaDataDefault = (poiType == 2 ? true : false);
    $action = $(this).attr('data-action');
    if ($action == 'edit') {
      $address = $(this).attr('data-address');
      $('#modalTitle').html('Update POI');
    } else {
      $address = $('#input_address').val();
      $('#modalTitle').html('Create POI');
    }

    $('#input_address').val($address);
    currentSelectedAddress = $address;
    $('#form-modal').modal('show');

    // direct address
    if ($address == '') {
      $('#type').val({{ type }});
      $('#input_name').val('');
      $('#input_radius').val(0.2);
      $('#cost-to').val('9999999999');
      $('#cost-from').val(0);
      $('#input_metadata').prop('checked', $metaDataDefault);
      $('#input_id').val('');
      $('.modal-loader').addClass('display-none');
      $('.form-container').removeClass('display-none');
    } else {
      // check if address already exists
      getPoi($address).then(
        result => {
          if (result.length > 0) {
            $('#type').val(result[0].type).trigger('change');
            $('#input_name').val(result[0].name);
            $('#input_radius').val(result[0].radius);
            $('#cost-to').val(result[0].maxCost);
            $('#cost-from').val(result[0].minCost);
            $('#input_metadata').prop('checked', result[0].metadata);
            $('#input_id').val(result[0].id);
            formMode = 'edit'
          } else {
            formMode = 'add'
            $('#type').val({{ type }});
            $('#input_name').val('');
            $('#input_radius').val(0.2);
            $('#cost-to').val('9999999999');
            $('#cost-from').val(0);
            $('#input_metadata').prop('checked', $metaDataDefault);
            $('#input_id').val('');
          }

          $('.modal-loader').addClass('display-none');
          $('.form-container').removeClass('display-none');


        }
      );
    }










  });
  $(document).ready(function () {


    $('#poiSave').click(function () {
      $name = $('#input_name').val();
      if ($name == '') {
        alert('Please enter Asset name');
        $('#input_name').focus();
        return false;
      }
      $btn = $(this);
      $btn.html('<i class="fa fa-spinner fa-spin"></i>');
      $btn.prop('disabled', true);
      $type = {{ type }};


      $address = $('#input_address').val();
      $radius = $('#input_radius').val();
      $max = parseInt($('#cost-to').val());
      $min = parseInt($('#cost-from').val());
      $costFrom = ($min > $max ? $max : $min);
      $costTo = ($max < $min ? $min : $max);
      $metadata = $('#input_metadata').is(':checked');
      $id = $('#input_id').val();

      if ($address == currentSelectedAddress) {
        $latitude = $('#input_latitude').val();
        $longitude = $('#input_longitude').val();
      } else {
        // changed address
        $latitude = '';
        $longitude = '';
      }
      if ($radius != 0 || $type == 2) {
        $.ajax({
          url: '{{ url('poi/save?ajax=1') }}',
          type: 'POST',
          data: {
            type: $type,
            name: $name,
            address: $address,
            radius: $radius,
            costFrom: $costFrom,
            costTo: $costTo,
            metadata: $metadata,
            latitude: $latitude,
            longitude: $longitude,
            mode: formMode,
            id: $id
          },
          dataType: 'json'
        }).done(function (response) {
          if (response.success == true) {
            if (formMode == 'add') {
              showNotification('Asset successfully created.', 'success');
            } else {
              showNotification('Asset successfully updated.', 'success');
            }

            $('#form-modal').modal('hide');

            // reset form
            $('#type').val(1);
            $('#input_name').val('');
            $('#input_address').val('');
            $('#input_radius').val('');
            $('#cost-to').val(9999999999);
            $('#cost-from').val(0);
            $('#input_metadata').prop('checked', false);
            $('#input_id').val('');
            $('#input_longitude').val(''    );
            $('#input_latitude').val('');
            hasSelectedFromGeocoder = false;
            currentSelectedAddress = '';

            if(poiType == 1){
              window.location.href = "primary?center="+$longitude+","+$latitude;
            }else{
              window.location.href = "secondary?center="+$longitude+","+$latitude;
            }


          } else {
            showNotification(response.message, 'error');
            $('#input_address').focus();
          }


          $btn.html('Save');
          $btn.prop('disabled', false);

        });

      } else {
        showNotification('Radius cannot be 0.', 'info');
        $('#input_radius').focus();
        $btn.html('Save');
        $btn.prop('disabled', false);
      }

    });


    // edit save

    $('#poiSaveEdit').click(function () {
      $name = $('#input_name_edit').val();
      if ($name == '') {
        alert('Please enter POI name');
        $('#input_name').focus();
        return false;
      }
      $btn = $(this);
      $btn.html('<i class="fa fa-spinner fa-spin"></i>');
      $btn.prop('disabled', true);
      $type = {{ type }};
      $address = $('#input_address_edit').val();

      $radius = $('#input_radius_edit').val();
      $max = parseInt($('#cost-to_edit').val());
      $min = parseInt($('#cost-from_edit').val());
      $costFrom = ($min > $max ? $max : $min);
      $costTo = ($max < $min ? $min : $max);
      $metadata = $('#input_metadata_edit').is(':checked');

      $id = $('#input_id_edit').val();


      $.ajax({
        url: '{{ url('poi/save?ajax=1') }}',
        type: 'POST',
        data: {
          type: $type,
          name: $name,
          address: $address,
          radius: $radius,
          costFrom: $costFrom,
          costTo: $costTo,
          metadata: $metadata,
          mode: 'edit',
          id: $id
        },
        dataType: 'json'
      }).done(function (response) {
        if (response.success == true) {
          showNotification('POI successfully updated.', 'success');
          setTimeout(function () {
            console.log(response);
            if(poiType == 1){
              window.location.href = "primary?center="+response.coordinates[0]+","+response.coordinates[1];
            }else{
              window.location.href = "secondary?center="+response.coordinates[0]+","+response.coordinates[1];
            }
          }, 500);
        } else {
          showNotification(response.message, 'error');
        }


        $btn.html('Save');
        $btn.prop('disabled', false);
      });

    });


    // import csv function
    $('#form').on('submit',function(e){
      e.preventDefault();
      var formData = new FormData($(this)[0]);
      $.ajax({
        url: '/poi/import',
        type: 'POST',
        xhr: function() {
          var myXhr = $.ajaxSettings.xhr();
          $('#submitBtn').attr('disabled', true).html('Importing....');
          return myXhr;
        },
        success: function (data) {
          if(data == 1){
            alert('CSV successfully imported.');
            location.reload();
          }else{
            alert('Oops somehting went wrong, please try again.')
          }
          $('#submitBtn').removeAttr('disabled').html('Import');
        },
        data: formData,
        cache: false,
        contentType: false,
        processData: false
      });
      return false;
    });


      // change map view
      $('.map-style-change').click(function(){
          $('.map-style-change').removeClass('btn-primary').addClass('btn-default');
          $('.map-style-change').attr('disabled', false);
          $(this).addClass('btn-primary');
          $(this).attr('disabled', 'disabled');
          $mapStyle = $(this).attr('data-style');
          map.setStyle($mapStyle);
      });

  });


  function getPoi(address = '', id = 0) {
    $('.modal-loader').removeClass('display-none');
    $('.form-container').addClass('display-none');
    return new Promise((resolve, reject) => {
      $.ajax({
        url: '{{ url('poi/get?ajax=1') }}',
        type: 'POST',
        data: {
          address: address,
          type: {{ type }},
          id: id
        },
        dataType: 'json'
      }).done(function (response) {

        features = [];
        if (response.data.length > 0) {
          for (var x = 0; x < response.data.length; x++) {
            features.push({
              "type": "Feature",
              "geometry": {
                "type": "Point",
                "coordinates": [
                  parseFloat(response.data[x].longitude),
                  parseFloat(response.data[x].latitude)
                ]
              },
              "properties": {
                "address": response.data[x].address,
                "country": "Australia",
                "radius": response.data[x].radius,
                "type": 'poi',
                "id": response.data[x].id,
                "name": response.data[x].name,

              }
            });
          }
        }

        if (address == '') {
          if (response.addresses.length > 0) {
            var addresses = [];
            for (var i = 0; i < response.addresses.length; i++) {
              for (var a = 0; a < response.addresses[i].length; a++) {
                addresses.push(
                  {
                    "type": "Feature",
                    "geometry": {
                      "type": "Point",
                      "coordinates": [
                        response.addresses[i][a].longitude,
                        response.addresses[i][a].latitude
                      ]
                    },
                    "properties": {
                      "address": response.addresses[i][a].address,
                      "councilReference": response.addresses[i][a].councilReference,
                      "description": response.addresses[i][a].description,
                      "type": 'da',
                      "poiId": response.addresses[i][a].poiId,
                      "documents": response.addresses[i][a].documents,
                      "dasId": response.addresses[i][a].dasId,
                      "council": response.addresses[i][a].council,
                      "lodgeDate": response.addresses[i][a].lodgeDate,
                      "lodgeDateUnix": response.addresses[i][a].lodgeDateUnix,
                      "dotColor": response.addresses[i][a].dotColor
                    }
                  }
                )
              }

            }

            addressesFeatures = {
              "type": "FeatureCollection",
              "features": addresses,

            };

            console.log(addressesFeatures);
          }
        }


        resolve(response.data);
      });
    });
  }

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

  // get poi

  getPoi().then(
    result => {

       map = new mapboxgl.Map({
        // container id specified in the HTML
        container: 'map',
        // style URL
        style: '{{ template }}',
        // initial position in [lon, lat] format
        center: [{{ center[0] }}, {{ center[1] }}],
        // initial zoom
        zoom: 15
      });

      var geocoder = new MapboxGeocoder({
        accessToken: mapboxgl.accessToken,
        zoom: 18,
        country: 'au',
        limit: 10
      });

      map.addControl(geocoder);
      map.addControl(new mapboxgl.FullscreenControl());


      data = {
        "type": "FeatureCollection",
        "features": features
      }
      map.on('load', function (e) {

        $('.mapboxgl-ctrl-geocoder').find('input[type=text]:eq(0)').on('change', function () {
          $('#input_address').val($(this).val());
        });

        var createGeoJSONCircle = function (center, radiusInKm, points) {
          if (!points) points = 64;

          var coords = {
            latitude: center[1],
            longitude: center[0]
          };

          var km = radiusInKm;

          var ret = [];
          var distanceX = km / (111.320 * Math.cos(coords.latitude * Math.PI / 180));
          var distanceY = km / 110.574;

          var theta, x, y;
          for (var i = 0; i < points; i++) {
            theta = (i / points) * (2 * Math.PI);
            x = distanceX * Math.cos(theta);
            y = distanceY * Math.sin(theta);

            ret.push([coords.longitude + x, coords.latitude + y]);
          }
          ret.push(ret[0]);

          return {
            "type": "geojson",
            "data": {
              "type": "FeatureCollection",
              "features": [{
                "type": "Feature",
                "geometry": {
                  "type": "Polygon",
                  "coordinates": [ret]
                }
              }]
            }
          };
        };


        map.addSource('data', {
          'type': 'geojson',
          'data': data
        });

        for (var x = 0; x < result.length; x++) {
          var poly = createGeoJSONCircle([parseFloat(result[x].longitude), parseFloat(result[x].latitude)], result[x].radius, 64);
          map.addSource("polygon" + x, poly);
          map.addLayer({
            "id": "polygon" + x,
            "type": "fill",
            "source": "polygon" + x,
            "layout": {},
            "paint": {
              "fill-color": "rgba(103, 115, 241, 0.1)",
              "fill-outline-color": "#6773f1",
              "fill-opacity": 1,
              "fill-antialias": true,
            }
          });
        }


        // add layer for addresses
        map.addLayer({
          id: 'locations',
          type: 'circle',
          // Add a GeoJSON source containing place coordinates and information.
          source: {
            type: 'geojson',
            data: addressesFeatures
          },
          paint: {
            {#'circle-color': '{{ dotColor }}',#}
            'circle-color': [
              'match',
              ['get', 'dotColor'],
              'white', '#ffffff',
              'purple', '{{ dotColor }}',
                /* other */ '#000'
            ],
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

        var singlePoint = [];


        // Listen for the `result` event from the MapboxGeocoder that is triggered when a user
        // makes a selection and add a symbol that matches the result.
        geocoder.on('result', function (ev) {
          map.getSource('single-point').setData(ev.result.geometry);
          singlePoint.push(ev.result);
          $long = ev.result.center[0];
          $lat = ev.result.center[1];

          $('#input_longitude').val($long);
          $('#input_latitude').val($lat);

          hasSelectedFromGeocoder = true;
          $('#showModal').attr('data-action', 'add');

          // add marker
          createMarker(singlePoint[singlePoint.length - 1]);

        });

        buildLocationList(data);

        data.features.forEach(function (marker) {
          // Create a div element for the marker
          var el = document.createElement('div');
          // Add a class called 'marker' to each div
          el.className = 'marker';
          // By default the image for your custom marker will be anchored
          // by its center. Adjust the position accordingly
          // Create the custom markers, set their position, and add to map
          new mapboxgl.Marker(el, {offset: [0, -15]})
            .setLngLat(marker.geometry.coordinates)
            .addTo(map);


          el.addEventListener('click', function (e) {
            var activeItem = document.getElementsByClassName('active');
            // 1. Fly to the point
//            flyToStore(marker);
            // 2. Close all other popups and display popup for clicked store
            createPopUp(marker);
            // 3. Highlight listing in sidebar (and remove highlight for all other listings)
            e.stopPropagation();
            if (activeItem[0]) {
              activeItem[0].classList.remove('active');
            }
            var listing = document.getElementById('listing-' + i);
            console.log(listing);
            listing.classList.add('active');
          });
        });


        $('.geocoder-icon-search').click(function () {
          $('.mapboxgl-ctrl-geocoder').find('input[type=text]:eq(0)').focus();
        });


        // toggle app slider

        $('.mapbox-side-bar-toggle').click(function () {
          if ($('.toggle-icon').hasClass('ion-ios7-arrow-back')) {
            $(this).attr('title', 'toggle');
            $('#app-sidebar').addClass('side-bar-collapse');
            $('#app-message').addClass('map-container-full-width');
            $('.toggle-icon').removeClass('ion-ios7-arrow-back').addClass('ion-ios7-arrow-forward');
          } else {
            $(this).attr('title', 'collapse');
            $('#app-sidebar').removeClass('side-bar-collapse');
            $('#app-message').removeClass('map-container-full-width');
            $('.toggle-icon').removeClass('ion-ios7-arrow-forward').addClass('ion-ios7-arrow-back');
          }
          setTimeout(function () {
            map.resize();
          }, 200);

        });


      });


      // Add an event listener for when a user clicks on the map
      map.on('click', function (e) {
        // Query all the rendered points in the view
        var features = map.queryRenderedFeatures(e.point, {layers: ['locations']});
        if (features.length) {
          var clickedPoint = features[0];


          // For da circle on click enlarge circle and select in da list
          map.setPaintProperty("locations", 'circle-radius', ["case", ["==", ["get", "address"], clickedPoint.properties.address], 7, 3.5]);

          var activeItem = document.getElementsByClassName('active-selected');
          if (activeItem[0]) {
            activeItem[0].classList.remove('active-selected');
          }



//          $('.da-list-row').removeClass('shown-mb');

          // show list

          setTimeout(function(){
            console.log('THIS');
            $target = '.da-'+clickedPoint.properties.dasId+'-'+clickedPoint.properties.address.replace(/ /g, '-').replace(/,/g, '-').replace(/:/g, '-').replace(/\//g, '-').replace(/\./g, '-') + ' .redirect:eq(0)';
            $targetScroll = '.da-'+clickedPoint.properties.dasId+'-'+clickedPoint.properties.address.replace(/ /g, '-').replace(/,/g, '-').replace(/:/g, '-').replace(/\//g, '-').replace(/\./g, '-');
            $('.poi_' + clickedPoint.properties.poiId + '').addClass('active-selected');
            if ($('.poi_' + clickedPoint.properties.poiId + '').find('.poi-action-show-list').hasClass('fa-angle-double-down')) {
                  $('.poi_' + clickedPoint.properties.poiId + '').find('.poi-action-show-list').trigger('click');
            }

              $($target).trigger('click');
              setTimeout(function(){
                  console.log($($targetScroll+'-input'));
                  $($targetScroll+'-input').trigger('focus');
              }, 500);
            // console.log($('.scroll-wrapper ' + $targetScroll).offset().top);
            //       $('.scroll-wrapper').animate({
            //         // scrollTop: $('.da-'+clickedPoint.properties.dasId+'-'+clickedPoint.properties.address.replace(/ /g, '-').replace(/,/g, '-').replace(/:/g, '-')).offset().top
            //           scrollTop: $($target).offset().top
            //       }, 500);
        }, 500);





          // 1. Fly to the point
//          flyToStore(clickedPoint);
          // 2. Close all other popups and display popup for clicked store
//          createPopUp(clickedPoint);
          // 3. Highlight listing in sidebar (and remove highlight for all other listings)
          var activeItem = document.getElementsByClassName('active');
          if (activeItem[0]) {
            activeItem[0].classList.remove('active');
          }
          // Find the index of the store.features that corresponds to the clickedPoint that fired the event listener
          var selectedFeature = clickedPoint.properties.address;

          for (var i = 0; i < data.features.length; i++) {
            if (data.features[i].properties.address === selectedFeature) {
              selectedFeatureIndex = i;
            }
          }
          // Select the correct list item using the found index and add the active class
//          var listing = document.getElementById('listing-' + selectedFeatureIndex);
//          listing.classList.add('active');
        }
      });

      function flyToStore(currentFeature) {
        if (currentFeature.properties.type == 'poi') {
          map.flyTo({
            center: currentFeature.geometry.coordinates,
            zoom: 15
          });
        } else {
          map.flyTo({
            center: currentFeature.geometry.coordinates,
          });
        }
      }

      function createPopUp(currentFeature) {
        console.log('Locality');
        console.log(currentFeature);

        var popUps = document.getElementsByClassName('mapboxgl-popup');
        // Check if there is already a popup on the map and if so, remove it
        if (popUps[0]) popUps[0].remove();
        if (currentFeature.properties.type == 'da') {

//          var activeItem = document.getElementsByClassName('active-selected');
//          if (activeItem[0]) {
//            activeItem[0].classList.remove('active-selected');
//          }
//
//          $('.poi_' + currentFeature.properties.poiId + '').addClass('active-selected');
//          if ($('.poi_' + currentFeature.properties.poiId + '').find('.poi-action-show-list').hasClass('fa-angle-double-down')) {
//            $('.poi_' + currentFeature.properties.poiId + '').find('.poi-action-show-list').trigger('click');
//          }
//
//
//          var documentsHtml = "<ul class='da-list-documents'>";
//          var documents = JSON.parse(currentFeature.properties.documents);
//          console.log(typeof documents);
//          $.each(documents, function (x, y) {
//            documentsHtml += "<li class='da-li-documents'><a class='title' target='_blank' href='" + y.url + "'>" + y.name + "</a></li>";
//          });
//          documentsHtml += "</ul>";
//
//          if (documents.length == 0) {
//            documentsHtml = 'No documents available';
//          }
//
//
//          var popup = new mapboxgl.Popup({closeOnClick: false})
//            .setLngLat(currentFeature.geometry.coordinates)
//            .setHTML('<h5 class="popover-title">' + currentFeature.properties.address + '</h5>'
//              + '<div class="pop-body"><b>' + currentFeature.properties.councilReference + '</b><br>' + currentFeature.properties.description + '<br><div class="mapbox-document-title">Documents <button onclick=\'downloadAllPdf(this)\' data-council=\'' + currentFeature.properties.council + '\' data-id=\'' + currentFeature.properties.dasId + '\' class=\'btn btn-default downloadAllPdf download-all-btn btn-sm\' id=\'downloadPdfBtn\' title=\'Download PDF\'><i class=\'fa fa-download\'></i></button></div>' + documentsHtml + '</div>')
//            .addTo(map);
//


        } else {
          if (typeof currentFeature.properties.name != 'undefined') {
            var popup = new mapboxgl.Popup({closeOnClick: false})
              .setLngLat(currentFeature.geometry.coordinates)
              .setHTML('<h5 class="popover-title">' + currentFeature.properties.name + '</h5>'
                + '<div class="pop-body">' +
                currentFeature.properties.address +
                '<div class="pop-body"><i data-address="' + currentFeature.properties.address + '" data-id="' + currentFeature.properties.id + '" data-action="edit" onclick="poiAction(this)" style="margin-top: 1px;" title="edit" class="fa fa-edit poi-action pull-right"></i><i data-address="' + currentFeature.properties.address + '" data-id="' + currentFeature.properties.id + '" data-action="delete" onclick="poiAction(this)" title="delete" class="fa fa-trash-o poi-action pull-right"></i></div>' +
                '</div>')
              .addTo(map);

          } else {
            var popup = new mapboxgl.Popup({closeOnClick: false})
              .setLngLat(currentFeature.geometry.coordinates)
              .setHTML('<h5 class="popover-title">' + currentFeature.place_name + '</h5>'
                + '<div class="pop-body text-center">' +
                '<a class="bulk-action-button btn btn-default" id="showModal" data-action="add">Create Asset</a>' +
                '</div>')
              .addTo(map);
          }

          setTimeout(function(){
            popup.remove();
          }, 3000);


        }


      }


      function buildLocationList(data, appendLast = false) {
        // Iterate through the list of stores
        if (appendLast == true) {
          var dataSet = [data.features[data.features.length - 1]];
        } else {
          var dataSet = data.features;
        }

        console.log(dataSet);
        for (i = 0; i < dataSet.length; i++) {
          var currentFeature = dataSet[i];
          // Shorten data.feature.properties to just `prop` so we're not
          // writing this long form over and over again.
          var prop = currentFeature.properties;
          console.log('PROP');
          console.log(prop);
          var geo = currentFeature.geometry;
          // Select the listing container in the HTML and append a div
          // with the class 'item' for each store
          var listings = document.getElementById('listings');
          var listing = listings.appendChild(document.createElement('div'));
          listing.className = 'message clearfix poi_' + prop.id;
          listing.id = 'listing-' + i;

          var actionContainer = document.createElement('div');
          actionContainer.className = 'poi-action-container';

          var actionIcon = actionContainer.appendChild(document.createElement('a'));
          actionIcon.className = 'ion-ios7-more fa-2x rotate-90 action-icon';
          actionIcon.dataset.id = prop.id;
          actionIcon.dataset.toggle = 'popover';
          actionIcon.dataset.container = 'body';
          actionIcon.dataset.content = '<i title="edit" class="fa fa-edit poi-action" data-lat="' + geo.coordinates[1] + '" data-lon="' + geo.coordinates[0] + '" data-address="' + prop.address + '" data-id="' + prop.id + '" data-action="edit" onclick="poiAction(this)"></i><br><i title="delete" class="fa fa-times poi-action" data-id="' + prop.id + '" data-action="delete" onclick="poiAction(this)"></i>';
          actionIcon.dataset.html = true;
          actionIcon.dataset.animation = true;
          actionIcon.dataset.trigger = 'focus';
          actionIcon.href = '#';

          listing.appendChild(actionContainer);


          // Create a new link with the class 'title' for each store
          // and fill it with the store address
          var linkContainer = listing.appendChild(document.createElement('div'));
          linkContainer.className = 'link-container';
          var link = linkContainer.appendChild(document.createElement('a'));
          link.href = '#';
          link.className = 'title title-index-' + i;
          link.dataPosition = i;
          link.innerHTML = '<span class="monitor-list-title">' + prop.name + '</span>';


          // Add an event listener for the links in the sidebar listing
          link.addEventListener('click', function (e) {
            // Update the currentFeature to the store associated with the clicked link
            var clickedListing = data.features[this.dataPosition];

            // 1. Fly to the point associated with the clicked link
            flyToStore(clickedListing);
            // 2. Close all other popups and display popup for clicked store
            createPopUp(clickedListing);
            // 3. Highlight listing in sidebar (and remove highlight for all other listings)
            var activeItem = document.getElementsByClassName('active-selected');
            if (activeItem[0]) {
              activeItem[0].classList.remove('active-selected');
            }
            this.parentNode.parentNode.classList.add('active-selected');
          });


          // Create a new div with the class 'details' for each store
          // and fill it with the city and phone number
          var details = listing.appendChild(document.createElement('div'));
          details.className = 'link-container-address';
          details.innerHTML = prop.address;

          // accordion icon container
          var accordionIcon = listing.appendChild(document.createElement('div'));
          accordionIcon.className = 'accordion-icon-container';
//          accordionIcon.innerHTML = '<i title="show DA" data-parent-index="' + i + '" class="fa fa-angle-double-down poi-action poi-action-show-list" data-toggle="collapse" data-target="#listing-a-' + i + '" aria-expanded="true" data-id="' + prop.id + '" data-container-id="listing-a-' + i + '" onclick="showList(this)"></i>';
          accordionIcon.innerHTML = '<i title="show DA" data-parent-index="' + i + '" class="accordion-icon fa fa-angle-double-down poi-action poi-action-show-list"   data-toggle="collapse" aria-expanded="true" data-id="' + prop.id + '" data-container-id="listing-a-' + i + '" onclick="showList(this)"></i>';

          // add accordion container
          var accordion = listing.appendChild(document.createElement('div'));
          accordion.id = 'listing-a-' + i;
          accordion.className = 'collapse accordion-container accordion';

        }


        $('[data-toggle="popover"]').popover();


      }

      function createMarker(newResult) {
        var el = document.createElement('div');
        // Add a class called 'marker' to each div
        el.className = 'marker';
        // By default the image for your custom marker will be anchored
        // by its center. Adjust the position accordingly
        // Create the custom markers, set their position, and add to map
        console.log(el);
        new mapboxgl.Marker(el, {offset: [0, -15]})
          .setLngLat(newResult.geometry.coordinates)
          .addTo(map);


        el.addEventListener('click', function (e) {
          var activeItem = document.getElementsByClassName('active');
          // 1. Fly to the point
//          flyToStore(newResult);
          // 2. Close all other popups and display popup for clicked store
          createPopUp(newResult);
          // 3. Highlight listing in sidebar (and remove highlight for all other listings)
          e.stopPropagation();
          if (activeItem[0]) {
            activeItem[0].classList.remove('active');
          }
          var listing = document.getElementById('listing-' + i);
          console.log(listing);
          listing.classList.add('active');
        });

      }


    } // end of poi
  )


  function poiAction(elem) {
    $action = elem.getAttribute('data-action');
    $id = elem.getAttribute('data-id');
    $address = elem.getAttribute('data-address');
    $lat = elem.getAttribute('data-lat');
    $lon = elem.getAttribute('data-lon');
    console.log($action, $id);
    if ($action == 'delete') {
      $.ajax({
        url: '{{ url('poi/delete?ajax=1') }}',
        type: 'POST',
        data: {
          action: $action,
          id: $id
        },
        dataType: 'json'
      }).done(function (response) {
        if (response == true) {
          showNotification('POI successfully deleted.', 'success');
          setTimeout(function () {
                $rUrl = removeParams('center');
                console.log($rUrl);
                if($rUrl[1] == 'reload'){
                  window.location.href = $rUrl[0];
                }else{
                  location.reload();
                }
          }, 500);
        } else {
          showNotification('', 'error');
        }
      });
    } else {
//      $('#showModal').attr('data-action', 'edit');
//      $('#showModal').attr('data-address', $address);
//      $('#input_latitude').val($lat);
//      $('#input_longitude').val($lon);
//      $('#showModal').trigger('click');


      $('#input_address_edit').val($address);
      $('#form-modal-edit').modal('show');
      // check if address already exists
      getPoi($address, $id).then(
        result => {
          if (result.length > 0) {
            $('#input_name_edit').val(result[0].name);
            $('#input_radius_edit').val(result[0].radius);
            $('#cost-to_edit').val(result[0].maxCost).trigger('change');
            $('#cost-from_edit').val(result[0].minCost).trigger('change');
            $('#input_metadata_edit').prop('checked', result[0].metadata);
            $('#input_id_edit').val(result[0].id);
          }
          $('.modal-loader').addClass('display-none');
          $('.form-container').removeClass('display-none');
        }
      );
    }

  }


  function showList(elem) {
    // allow only to open one accordion at a time
    $containerId = elem.getAttribute('data-container-id');
    $container = $('#' + $containerId);
    $id = elem.getAttribute('data-id');
    $('.accordion').slideUp();
    if(elem.classList.contains("fa-angle-double-up") == false){
      $($container).slideDown();
    }
    $('.accordion-icon').removeClass('fa-angle-double-up');
    $('.accordion-icon').addClass('fa-angle-double-down');
    if (!$container.hasClass('in')) {
      elem.classList.remove('fa-angle-double-down')
      elem.classList.add('fa-angle-double-up');
      elem.title = 'hide DA';
      $container.html('<i class="fa fa-spinner fa-spin"></i>');
      console.log($containerId);
      $aFeatures = addressesFeatures.features;

      $htmlData = '';
      $.each($aFeatures, function (index, key) {
        if (key.properties.poiId == $id) {

          var documentsHml = "<ul class='da-list-documents'>";
          var documents = key.properties.documents;
          $.each(documents, function (x, y) {
            documentsHml += "<li class='da-li-documents'><a class='title' target='_blank' href='" + y.url + "'>" + y.name + "</a></li>";
          });
          documentsHml += "</ul>";

          if (documents.length == 0) {
            documentsHml = 'No documents available.';
          }
          if(key.properties.description != null){
            $desc = key.properties.description.replace(/'|"/g, '');
          }else{
            $desc = '';
          }

//          $htmlData += '<li class="da-li">' +
//                            '<a ' +
//                            'data-toggle="popover" ' +
//                            'data-container="body" ' +
//                            'data-html="true" ' +
//                            'data-animation="true" ' +
//                            'data-title="<b>'+ key.properties.councilReference+' </b><br> '+$desc+ ' <br><br> Documents <button onclick=\'downloadAllPdf(this)\' data-council=\''+key.properties.council+'\' data-id=\''+key.properties.dasId+'\' class=\'btn btn-default downloadAllPdf download-all-btn btn-sm\' id=\'downloadPdfBtn\' title=\'Download PDF\'><i class=\'fa fa-download\'></i></button>" ' +
//                            'data-content="'+documentsHml+'"' +
//
//               'data-trigger="focus"' +
//                            'class="title" href="#">'+key.properties.address+'</a>' +
//                        '</li>';

          $rowIdentifier = key.properties.dasId+'-'+key.properties.address;
          $htmlData += '<tr data-index="'+index+'" class=" da-list-row hover da-'+$rowIdentifier.replace(/ /g, '-').replace(/,/g, '-').replace(/:/g, '-').replace(/\//g, '-').replace(/\./g, '-')+'" data-dasId="' + key.properties.dasId + '" id="' + key.properties.poiId + '"><td class="da-li text-left ">' + key.properties.lodgeDateUnix + ' </td><td class="da-li text-left  redirect">' + key.properties.lodgeDate + '</td><td class="da-li text-left  redirect"> <input type="text" style="position: absolute; opacity: 0;" class="da-'+$rowIdentifier.replace(/ /g, '-').replace(/,/g, '-').replace(/:/g, '-').replace(/\//g, '-').replace(/\./g, '-')+'-input">' +
            key.properties.address +
            '</td></tr>';
        }
      });

//      $container.html('<ul class="da-list">'+($htmlData != '' ? $htmlData : 'No DA available')+'</ul>');
      if($htmlData != ''){
        $container.html('<table style="width: 100%;" class="table table-hover-dark" id="list-' + $id + '"><thead class="display-none"><tr><th>Timestamp</th><th>Lodge Date</th><th>Address</th></tr></thead><tbody class="tbody">' + $htmlData + '</tbody></table>');

        table = $('#list-' + $id).unbind().DataTable({
//              "bSort" : false,
          "searching": false,
          "bPaginate": false,
          "bLengthChange": false,
          "bFilter": true,
          "bInfo": false,
          "order": [[0, "desc"]],
          "columnDefs": [
            {
              "targets": [0],
              "visible": false,
            }
          ]
        });


        console.log('start');
        $('.tbody .redirect').click(function (e) {
          console.log($(this));
          $poiId = $(this).parent().attr('id');
          $dasId = $(this).parent().attr('data-dasid');
          var tr = $(this).parents('tr');
          var row = table.row(tr);

          if (row.child.isShown()) {
            // This row is already open - close it
            $('.card-slider', row.child()).slideUp(function () {
              row.child.hide();
              tr.removeClass('shown-mb');
            });
          }
          else {
            // Open this row (the format() function would return the data to be shown-mb)
            if (table.row('.shown-mb').length) {
              console.log(table.row('.shown-mb').length);
              $('.redirect', table.row('.shown-mb').node()).click();
            }

            row.child(format($dasId, $poiId)).show();
            row.child().find('td').addClass('no-pdd-left no-pdd-right');
            tr.addClass('shown-mb');
            $('.card-slider', row.child()).slideDown();
            $('.card-block-poi').removeClass('text-left').addClass('text-center');
            // get Documents and Parties
            $.ajax({
              url: '{{ url('search/getDocumentsAndParties?ajax=1') }}',
              type: 'POST',
              data: {
                dasId: $dasId,
                poiId: $poiId,
                from: 'poi'
              },
              dataType: 'json'
            }).done(function (response) {
              setTimeout(function(){
                $('.card-block-poi').removeClass('text-center').addClass('text-left');
                // check if da is saved
                if (response.saved == true) {
                  $('#star-' + $dasId).removeClass('ion-ios7-star-outline').addClass('ion-ios7-star starred').attr('data-starred', true).attr('data-id', $dasId).attr('data-poiId', $id);
                } else {
                  $('#star-' + $dasId).removeClass('ion-ios7-star starred').addClass('ion-ios7-star-outline').attr('data-starred', false).attr('data-id', $dasId).attr('data-poiId', $id);
                }

                // create table

                if (typeof response.info.council != 'undefined') {
                  $infoDesc = (typeof response.info.description != 'undefined' ? response.info.description : '&nbsp;');
                  $('#council-info').html('<strong>' + response.info.council + ' Development Application ' + response.info.councilReference + '</strong>');
                  $('#da-description').html(($infoDesc !=  null ? $infoDesc : '&nbsp;'));
                  $('#council-logo').html('<img class="avatar-poi" src="' + response.info.councilLogo + '">');
                  $infoTable = '<table class="table text-left table-card-box">';
                  $infoTable += '<tbody>';
                  $infoTable += '<tr><td>Council</td><td>' + response.info.council + '</td></tr>';
                  $infoTable += '<tr><td>Lodge date</td><td>' + response.info.lodgeDate + '</td></tr>';
                  $infoTable += '<tr><td>Estimated Cost</td><td>$' + response.info.estimatedCost + '</td></tr>';
                  $infoTable += '</tbody>';
                  $infoTable += '</table>';
                } else {
                  $infoTable = '';
                }
                $('#card-info-container-' + $dasId).html($infoTable);

                if (response.addresses.length > 0) {
                  $addressTable = '<table class="table text-left table-card-box">';
                  $addressTable += '<tbody>';
                  for ($d = 0; $d < response.addresses.length; $d++) {
                    $addressTable += '<tr><td>' + response.addresses[$d] + '</td></tr>';
                  }
                  $addressTable += '</tbody>';
                  $addressTable += '</table>';
                } else {
                  $addressTable = '<p>No address available.</p>';
                }

//                $('#card-addresses-container-' + $dasId).html($addressTable);

                if (response.documents.length > 0) {
                  $docTable = '<table class="table table-card-box text-left">';
                  $docTable += '<tbody>';
                  for ($d = 0; $d < response.documents.length; $d++) {
                    $docTable += '<tr><td><a target="_blank" class="docu-link" href="' + response.documents[$d].url + '">' + response.documents[$d].name + '</a></td></tr>';
                  }
                  $docTable += '</tbody>';
                  $docTable += '</table>';
                } else {
                  $docTable = '<p>No documents available.</p>';
                }

                $('#card-document-container-' + $dasId).html($docTable);

                if (response.parties.length > 0) {
                  $partTable = '<table class="table table-card-box text-left">';
                  $partTable += '<tbody>';
                  for ($p = 0; $p < response.parties.length; $p++) {
                    $partTable += '<tr><td><strong>' + response.parties[$p].role + '</strong></td><td>' + response.parties[$p].name + '</td></tr>';
                  }
                  $partTable += '</tbody>';
                  $partTable += '</table>';
                } else {
                  $partTable = '<p>No parties available.</p>';
                }

                $('#card-party-container-' + $dasId).html($partTable);
              }, 500);



            });



            $('.star').unbind().on('click', function () {
              $starIcon = $(this).find('.star-icon');
              $status = ($starIcon.attr('data-starred') == 'true' ? 1 : 2);
              $leadId = $(this).attr('data-id');
              $poiId = $(this).attr('data-poiid');

              console.log($(this));
              console.log('star');
              console.log($poiId);
              $.ajax({
                url: '{{ url('poi/saveDa?ajax=1') }}',
                type: 'POST',
                data: {
                  dasId: $leadId,
                  poiId: $poiId,
                  status: $status
                },
                dataType: 'json'
              }).done(function (response) {
                if (response == true) {
                  if ($status == 2) {
                    $starIcon.removeClass('ion-ios7-star-outline').addClass('ion-ios7-star').addClass('starred');
                    $starIcon.attr('data-starred', 'true');
                  } else {
                    $starIcon.removeClass('starred').removeClass('ion-ios7-star').addClass('ion-ios7-star-outline');
                    $starIcon.attr('data-starred', 'false');
                  }
                } else {
                  alert('Ops! Something went wrong, please try again.');
                }
              });
            });


            $('.downloadPdfBtn').unbind().click(function () {
              $btn = $(this);
              $btn.html('<i class="fa fa-spinner fa-spin font-size-12"></i>');
              $btn.attr('disabled', true);
              var dasId = $(this).attr('data-id');
              $.ajax({
                url: '{{ url('pdf/downloadPdf?ajax=1') }}',
                type: 'POST',
                data: {
                  id: dasId
                },
                dataType: 'json'
              }).done(function (response) {
                console.log(response);
                if (response != false) {
                  if (response.s == 1) {
                    location.href = '{{ url('pdf/download') }}?file=' + encodeURI(response.file);
                  } else {
                    if (response.links.length > 0) {
                      for (var l = 0; l < response.links.length; l++) {

                        window.open(response.links[l], '_blank');
                      }
                    }
                  }

                } else {
                  showNotification('', 'error');
                }
                $btn.html('<i class="fa fa-download fa-download font-size-12"></i>');
                $btn.attr('disabled', false);

              });
            });
          }


        });
      }else{
        $container.html('<table style="width: 100%;" class="table table-hover-dark" id="list-' + $id + '"><thead class="display-none"><tr><th>na</th></tr></thead><tbody class="tbody"><tr><td >No DA available</td></tr></tbody></table>');
      }

      $('[data-toggle="popover"]').popover();

//      setTimeout(function(){
//        sortByDate();
//      }, 100);





      $('.timeago').timeago();
      $($container).addClass('in');



    } else {
      $($container).removeClass('in');
      elem.classList.remove('fa-angle-double-up');
      elem.classList.add('fa-angle-double-down')
      elem.title = 'show DA';
      console.log('aaaaaa');
    }

    // bind on hover row to highlight address circle

    $('table tr').hover(function(){
      $index = $(this).attr('data-index');
      if(typeof $index != 'undefined'){
        map.setPaintProperty("locations", 'circle-radius', ["case", ["==", ["get", "address"], $aFeatures[$index].properties.address], 7, 3.5]);
      }
    });

  }

//  function reInitTable(table) {
//    console.log('reinit');
//    table.destroy();
//    var table = $('#list-table').DataTable({
////              "bSort" : false,
//      "searching": false,
//      "bPaginate": false,
//      "bLengthChange": false,
//      "bFilter": true,
//      "bInfo": false,
//      "order": [[0, "desc"]],
//      "columnDefs": [
//        {
//          "targets": [0],
//          "visible": false,
//        }
//      ]
//    });
//    $('.timeago').timeago();
//  }

  function downloadAllPdf(elem) {
    elem.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
    elem.disabled = true;
    var id = elem.getAttribute('data-id');
    var council = elem.getAttribute('data-council');
    $.ajax({
      url: '{{ url('pdf/downloadPdf?ajax=1') }}',
      type: 'POST',
      data: {
        id: id,
        council: council
      },
      dataType: 'json'
    }).done(function (response) {
      console.log(response);
      if (response != false) {
        if (response.s == 1) {
          location.href = '{{ url('pdf/download') }}?file=' + encodeURI(response.file);
        } else {
          if (response.links.length > 0) {
            for (var l = 0; l < response.links.length; l++) {

              window.open(response.links[l], '_blank');
            }
          }
        }

      } else {
        showNotification('', 'error');
      }

      elem.innerHTML = '<i class="fa fa-download"></i>';
      elem.disabled = false;

    });

  }


  function convertDate(d) {
    var p = d.split("/");
    return +(p[2] + p[1] + p[0]);
  }

  function sortByDate() {
    var tbody = document.querySelector("#list-table tbody");
    // get trs as array for ease of use
    var rows = [].slice.call(tbody.querySelectorAll("tr"));

    rows.sort(function (a, b) {
      return convertDate(b.cells[0].innerHTML) - convertDate(a.cells[0].innerHTML);
    });

    rows.forEach(function (v) {
      tbody.appendChild(v); // note that .appendChild() *moves* elements
    });
  }

  function format(d, p) {
    $template = '<div class="row card-slider card-slider-poi width-100 no-mrg-right no-mrg-left">' +
      '<div class="col-sm-12 mrg-btm-10 no-pdd-left no-pdd-right">' +
      '<button data-id="' + d + '" data-poiId="'+p+'" class="btn btn-sm btn-default pull-right downloadPdfBtn"  title="Download PDF"><i class="fa fa-download font-size-12"></i></button>' +
      '<button data-id="' + d + '" data-poiId="'+p+'" class="btn btn-sm btn-default pull-right star mrg-right-10"><i data-poiId="'+p+'" id="star-' + d + '" class="star-icon ion-ios7-star-outline font-size-12"></i></button>' +
      '</div>' +
      '<div class="col-sm-12 no-pdd-left no-pdd-right">' +
      '<div class="card child-card">' +
      '<div class="card-block card-block-poi text-center">' +
      '<div class="col-sm-3 no-pdd-left no-pdd-right" id="council-logo"></div>' +
      '<div class="col-sm-9 no-pdd-left no-pdd-right"><h5 class="card-title" id="council-info"></h5></div>' +
      '<p  id="da-description"></p>' +
      '<div id="card-info-container-' + d + '" class="card-container-poi"><i class="fa fa-spinner fa-spin text-left"></i></div>' +
      '</div>' +
      '</div>' +
      '</div>' +
//      '<div class="col-sm-12 no-pdd-left no-pdd-right">' +
//      '<div class="card child-card">' +
//      '<div class="card-block card-block-poi">' +
//      '<h4 class="card-title">Address</h4>' +
//      '<div id="card-addresses-container-' + d + '" class="card-container-poi"><i class="fa fa-spinner fa-spin"></i></div>' +
//      '</div>' +
//      '</div>' +
//      '</div>' +
      '<div class="col-sm-12 no-pdd-left no-pdd-right">' +
      '<div class="card child-card">' +
      '<div class="card-block card-block-poi">' +
      '<h4 class="card-title">Documents</h4>' +
      '<div id="card-document-container-' + d + '" class="card-container-poi"><i class="fa fa-spinner fa-spin"></i></div>' +
      '</div>' +
      '</div>' +
      '</div>' +
      '<div class="col-sm-12 no-pdd-left no-pdd-right">' +
      '<div class="card child-card">' +
      '<div class="card-block card-block-poi">' +
      '<h4 class="card-title">Parties</h4>' +
      '<div id="card-party-container-' + d + '" class="card-container-poi"><i class="fa fa-spinner fa-spin"></i></div>' +
      '</div>' +
      '</div>' +
      '</div>' +
      '</div>';
    // `d` is the original data object for the row
    return $template;
  }


  function removeParams(sParam)
  {
    var action = 'reload';
    var url = window.location.href.split('?')[0]+'?';
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
      sURLVariables = sPageURL.split('&'),
      sParameterName,
      i;

    for (i = 0; i < sURLVariables.length; i++) {
      sParameterName = sURLVariables[i].split('=');
      if (sParameterName[0] != sParam) {
        if(typeof sParameterName[1] != 'undefined'){
          url = url + sParameterName[0] + '=' + sParameterName[1] + '&'
          action = 'location';
        }else{
          url = url;
        }
      }
    }

    return [url.substring(0,url.length-1), action];
  }
</script>