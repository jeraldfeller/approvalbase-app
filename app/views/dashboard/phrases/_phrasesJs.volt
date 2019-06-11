<script src="{{ url("dashboard_assets/js/vendor/shepherd.min.js") }}"></script>
<script src="{{ url("dashboard_assets/js/vendor/select2.min.js?v=1.1") }}"></script>
<script type="text/javascript">

    var samplePhases = ['pool'];
    var filter = '';
    var minCost = 0;
    var maxCost = {{ maxCost }};
    var councils = $('#filter_councils').val();
    var caseSensitive = false;
    var searchAddress = false;
    var literalSearch = false;
    var excludePhrase = false;
    var metadata = false;
  $(function () {
    var isMobile = false; //initiate as false
    // device detection
    if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
      || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0, 4))) {
      isMobile = true;
    }


    $onBoardingFinish = {{ onboardingStatus }};
    $onboardingAlerts = '{{ user['onboardingAlerts'] }}';
    $hasSeenModal = '{{ user['seenModal'] }}';
    $onboardingFilter = '{{ user['onboardingFilter'] }}';
    if($onBoardingFinish == 1){
      $.ajax({
        url: '{{ url('account-profile/updateSeen') }}',
        type: 'POST',
        data: {
          v: 'filter'
        },
        dataType: 'json'
      });
      $hasToured = localStorage.getItem('tourFinished');

    //  if ($hasSeenModal == 1 && $onboardingFilter != 1) {
        console.log($hasSeenModal, $onboardingFilter);
        if ($hasToured != 'true') {
          if ($hasToured == null) {
            $('#createFilterModal').modal('show');
            $('body').attr('id', 'ui');
            var tour;

            tour = new Shepherd.Tour({
              defaults: {
                classes: 'shepherd-element shepherd-open shepherd-theme-arrows',
                showCancelLink: true
              }
            });




              tour.addStep('step1', {
                title: 'Search phrase',
                text: 'To create an automated email alert, enter a search phrase, select filters and click Create Phrase',
                attachTo: {
                  element: '#input_phrase',
                  on: 'bottom'
                },
                buttons: [
                  {
                    text: 'Exit',
                    classes: 'btn btn-default',
                    action: tour.cancel
                  },
                  {
                    text: 'Next',
                    classes: 'btn btn-primary',
                    action: tour.next
                  }]
              });





            tour.addStep('step2', {
              text: 'Your phrase will be added to the Filters page and we will alert you if we detect this phrase in any projects',
              attachTo: {
                element: '.step-table',
                on: 'top'
              },
              buttons: [
                {
                  text: 'Back',
                  classes: 'btn btn-default',
                  action: tour.back
                },
                {
                  text: 'Next',
                  classes: 'btn btn-primary',
                  action: tour.next
                }]

            });




            Shepherd.on('complete', function () {
              localStorage.setItem('tourPhrase', 'true');
              $('body').removeAttr('id');
              location.href = '{{ url('leads') }}?t=1';
            })


            setTimeout(function(){
              tour.start();

              Shepherd.on('show', function(){
                if(tour.getCurrentStep().id == 'step1'){
                    $('#createFilterModal').modal('hide')

                }
              });
            }, 500);
          }

        }

     // }
    }




    var table = $("#dt-opt").DataTable({
      "serverSide": true,
      "ajax": {
        "url": "{{ url("datatables/phrases") }}",
        "data": function (d) {
          d.customSearch = customSearchData();
        }
      },
      "stripeClasses": [],
      "pageLength": 25,
      "pagingType": "full_numbers",
      "lengthMenu": [[10, 25, 50, 100, 250, 500], [10, 25, 50, 100, 250, 500]],
      "stateSave": true,
      "filter": false,
      "columnDefs": [
        {"targets": [0, 1, 2, 3, 4], "className": "text-center vertical-middle"},
        {"targets": [1, 4], "orderable": false},
        {"targets": [0, 1], "width": "30%"},
        {"targets": [2, 3], "width": "10%"},
        {"targets": [4], "width": "5%"}

      ],
      "language": {
        "emptyTable": "There are no phrases available"
      }

    });

    table.on('draw', function () {

      // edit
      $('.editPhrase').click(function () {
        $phraseId = $(this).attr('data-id');
        $('.modal-loader').removeClass('display-none');
        $('.form-container').addClass('display-none');
        $('.select2-checkbox').prop('checked', false);
        $('#form-modal').modal('show');
        $.ajax({
          url: '{{ url('phrases/get') }}',
          type: 'POST',
          data: {id: $phraseId},
          dataType: 'json'
        }).done(function (response) {
          console.log(response)
          $id = response.id;
          $phrase = response.phrase;
          $cs = response.caseSensitive;
          $sa = response.searchAddresses;
          $ls = (response.literalSearch == true ? 0 : 1);
          $ep = response.excludePhrase;
          $m = response.metadata;
          $filterBy = (response.filterBy == 'all' ? 'all' : JSON.parse(response.filterBy));
          $councils = (response.councils == 'all' ? 'all' : JSON.parse(response.councils));
          $costFrom = response.costFrom;
          $costTo = response.costTo;

          $('.editSave').attr('data-id', $id);
          $('.deleteSave').attr('data-id', $id);
          if ($councils != 'all') {
              $("#councils_edit").val($councils).trigger('change');
          }else{
              if(typeof typeof $("#councils_edit").attr('data-select2-id') != 'undefined'){
                  $("#councils_edit").val(" ");
              }else{
                  $("#councils_edit").select2("val", " ");
              }

          }

          if ($filterBy != 'all') {
             $("#filter1_edit").val($filterBy).trigger('change');
          }else{
              if(typeof typeof $("#councils_edit").attr('data-select2-id') != 'undefined') {
                  $("#filter1_edit").val(" ");
              }else{
                  $("#filter1_edit").select2("val", " ");
              }
          }


          $('#input_phrase_edit').val($phrase);
          if ($costTo > 50000000) {
            $('#cost-to_edit').val(50000000).trigger('change');
          } else {
            $('#cost-to_edit').val($costTo).trigger('change');
          }
          $('#cost-from_edit').val($costFrom).trigger('change');


          $('#input_metadata_edit').prop('checked', $m);
          $('#input_search_addresses_edit').prop('checked', $sa);
          $('#input_literal_search_edit').prop('checked', $ls);
          $('#input_exclude_phrase_edit').prop('checked', $ep);


          $('.select2-search').css('width', '100%');
          $('.select2-search__field').css('width', '100%');
          $('.modal-loader').addClass('display-none');
          $('.form-container').removeClass('display-none');

        });
      });


      setTimeout(function(){
        $('#dt-opt').removeAttr('style');
      }, 3000);
    });

    // If toggle all button changes
    $("#dt-opt").on("change", "#checkbox-toggle-all", function (e) {

      var toggleAllButton = $(e.target);
      var checkboxes = $(".dt-checkbox");

      var toggleAllButtonChecked = toggleAllButton.is(":checked");
      if (typeof toggleAllButtonChecked !== typeof undefined && toggleAllButtonChecked !== false) {

        checkboxes.prop("checked", true);
        checkboxes.parents(":eq(2)").removeClass().addClass("bg-active");
      } else {

        checkboxes.prop("checked", false);
        checkboxes.parents(":eq(2)").removeClass();
        if (checkboxes.parents(":eq(2)").data("seen") === false) {
          checkboxes.addClass("bg-unread");
        }
      }

      checkboxes.trigger("change");
      toggleBulkActions(true);
    });

    // Clicking on first <td>, checks the checkbox
    $("#dt-opt").on("click", "td:nth-child(1)", function () {

      var checkbox = $(this).find('>.dt-checkbox');
      checkbox.trigger("click");
    });

    // Checkbox changes
    $("#dt-opt").on("change", ".dt-checkbox", function (e) {

      var tableRow = $(e.target).parents(":eq(2)");
      var checkbox = $(e.target);

      var checked = checkbox.is(":checked");
      if (typeof checked !== typeof undefined && checked !== false) {

        tableRow.removeClass().addClass("bg-active");
        checkbox.prop("checked", true);
      } else {

        tableRow.removeClass();
        if (tableRow.data("seen") === false) {
          tableRow.addClass("bg-unread");
        }

        checkbox.prop("checked", false);
      }

      // Check if any checkboxes are checked
      var toggleAllCheckbox = $("#checkbox-toggle-all");
      var checkboxes = $(".dt-checkbox");
      var checkedCheckboxes = $(".dt-checkbox:checked");

      if (checkboxes.length === checkedCheckboxes.length) {
        toggleAllCheckbox.prop("checked", true);
      } else if (checkedCheckboxes.length < checkboxes.length || checkedCheckboxes.length === 0) {
        toggleAllCheckbox.prop("checked", false);
      }

      var event = jQuery.Event("change");
      event.target = $(this).find("#checkbox-toggle-all");
      $(this).trigger(event);

      toggleBulkActions(!!+checkedCheckboxes.length);
    });

    // Settings checkboxes
    $("#dt-opt").on("change", ".dt-case-checkbox, .dt-literal-checkbox, .dt-exclude-checkbox", function (e) {

      var row = $(e.target).parents(":eq(2)");
      var checkbox = $(e.target);
      var phraseId = +row.attr("id").split("_")[1];

      var url;
      if (checkbox.hasClass("dt-case-checkbox") === true) {
        url = "{{ url('phrases/flipCase') }}";
      } else if (checkbox.hasClass("dt-literal-checkbox") === true) {
        url = "{{ url('phrases/flipLiteral') }}";
      } else if (checkbox.hasClass("dt-exclude-checkbox") === true) {
        url = "{{ url('phrases/flipExclude') }}"
      }

      $.ajax({
        url: url,
        type: 'POST',
        data: {"phrase_id": phraseId},
        dataType: 'json'
      });

    });

    $("#bulk-delete").click(function () {
      $btn = $(this);
      $btn.html('<i class="fa fa-spinner fa-spin"></i>');
      $btn.prop('disalbed', true);
      var rows = $(".dt-checkbox:checked");
      var ids = rows.map(function () {

        var row = $(this).parents(":eq(2)");
        return +row.attr("id").split("_")[1];

      }).get();

      $.ajax({

        url: '{{ url('phrases/delete') }}',
        type: 'POST',
        data: {ids: ids},
        dataType: 'json',

      }).done(function () {
        $btn.html('DELETE');
        $btn.prop('disalbed', false);
        showNotification('Filter successfully deleted', 'success');
        table.draw();
      });

    });

    function toggleBulkActions(show) {

      var bulkActionButtons = $(".bulk-action-button");
      if (show) {
        bulkActionButtons.removeClass("disabled");
      } else {
        bulkActionButtons.addClass("disabled");
      }
    }


    if (!isMobile) {
      // councils

      let branch_all = [];

      function formatResult(state) {
        if (!state.id) {
          var btn = $('<div class="text-right"><button id="all-branch" style="margin-right: 10px;" class="btn btn-default">Select All</button><button id="clear-branch" class="btn btn-default">Clear All</button></div>')
          return btn;
        }

        branch_all.push(state.id);
        var id = 'state' + state.id;
        var checkbox = $('<div class="checkbox-select"><input class="select2-checkbox" id="' + id + '" type="checkbox" ' + (state.selected ? 'checked' : '') + '><label for="checkbox1">' + state.text + '</label></div>', {id: id});
        return checkbox;
      }


      function arr_diff(a1, a2) {
        var a = [], diff = [];
        for (var i = 0; i < a1.length; i++) {
          a[a1[i]] = true;
        }
        for (var i = 0; i < a2.length; i++) {
          if (a[a2[i]]) {
            delete a[a2[i]];
          } else {
            a[a2[i]] = true;
          }
        }
        for (var k in a) {
          diff.push(k);
        }
        return diff;
      }


      $('#filter1').select2({
        placeholder: "Search All",
        closeOnSelect: false,
        width: '100%',
        templateResult: formatResult,
      })

      $("#councils").select2({
        dropdownParent: $('#createFilterModal'),
        placeholder: "All",
        allowClear: true,
        closeOnSelect: false,
        width: '100%',
        templateResult: formatResult,
      });

      $("#filter_councils").select2({
        dropdownParent: $('#filterModal'),
        placeholder: "All",
        allowClear: true,
        closeOnSelect: false,
        width: '100%',
        templateResult: formatResult,
      });

      $('#filter1_edit').select2({
        placeholder: "Search All",
        closeOnSelect: false,
        width: '100%',
        dropdownParent: $('#form-modal'),
        templateResult: formatResult,
      })

      $("#councils_edit").select2({
        placeholder: "All",
        allowClear: true,
        closeOnSelect: false,
        width: '100%',
        dropdownParent: $('#form-modal'),
        templateResult: formatResult,
      });

      $('.select2-search').css('width', '100%');
      $('.select2-search__field').css('width', '100%');



      $(".select2").on("select2:selecting", function (event) {
        var $pr = $('#' + event.params.args.data._resultId).parent();
        scrollTop = $pr.prop('scrollTop');
      });

      $(".select2").on("select2:select", function (event) {
        console.log(event);
        $(window).scroll();

        var $pr = $('#' + event.params.data._resultId).parent();
        $pr.prop('scrollTop', scrollTop);

        $(this).val().map(function (index) {
          $("#state" + index).prop('checked', true);
        });
      });

      $(".select2").on("select2:unselecting", function (event) {
        var $pr = $('#' + event.params.args.data._resultId).parent();
        scrollTop = $pr.prop('scrollTop');
      });

      $(".select2").on("select2:unselect", function (event) {
        $(window).scroll();

        var $pr = $('#' + event.params.data._resultId).parent();
        $pr.prop('scrollTop', scrollTop);

        var branch = $(this).val() ? $(this).val() : [];
        var branch_diff = arr_diff(branch_all, branch);
        branch_diff.map(function (index) {
          $("#state" + index).prop('checked', false);
        });
      });
    } else {
      $("#councils").addClass('form-control');
      $("#filter1").addClass('form-control');
      $("#councils_edit").addClass('form-control');
      $("#filter1_edit").addClass('form-control');
    }

    $("#councils").removeClass('display-none');
    $("#filter1").removeClass('display-none');


    $('#createBtn').click(function () {
      $btn = $(this);
      $btn.html('<i class="fa fa-spinner fa-spin"></i>');
      $btn.prop('disabled', true);
      $max = parseInt($('#cost-to').val());
      $min = parseInt($('#cost-from').val());


      $phrase = $('#input_phrase').val();
      $councils = ($("#councils").val() != null ? $("#councils").val() : 'all');
      $filter = ($("#filter1").val() != null ? $("#filter1").val() : 'all');


      $costFrom = ($min > $max ? $max : $min);
      $costTo = ($max < $min ? $min : $max);
      $costTo = ($costTo == 100000000 ? {{ maxCostValue }} : $costTo)
      $data = {
        'inputPhrase': $phrase,
        'filter': $filter,
        'councils': $councils,
        'costFrom': $costFrom,
        'costTo': $costTo,
        'inputLiteralSearch': ($('#input_literal_search').is(':checked') == true ? 0 : 1),
        'inputExcludePhrase': ($('#input_exclude_phrase').is(':checked') == true ? 1 : 0),
        'inputMetadata': ($('#input_metadata').is(':checked') == true ? 1 : 0),
          'inputSearchAddresses': ($('#input_search_addresses').is(':checked') == true ? 1 : 0),

      }
      $.ajax({
        url: '{{ url('phrases/create') }}',
        type: 'POST',
        data: $data,
        dataType: 'json'
      }).done(function (response) {
        if(response == true){
          showNotification('Filter successfully created', 'success');
        }else{
          showNotification('Ops. Something went wrong please try again.', 'error');
        }

         $btn.prop('disabled', false);
        $btn.html('Save');
        table.ajax.reload();
        $('#createFilterModal').modal('hide');

      });
    });

    $('.editSave').click(function () {
      $btn = $(this);
      $id = $btn.attr('data-id');
      $btn.html('<i class="fa fa-spinner fa-spin"></i>');
      $btn.prop('disabled', true);
      $max = parseInt($('#cost-to_edit').val());
      $min = parseInt($('#cost-from_edit').val());


      $phrase = $('#input_phrase_edit').val();
      $councils = ($("#councils_edit").val() != null ? $("#councils_edit").val() : 'all');
      $filter = ($("#filter1_edit").val() != null ? $("#filter1_edit").val() : 'all');


      $costFrom = ($min > $max ? $max : $min);
      $costTo = ($max < $min ? $min : $max);
      $costTo = ($costTo == 100000000 ? {{ maxCostValue }} : $costTo)
      $data = {
        'inputPhrase': $phrase,
        'filter': $filter,
        'councils': $councils,
        'costFrom': $costFrom,
        'costTo': $costTo,

        'inputLiteralSearch': ($('#input_literal_search_edit').is(':checked') == true ? 0 : 1),
        'inputExcludePhrase': ($('#input_exclude_phrase_edit').is(':checked') == true ? 1 : 0),
        'inputMetadata': ($('#input_metadata_edit').is(':checked') == true ? 1 : 0),
          'inputSearchAddresses': ($('#input_search_addresses_edit').is(':checked') == true ? 1 : 0),


      }
      $.ajax({

        url: '{{ url('phrases/delete') }}',
        type: 'POST',
        data: {ids: [$id]},
        dataType: 'json',

      }).done(function () {
        $.ajax({
          url: '{{ url('phrases/create') }}',
          type: 'POST',
          data: $data,
          dataType: 'json'
        }).done(function (response) {
          if(response == true){
            showNotification('Filter successfully updated', 'success');
          }else{
            showNotification('Ops. Something went wrong please try again.', 'error');
          }
          $('#form-modal').modal('hide');
          $btn.html('Save');
          $btn.prop('disabled', false);

          table.ajax.reload();
        });
      });
    });

    $('.deleteSave').click(function(){
      $btn = $(this);
      $btn.html('<i class="fa fa-spinner fa-spin"></i>');
      $btn.prop('disabled', true);
      $id = $(this).attr('data-id');
      $.ajax({
        url: '{{ url('phrases/delete') }}',
        type: 'POST',
        data: {ids: [$id]},
        dataType: 'json',
      }).done(function () {
        $btn.html('DELETE');
        $btn.prop('disabled', false);
        showNotification('Filter successfully deleted', 'success');
        table.draw();
        $('#form-modal').modal('hide');
      });
    });


    $('#searchFilter').keyup(function () {
      filter = $(this).val();
      $('#filter_input_phrase').val(filter);
      table.ajax.reload();
    });


    $('.refineSearchBtn').click(function(){
      filter = $('#filter_input_phrase').val();
      $max = parseInt($('#filter_cost-to').val());
      $min = parseInt($('#filter_cost-from').val());
      minCost = ($min > $max ? $max : $min);
      maxCost = ($max < $min ? $min : $max);
      councils = $('#filter_councils').val();
      caseSensitive = $('#filter_input_case_sensitive').is(':checked');
      literalSearch = $('#filter_input_literal_search').is(':checked');
      excludePhrase = $('#filter_input_exclude_phrase').is(':checked');
      metadata = $('#filter_input_metadata').is(':checked');

    });

    $('.clearBtn').click(function(){
      $('#input_phrase').val('');
      $('#cost-to').val(50000000).trigger('change');
      $('#cost-from').val(0).trigger('change');
      $('#councils').val('');
      $('#input_case_sensitive').prop('checked', false);
      $('#input_literal_search').prop('checked', false);
      $('#input_exclude_phrase').prop('checked', false);
      $('#input_metadata').prop('checked', false);
    });

  });

    function customSearchData() {
      return {
        "filter": filter
      }
    }
</script>
