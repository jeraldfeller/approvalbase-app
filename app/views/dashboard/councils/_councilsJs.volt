<script type="text/javascript">
    $(function () {

        var table = $('#dt-opt').DataTable({
            "serverSide": true,
            "ajax": {
                "url": "{{ url("datatables/councils") }}",
                "data": function (d) {
                    d.tableFilter = $('#table-filter').val();
                }
            },
            "stripeClasses": [],
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, 250, 500], [10, 25, 50, 100, 250, 500]],
            "stateSave": true,
            "pagingType": "full_numbers",
            "columnDefs": [
                {"targets": [0], "orderable": false, "width": "5%", "className": "text-center"},
                {"targets": [1], "width": "15%"},
                {"targets": [2], "width": "30%"},
                {"targets": [3], "width": "15%", "className": "text-center vertical-midle"},
                { className: "text-center vertical-middle", "targets": [ 1,2 ] },
            ],
            "language": {
                "emptyTable": "There are no councils available"
            }
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

        // Clicking on first <td>, checks the checkbox
        $("#dt-opt").on("click", "td:nth-child(1)", function () {

            var checkbox = $(this).find('>.dt-checkbox');
            checkbox.trigger("click");
        });

        // Subscribe button
        $("#bulk-subscribe").click(function () {
            bulkSubscribe(true);
        });

        // Unsubscribe button
        $("#bulk-unsubscribe").click(function () {
            bulkSubscribe(false);
        });

        function toggleBulkActions(show) {

            var bulkActionButtons = $(".bulk-action-button");
            if (show) {
                bulkActionButtons.removeClass("disabled");
            } else {
                bulkActionButtons.addClass("disabled");
            }
        }

        function bulkSubscribe(subscribe) {

            var councilIds = $(".dt-checkbox:checked").map(function () {

                var tableRow = $(this).parents(":eq(2)");
                console.log(tableRow);
                return +tableRow.attr("id").split("_")[1];

            }).get();
            $.ajax({
                url: '{{ url('councils/') }}' + (subscribe ? 'bulkSubscribe' : 'bulkUnsubscribe'),
                type: 'POST',
                data: {
                    "council_ids": councilIds
                },
                dataType: 'json',
            }).done(function (responseData) {

                if (responseData.status == "OK") {

                    councilIds.forEach(function (council_id) {

                        var row = $("#council_" + council_id);

                        var checkbox = row.find(".dt-checkbox");
                        checkbox.prop("checked", false);
                        checkbox.trigger("change");

                        var subscribeAnchor = $("#subscribe_council_" + council_id);
                        subscribeAnchor.removeClass();

                        if (subscribe) {
                            subscribeAnchor.addClass("text-success");
                            subscribeAnchor.text("Subscribed");
                            subscribeAnchor.attr("href", "{{ url.get("councils/") }}" + council_id + "/unsubscribe");
                        } else {
                            subscribeAnchor.addClass("text-danger");
                            subscribeAnchor.text("Not subscribed");
                            subscribeAnchor.attr("href", "{{ url.get("councils/") }}" + council_id + "/subscribe");
                        }
                    })
                }
            });
        }

    });
</script>
