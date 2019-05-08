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

          $activeContainer = $('a.active-da').attr('data-type');

          var clickedPoint = features[0];
          // For da circle on click enlarge circle and select in da list
          map.setPaintProperty("locations-"+$activeContainer, 'circle-radius', ["case", ["==", ["get", "address"], clickedPoint.properties.address], 7, 3.5]);

          var activeItem = document.getElementsByClassName('active-selected');
          if (activeItem[0]) {
            activeItem[0].classList.remove('active-selected');
          }



//          $('.da-list-row').removeClass('shown');

          // show list

          setTimeout(function(){
            console.log('THIS');
            $('.poi_' + clickedPoint.properties.poiId + '').addClass('active-selected');
            if ($('.poi_' + clickedPoint.properties.poiId + '').find('.poi-action-show-list').hasClass('fa-angle-double-down')) {
              $('.poi_' + clickedPoint.properties.poiId + '').find('.poi-action-show-list').trigger('click');
            }
            console.log($('.da-'+clickedPoint.properties.dasId+'-'+clickedPoint.properties.address.replace(/ /g, '-').replace(/,/g, '-')+ ' .redirect:eq(0)'));
            $('.da-'+clickedPoint.properties.dasId+'-'+clickedPoint.properties.address.replace(/ /g, '-').replace(/,/g, '-')+ ' .redirect:eq(0)').trigger('click');
//            $('.da-'+clickedPoint.properties.dasId+'-'+clickedPoint.properties.address.replace(/ /g, '-').replace(/,/g, '-')).addClass('shown');
//


            $('.scroll-wrapper').animate({
              scrollTop: $('.da-'+clickedPoint.properties.dasId+'-'+clickedPoint.properties.address.replace(/ /g, '-').replace(/,/g, '-')).offset().top
            }, 500);
          }, 500);


          // 1. Fly to the point
//          flyToStore(clickedPoint);
          // 2. Close all other popups and display popup for clicked store
//          createPopUp(clickedPoint);
          // 3. Highlight listing in sidebar (and remove highlight for all other listings)
//          var activeItem = document.getElementsByClassName('active');
//          if (activeItem[0]) {
//            activeItem[0].classList.remove('active');
//          }
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
          $aFeatures = addressesFeaturesAlpha.features;
          $bFeatures = addressesFeaturesBeta.features;
          $containerAlpha = $('#listings-alpha');
          $containerBeta = $('#listings-beta');

          // start alpha
          $htmlData = '';
          console.log($aFeatures);
          $.each($aFeatures, function (index, key) {
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
              $rowIdentifier = key.properties.dasId+'-'+key.properties.address;
              $htmlData += '<tr data-index="'+index+'" class=" da-list-row hover da-'+$rowIdentifier.replace(/ /g, '-').replace(/,/g, '-')+'" data-dasId="' + key.properties.dasId + '" id="' + key.properties.poiId + '"><td class="da-li text-left ">' + key.properties.lodgeDateUnix + '</td><td class="da-li text-left  redirect">' + key.properties.lodgeDate + '</td><td class="da-li text-left  redirect">' +
                key.properties.address +
                '</td></tr>';
          });

//      $container.html('<ul class="da-list">'+($htmlData != '' ? $htmlData : 'No DA available')+'</ul>');
          if($htmlData != ''){
            $containerAlpha.html('<table style="width: 100%;" class="table table-hover-dark" id="listAlpha"><thead class="display-none"><tr><th>Timestamp</th><th>Lodge Date</th><th>Address</th></tr></thead><tbody class="tbody">' + $htmlData + '</tbody></table>');

            table = $('#listAlpha').unbind().DataTable({
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
                  tr.removeClass('shown');
                });
              }
              else {
                // Open this row (the format() function would return the data to be shown)
                if (table.row('.shown').length) {
                  console.log(table.row('.shown').length);
                  $('.redirect', table.row('.shown').node()).click();
                }

                row.child(format($dasId, $poiId)).show();
                row.child().find('td').addClass('no-pdd-left no-pdd-right');
                tr.addClass('shown');
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
                      $('#star-' + $dasId).removeClass('ion-ios7-star-outline').addClass('ion-ios7-star starred').attr('data-starred', true).attr('data-id', $dasId).attr('data-poiId', $poiId);
                    } else {
                      $('#star-' + $dasId).removeClass('ion-ios7-star starred').addClass('ion-ios7-star-outline').attr('data-starred', false).attr('data-id', $dasId).attr('data-poiId', $poiId);
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
            $containerAlpha.html('<table style="width: 100%;" class="table table-hover-dark" id="listAlpha  "><thead class="display-none"><tr><th>na</th></tr></thead><tbody class="tbody"><tr><td class="text-center">No DA available</td></tr></tbody></table>');
          }

        // bind on hover row to highlight address circle

        $('table#listAlpha tr').hover(function(){
          $index = $(this).attr('data-index');
          if(typeof $index != 'undefined'){
            map.setPaintProperty("locations-alpha", 'circle-radius', ["case", ["==", ["get", "address"], $aFeatures[$index].properties.address], 7, 3.5]);
          }
        });


        // end of alpha

        // start of beta
        $htmlData = '';
        $.each($bFeatures, function (index, key) {
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
          $rowIdentifier = key.properties.dasId+'-'+key.properties.address;
          $htmlData += '<tr data-index="'+index+'" class=" da-list-row hover da-'+$rowIdentifier.replace(/ /g, '-').replace(/,/g, '-')+'" data-dasId="' + key.properties.dasId + '" id="' + key.properties.poiId + '"><td class="da-li text-left ">' + key.properties.lodgeDateUnix + '</td><td class="da-li text-left  redirect">' + key.properties.lodgeDate + '</td><td class="da-li text-left  redirect">' +
            key.properties.address +
            '</td></tr>';
        });

//      $container.html('<ul class="da-list">'+($htmlData != '' ? $htmlData : 'No DA available')+'</ul>');
        if($htmlData != ''){
          $containerBeta.html('<table style="width: 100%;" class="table table-hover-dark" id="listBeta"><thead class="display-none"><tr><th>Timestamp</th><th>Lodge Date</th><th>Address</th></tr></thead><tbody class="tbody">' + $htmlData + '</tbody></table>');

          table = $('#listBeta').unbind().DataTable({
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
                tr.removeClass('shown');
              });
            }
            else {
              // Open this row (the format() function would return the data to be shown)
              if (table.row('.shown').length) {
                console.log(table.row('.shown').length);
                $('.redirect', table.row('.shown').node()).click();
              }

              row.child(format($dasId, $poiId)).show();
              row.child().find('td').addClass('no-pdd-left no-pdd-right');
              tr.addClass('shown');
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
                    $('#star-' + $dasId).removeClass('ion-ios7-star-outline').addClass('ion-ios7-star starred').attr('data-starred', true).attr('data-id', $dasId).attr('data-poiId', $poiId);
                  } else {
                    $('#star-' + $dasId).removeClass('ion-ios7-star starred').addClass('ion-ios7-star-outline').attr('data-starred', false).attr('data-id', $dasId).attr('data-poiId', $poiId);
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
          $containerBeta.html('<table style="width: 100%;" class="table table-hover-dark" id="listBeta"><thead class="display-none"><tr><th>na</th></tr></thead><tbody class="tbody"><tr><td class="text-center">No DA available</td></tr></tbody></table>');
        }



        // bind on hover row to highlight address circle

        $('table#listBeta tr').hover(function(){
          $index = $(this).attr('data-index');
          if(typeof $index != 'undefined'){
            map.setPaintProperty("locations-beta", 'circle-radius', ["case", ["==", ["get", "address"], $bFeatures[$index].properties.address], 7, 3.5]);
          }
        });

        $('.timeago').timeago();
        // end of beta
      }





    }
  );



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
      '<p  id="da-description">&nbsp;</p>' +
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
              "status": response[i].status,
              "documents": response[i].documents,
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