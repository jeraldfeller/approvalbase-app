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
  $(function () {
    // Flot Charts
    var chart_border_color = "#efefef";
    var chart_color = "#b0b3e3";
    var d2 = [[utils.get_timestamp(14), 1500], [utils.get_timestamp(13), 1600], [utils.get_timestamp(12), 1630], [utils.get_timestamp(11), 1310], [utils.get_timestamp(10), 1530], [utils.get_timestamp(9), 2050], [utils.get_timestamp(8), 2310], [utils.get_timestamp(7), 2050], [utils.get_timestamp(6), 2125], [utils.get_timestamp(5), 1400], [utils.get_timestamp(4), 1600], [utils.get_timestamp(3), 1930], [utils.get_timestamp(2), 2000], [utils.get_timestamp(1), 2320]];


    var options = {
      xaxis: {
        mode: "time",
        tickLength: 10
      },
      series: {
        lines: {
          show: true,
          lineWidth: 2,
          fill: true,
          fillColor: {
            colors: [{
              opacity: 0.04
            }, {
              opacity: 0.1
            }]
          }
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
      colors: [chart_color]
    };


    reload(options);

    // daterange input
    $('#date-range-picker').daterangepicker({
      ranges: {
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
        'Last 7 Days': [moment().subtract('days', 6), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
      },
      locale: {
        format: 'DD/MM/Y'
      },
      opens: "left",
      startDate: moment().subtract('months', 4),
      endDate: moment()
    }, function (start, end) {
      startDate = moment(start).format('Y-MM-DD HH:mm:ss');
      endDate = moment(end).format('Y-MM-DD HH:mm:ss');


      reload(options);
    });
  });

  function getMetrics() {
    return new Promise((resolve, reject) => {
      // ajax request to fetch data
      $.ajax({
        url: '{{ url('dashboard/getMetrics') }}',
        type: 'POST',
        data: {
          from: startDate,
          to: endDate
        },
        dataType: 'json'
      }).done(function (response) {

        // Users application count
        $('.data-applications').html(formatNumber(response.applications.totalCount));
        // Users council total value
        $('.data-value').html(shortizeNumber(response.applications.totalCost));


        if (response.documents.incDec.status == 'up') {
          $('.data-documents-inc-dec').removeClass('fa-chevron-down').addClass('fa-chevron-up');
          $('.data-documents-inc-dec').parent().removeClass('down').addClass('up');
        } else if (response.documents.incDec.status == 'down') {
          $('.data-documents-inc-dec').removeClass('fa-chevron-up').addClass('fa-chevron-down');
          $('.data-documents-inc-dec').parent().removeClass('up').addClass('down');
        } else {
          $('.data-documents-inc-dec').removeClass('fa-chevron-up');
          $('.data-documents-inc-dec').parent().removeClass('up');
        }
        $('.data-documents-increase').html(response.documents.incDec.percent + '%');


        if (response.alerts.incDec.status == 'up') {
          $('.data-alerts-inc-dec').removeClass('fa-chevron-down').addClass('fa-chevron-up');
          $('.data-alerts-inc-dec').parent().removeClass('down').addClass('up');
        } else if (response.alerts.incDec.status == 'down') {
          $('.data-alerts-inc-dec').removeClass('fa-chevron-up').addClass('fa-chevron-down');
          $('.data-alerts-inc-dec').parent().removeClass('up').addClass('down');
        } else {
          $('.data-alerts-inc-dec').removeClass('fa-chevron-up');
          $('.data-alerts-inc-dec').parent().removeClass('up');
        }
        $('.data-alerts-increase').html(response.alerts.incDec.percent + '%');


        // Users alerts count
        $('.data-alerts').html(formatNumber(response.alerts.total));
        // Users councils count
        $('.data-councils').html(formatNumber(response.councils));

        // total number of documents
        $('.data-documents').html(response.documents.total);


        // Assets Metrics
        $('.data-assets').html(formatNumber(response.assets));

        resolve(true);
      });
    });
  }

  function getDocumentsSearchData() {
    $('#documents-chart').html('<i class="icomoon icomoon-spinner2 icomoon-spin fa-2x"></i>');
    return new Promise((resolve, reject) => {

      setTimeout(function () {
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
      }, 1000)

    });
  }


  function getAlertsData() {
    $('#alerts-chart').html('<i class="icomoon icomoon-spinner2 icomoon-spin fa-2x"></i>');
    return new Promise((resolve, reject) => {
      setTimeout(function () {
        $.ajax({
          url: '{{ url('dashboard/getData') }}',
          type: 'POST',
          data: {
            from: startDate,
            to: endDate,
            action: 'alerts'
          },
          dataType: 'json'
        }).done(function (response) {
          resolve(response);
        });
      }, 2300)

    });
  }


  function loadMap() {
    if(solution == 'monitor'){
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
    }else{
      return new Promise((resolve, reject) => {
        resolve(true);
      })
    }


  }

  function getApplicationsSavedData() {
    $('#applications-saved-chart').html('<i class="icomoon icomoon-spinner2 icomoon-spin fa-2x"></i>');
    return new Promise((resolve, reject) => {
      setTimeout(function () {
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
      }, 3100)

    });
  }


  function getSources() {
    $('#councils-container').html('<i class="icomoon icomoon-spinner2 icomoon-spin fa-2x"></i>');
    return new Promise((resolve, reject) => {
      setTimeout(function () {
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
      }, 4000)

    });
  }


  function reload(options) {
    getMetrics().then(
      getDocumentsSearchData().then(
        d => {
          console.log(d);
          var plotDocuments = $.plot($("#documents-chart"), [d], $.extend(options, {
            tooltipOpts: {
              content: "Documents searched on <b>%x : <span class=''>%y</span></b>",
              defaultTheme: false,
              shifts: {
                x: -75,
                y: -70
              }
            }
          }));
        }
      ).then(
        getAlertsData().then(
          d => {

            var plotAlerts = $.plot($("#alerts-chart"), [d], $.extend(options, {
              tooltipOpts: {
                content: "Alerts on <b>%x : <span class=''>%y</span></b>",
                defaultTheme: false,
                shifts: {
                  x: -75,
                  y: -70
                }
              }
            }));
          }

        ).then(
          loadMap().then(
            getApplicationsSavedData().then(
              d => {

                if(solution == 'monitor'){
                  // Bar chart (visitors)
                  var chart_border_color = "#efefef";
                  var chart_color = "#b0b3e3";
                  var dBar = [[utils.get_timestamp(30), 930], [utils.get_timestamp(29), 1200], [utils.get_timestamp(28), 980], [utils.get_timestamp(27), 950], [utils.get_timestamp(26), 1000], [utils.get_timestamp(25), 1050], [utils.get_timestamp(24), 1150], [utils.get_timestamp(23), 2300], [utils.get_timestamp(22), 1200], [utils.get_timestamp(21), 1300], [utils.get_timestamp(20), 1700], [utils.get_timestamp(19), 1450], [utils.get_timestamp(18), 1500], [utils.get_timestamp(17), 546], [utils.get_timestamp(16), 614], [utils.get_timestamp(15), 954], [utils.get_timestamp(14), 1700], [utils.get_timestamp(13), 1800], [utils.get_timestamp(12), 1900], [utils.get_timestamp(11), 2000], [utils.get_timestamp(10), 2100], [utils.get_timestamp(9), 2200], [utils.get_timestamp(8), 2300], [utils.get_timestamp(7), 2400], [utils.get_timestamp(6), 2550], [utils.get_timestamp(5), 2600], [utils.get_timestamp(4), 1800], [utils.get_timestamp(3), 2200], [utils.get_timestamp(2), 2350], [utils.get_timestamp(1), 2800], [utils.get_timestamp(0), 3245]];
                  console.log(dBar);
                  console.log(d);
                  var options2 = {
                    yaxes: {
                      min: 0
                    },
                    xaxis: {
                      mode: "time",
                      timeformat: "%a %d",
                    },
                    series: {
                      bars: {
                        show: true,
                        lineWidth: 0,
                        barWidth: 43200000, // for bar charts, this is width in milliseconds (86400000 would be the width of a day)
                        align: 'center',
                        fillColor: {
                          colors: [{opacity: 0.7}, {opacity: 0.7}]
                        }
                      }
                    },
                    grid: {
                      show: true,
                      hoverable: true,
                      clickable: true,
                      tickColor: chart_border_color,
                      borderWidth: 0,
                      borderColor: chart_border_color,
                    },
                    tooltip: true,
                    tooltipOpts: {
                      content: "Saved on <b>%x : <span class=''>%y</span></b>",
                      defaultTheme: false,
                      shifts: {
                        x: -65,
                        y: -70
                      }
                    },
                    colors: ["#4fa3d5"]
                  };

                  var plot4 = $.plot($("#applications-saved-chart"), [d], options2);
                }
              }

            ).then(
              getSources().then(
                d => {
                  var total = d.total;
                  var htmlData = '';
                  $.each(d.data, function (key, val) {
                    var percent = (val / total) * 100;
                    htmlData += '<div class="referral">'
                      + '<span>'
                      + key
                      + '<div class="pull-right">'
                      + '<span class="data">' + val + '</span>  ' + percent.toFixed(2) + '%'
                      + '</div>'
                      + '</span>'
                      + '<div class="progress">'
                      + '<div class="progress-bar progress-bar-success animate" role="progressbar" aria-valuenow="' + percent.toFixed(2) + '" aria-valuemin="0" aria-valuemax="100" style="width: ' + percent.toFixed(2) + '%">'
                      + '</div>'
                      + '</div>'
                      + '</div>';

                  });

                  $('#councils-container').html(htmlData);
                }
              )
            )
          )
        )
      )
    );
  }
</script>