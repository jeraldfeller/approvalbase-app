<script src="{{ url("dashboard_assets/js/vendor/moment/min/moment.min.js") }}"></script>
{#<script src="{{ url("dashboard_assets/js/vendor/bootstrap-daterangepicker.js") }}"></script>#}
<script src="{{ url("dashboard_assets/js/vendor/bootstrap-daterangepicker/daterangepicker.js") }}"></script>
<script src="{{ url("dashboard_assets/js/vendor/raphael-min.js") }}"></script>
<script src="{{ url("dashboard_assets/js/vendor/morris.min.js") }}"></script>

<script src="{{ url("dashboard_assets/js/vendor/jquery.flot/jquery.flot.js") }}"></script>
<script src="{{ url("dashboard_assets/js/vendor/jquery.flot/jquery.flot.time.js") }}"></script>
<script src="{{ url("dashboard_assets/js/vendor/jquery.flot/jquery.flot.tooltip.js") }}"></script>

<script src='https://api.tiles.mapbox.com/mapbox-gl-js/v0.51.0/mapbox-gl.js'></script>
<script type="text/javascript">
  var startDate = "{{ defaultDateRange[0] }}";
  var endDate = "{{ defaultDateRange[1] }}";
  var solution = '{{ solution }}';
  var dateFilter = 'year';
  var table ='undefined';
  $(function () {

    var chart_border_color = "#efefef";
    var chart_color = "#b0b3e3";

    var options = {
      xaxis: {
        mode: "time",
        tickLength: 3
      },
      yaxis:
        {
          tickSize: 600
        },
      series: {
        lines: {
          show: true,
          lineWidth: 2,
          fill: false
        },
        shadowSize: 0
      },
      selection: {
        mode: "x"
      },
      grid: {
        hoverable: true,
        clickable: true,
        tickColor: chart_border_color,
        borderWidth: 0,
        borderColor: chart_border_color,
      },
      tooltip: true,
      tooltipOpts: {
        content: "Your sales for <b>%x</b>: <span class='value'>$%y</span>",
        dateFormat: "%y-%0m-%0d",
        defaultTheme: false,
        shifts: {
          x: -75,
          y: -70
        }
      },
      colors: [chart_color],

    };



    reload(options);


    $('.dateFilter').click(function(){
      if(!$(this).hasClass('active')){
        $dateFilter = $(this).attr('data-value');
        dateFilter = $dateFilter;
        if(dateFilter == 'year'){
          options.yaxis.tickSize = 600;
        }else{
          options.yaxis.tickSize = 60;
        }
        reload(options);
      }

    });



  });

  function getMetrics() {
    // Projects
    $('.data-projects').html('<i class="icomoon icomoon-spinner2 icomoon-spin"></i>');
    $('.data-projects-percent').removeClass('up').removeClass('down');
    $('.data-projects-percent-value').html('<i class="icomoon icomoon-spinner2 icomoon-spin"></i>');
    $('.data-value').html('<i class="icomoon icomoon-spinner2 icomoon-spin"></i>');

    return new Promise((resolve, reject) => {
      // ajax request to fetch data
      $.ajax({
        url: '{{ url('dashboard/getMetrics') }}',
        type: 'POST',
        data: {
          dateFilter: dateFilter
        },
        dataType: 'json'
      }).done(function (response) {

        // ALERTS
        $('.data-alerts').html(formatNumber(response.alerts.total));
//        $('.data-alerts-percent').addClass(response.alerts.incDec.status);
        if(response.alerts.total > 0){
          $('.data-alerts-percent').addClass('up');
          $('.data-alerts-percent-caret').addClass('fa-caret-up');
        }

//        if (response.alerts.incDec.status == 'up') {
//          $('.data-alerts-percent-caret').addClass('fa-caret-up');
//        } else {
//          $('.data-alerts-percent-caret').addClass('fa-caret-down');
//        }
//        $('.data-alerts-percent-value').html(response.alerts.incDec.percent + '%');
        $('.data-alerts-percent-value').html('');
        $('.data-alerts-saved').html(formatNumber(response.alerts.totalSaved));

        // Projects
        $('.data-projects').html(formatNumber(response.applications.totalCount));
//        $('.data-projects-percent').addClass(response.applications.incDec.status);
//        if (response.applications.incDec.status == 'up') {
//          $('.data-projects-percent-caret').addClass('fa-caret-up');
//        } else {
//          $('.data-projects-percent-caret').addClass('fa-caret-down');
//        }
        $('.data-projects-percent').addClass('up');
        $('.data-projects-percent-caret').addClass('fa-caret-up');
        $('.data-projects-percent-value').html('');
//        $('.data-projects-percent-value').html(response.applications.incDec.percent + '%');


        $('.data-value').html('$'+shortizeNumber(response.applications.totalCost));

        resolve(true);
      });
    });
  }

  function getDocumentsSearchData() {
    $('#documents-chart').html('<i class="icomoon icomoon-spinner2 icomoon-spin fa-2x"></i>');
    return new Promise((resolve, reject) => {

      //setTimeout(function () {
        $.ajax({
          url: '{{ url('dashboard/getData') }}',
          type: 'POST',
          data: {
            from: startDate,
            to: endDate,
            action: 'documents'
          },
          dataType: 'json'
        }).done(function (response) {
          resolve(response);
        });
     // }, 1000)

    });
  }


  function getAlertsData() {
    $('#alerts-chart').html('<i class="icomoon icomoon-spinner2 icomoon-spin fa-2x"></i>');
    return new Promise((resolve, reject) => {
     // setTimeout(function () {
        $.ajax({
          url: '{{ url('dashboard/getData') }}',
          type: 'POST',
          data: {
            dateFilter: dateFilter,
            action: 'alerts'
          },
          dataType: 'json'
        }).done(function (response) {
          resolve(response);
        });
     // }, 2300)

    });
  }

  function getProjectsData() {
    $('#alerts-chart').html('<i class="icomoon icomoon-spinner2 icomoon-spin fa-2x"></i>');
    return new Promise((resolve, reject) => {
      //setTimeout(function () {
        $.ajax({
          url: '{{ url('dashboard/getData') }}',
          type: 'POST',
          data: {
            dateFilter: dateFilter,
            action: 'projects'
          },
          dataType: 'json'
        }).done(function (response) {
          resolve(response);
        });
    // }, 2300)

    });
  }


  function loadMap() {
    if (solution == 'monitor') {
      mapboxgl.accessToken = '{{ mapboxKey }}';
      var map = new mapboxgl.Map({
        // container id specified in the HTML
        container: 'map',
        // style URL
        style: '{{ template }}',
        // initial position in [lon, lat] format
        center: [{{ center[0] }}, {{ center[1] }}],
        // initial zoom
        zoom: 4
      });
      return new Promise((resolve, reject) => {
        map.on('load', function (e) {
          resolve(true);
        });
      })
    } else {
      return new Promise((resolve, reject) => {
        resolve(true);
      })
    }


  }

  function getApplicationsSavedData() {
    $('#applications-saved-chart').html('<i class="icomoon icomoon-spinner2 icomoon-spin fa-2x"></i>');
    return new Promise((resolve, reject) => {
     //setTimeout(function () {
        $.ajax({
          url: '{{ url('dashboard/getData') }}',
          type: 'POST',
          data: {
            from: startDate,
            to: endDate,
            action: 'saved'
          },
          dataType: 'json'
        }).done(function (response) {
          resolve(response);
        });
    //}, 3100)

    });
  }


  function getSources() {
    $('#councils-container').html('<i class="icomoon icomoon-spinner2 icomoon-spin fa-2x"></i>');
    return new Promise((resolve, reject) => {
      //setTimeout(function () {
        $.ajax({
          url: '{{ url('dashboard/getSources') }}',
          type: 'POST',
          data: {
            from: startDate,
            to: endDate
          },
          dataType: 'json'
        }).done(function (response) {
          resolve(response);
        });
   //}, 4000)

    });
  }

  function getTableData(){
//    $('#table-tbody').html('<tr><td colspan="5" style="text-align: center;"><i class="icomoon icomoon-spinner2 icomoon-spin fa-2x"></i></td> </tr>');
    return new Promise((resolve, reject) => {
      //setTimeout(function(){
        $.ajax({
          url: '{{ url('dashboard/getTableData') }}',
          type: 'POST',
          data: {
            dateFilter: dateFilter
          },
          dataType: 'json'
        }).done(function (response) {
          resolve(response);
        });
   //}, 6000);

    });
  }


  function reload(options) {
    getMetrics().then(
      getProjectsData().then(
        d => {

          var plotAlerts = $.plot($("#alerts-chart"), [d], $.extend(options, {
            tooltipOpts: {
              content: "Projects on <b>%x : <span class=''>%y</span></b>",
              defaultTheme: false,
              shifts: {
                x: -75,
                y: -70
              }
            }
          }));

//          window.onresize = function (event) {
//            console.log('resize');
//            var plotAlerts = $.plot($("#alerts-chart"), [d], $.extend(options, {
//              tooltipOpts: {
//                content: "Alerts on <b>%x : <span class=''>%y</span></b>",
//                defaultTheme: false,
//                shifts: {
//                  x: -75,
//                  y: -70
//                }
//              }
//            }));
//          }
        }
      ).then(
        getTableData().then(
          d => {
              $totalProject = 0;
              $tableHtml = '';
              $.each(d, function(key, prop){
                  $tableHtml += '<tr>' +
                      '<td>'+key+'</td>' +
                      '<td>'+formatNumber(prop.projects)+'</td>' +
                      '<td>'+formatNumber(prop.documents)+'</td>' +
                      '<td>$'+formatNumber(Math.floor(prop.totalCost / prop.projects))+'</td>' +
                      '<td>$'+formatNumber(prop.totalCost)+'</td>' +
                      '</tr>';

                  $totalProject += prop.projects;
              });

              console.log('Total projects: ', $totalProject);
            $('#table-tbody').html($tableHtml);
            console.log(typeof(table));
            if(typeof(table) ==  'string'){
                console.log('sssss');
              table = $('#datatable-example').dataTable({
                  "order": [[1, "desc"]],
                  fixedHeader: true,
                  scrollY:        '500px',
                  scrollCollapse: true,
                  paging:         false
              });
            }


          }
        )
      )
    );
  }
</script>