<script src="{{ url("dashboard_assets/js/vendor/select2.min.js") }}"></script>
<script type="text/javascript">
    $(function () {
      var councils = [];
        var table = $('#dt-opt').DataTable({
            "serverSide": true,
            "ajax": {
                "url": "{{ url("admin/datatables/leads?status=" ~ lead_status ~ "&currentViewedLead=" ~ currentViewedLead) }}",
                "data": function (d) {
                  d.customSearch = customSearchData();
                }
            },
            "stripeClasses": [],
            "pageLength": 25,
            "pagingType": "full_numbers",
            "lengthMenu": [[10, 25, 50, 100, 250, 500], [10, 25, 50, 100, 250, 500]],
            "stateSave": true,
            "columnDefs": [
                {"targets": [0], "width": "5%", "orderable": false},
                {"targets": [2], "width": "50%"},
                {"targets": [0, 1, 3], "className": "text-center vertical-middle"},
                {"targets": [2], "className": "vertical-middle"},
            ],
            "language": {
                "emptyTable": "There are no leads available"
            }

        });

      // councils
      $("#councils").select2({
        placeholder: "Select council",
        allowClear: true,
        width: null
      });
      $("#councils").removeClass('display-none');
//      $('.select2-input').css('width', '200%');
//      $("#content .menubar").css('height', 'auto');



      $('#councils').change(function(){
        councils = $(this).val();
        table.ajax.reload();
      })

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

        // Clicking on any <td> in .lead-row after 1st
        $("#dt-opt").on("click", "td:nth-child(n+2)", function (e) {

            var row = $(e.target).parent();
            var leadId = row.attr("id").split("_")[1];
            location.href = "{{ url('leads/') }}" + leadId + "/view?a=1";
        });

        // seenFilter button, fnDraw() will request data from server using filter information.
        $("#table-filter").change(function () {
            table.draw();
        });

        // Move to leads button
        $("#bulk-move-to-leads").click(function () {
            bulkUpdateLeadStatus({{ constant("Aiden\Models\Das::STATUS_LEAD") }});
        });
        function toggleBulkActions(show) {

            var bulkActionButtons = $(".bulk-action-button");
            if (show) {
                bulkActionButtons.removeClass("disabled");
            } else {
                bulkActionButtons.addClass("disabled");
            }
        }

        function bulkUpdateLeadStatus(status) {

            var leadIds = $("input[name='select-row']").map(function () {
                var row = $(this).parent().parent();
                if ($(this).is(":checked")) {
                    return +row.attr("id").split("_")[1];
                }
            }).get();
            $.ajax({

                url: '{{ url('admin/leads/bulkUpdateLeadStatus') }}',
                type: 'POST',
                data: {status: +status, lead_ids: leadIds},
                dataType: 'json',
            }).done(function (responseData) {

                if (responseData.status == "OK") {
                    location.reload();
                }

            });
        }

      function customSearchData(){
        return {
          "councils": councils
        }
      }

    });
</script>
