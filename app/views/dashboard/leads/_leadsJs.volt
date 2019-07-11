<script src="{{ url("dashboard_assets/js/vendor/moment/min/moment.min.js") }}"></script>
{#<script src="{{ url("dashboard_assets/js/vendor/bootstrap-daterangepicker.js") }}"></script>#}
<script src="{{ url("dashboard_assets/js/vendor/bootstrap-daterangepicker/daterangepicker.js") }}"></script>
<script src="{{ url("dashboard_assets/js/vendor/select2.min.js?v=1.1") }}"></script>
<script src="{{ url("dashboard_assets/js/vendor/shepherd.min.js") }}"></script>
<script src="{{ url("dashboard_assets/js/vendor/bootstrap-contextmenu.js") }}"></script>
<script type="text/javascript">
    $(function () {
        var isMobile = false; //initiate as false
        // device detection
        if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
            || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0, 4))) {
            isMobile = true;
        }
        $onBoardingFinish = {{ onboardingStatus }};
        $hasSeenModal = '{{ user['seenModal'] }}';
        $onboardingAlerts = '{{ user['onboardingAlerts'] }}';
        if ($onBoardingFinish == 1) {
            $.ajax({
                url: '{{ url('account-profile/updateSeen') }}',
                type: 'POST',
                data: {
                    v: 'alerts'
                },
                dataType: 'json'
            });
            $hasToured = localStorage.getItem('tourFinished');
            // if ($hasSeenModal == 1 && $onboardingAlerts != 1) {
            if ($hasToured == null) {
                $('body').attr('id', 'ui');
                var tour;

                tour = new Shepherd.Tour({
                    defaults: {
                        classes: 'shepherd-element shepherd-open shepherd-theme-arrows',
                        showCancelLink: true
                    }
                });

                tour.addStep('step', {
                    title: 'Alerts',
                    text: 'The Alerts page displays any projects that contain your phrases',
                    attachTo: {
                        element: '#dt-opt',
                        on: 'top'
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

                tour.addStep('step', {
                    text: 'Phrases are detected in real-time and you will be alerted daily via email. You can change the notification controls in Settings',
                    attachTo: {
                        element: '#dt-opt',
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
                        }
                    ]
                });

                Shepherd.on('complete', function () {
                    localStorage.setItem('tourFinal', 'true');
                    $('body').removeAttr('id');
                    location.href = '{{ url('search') }}?t=1';
                })

                setTimeout(function () {
                    tour.start();
                }, 500);

            }
            //   }
        }


        $searchHistory = JSON.parse(localStorage.getItem('customSearchLeads'));
        $searchHistory = null;
        var startDate = "{{ defaultDateRange[0] }}";
        var endDate = "{{ defaultDateRange[1] }}";
        var maxCost = {{ maxCost }};
        var minCost = 0;
        var maxCostValue = {{ maxCost }};
        var councils = $('#councils').val();
        var filter = '';
        var filterBy = ['description'];
        var caseSensitive = false;
        var searchAddresses = false;
        var literalSearch = false;
        var excludePhrase = false;
        var metadata = false;

        // table
        var table = $('#dt-opt').DataTable({
            "serverSide": true,
            "ajax": {
                "url": "{{ url('datatables/leads?status=' ~ lead_status ~'&currentViewedLead=' ~ currentViewedLead) }}",
                "cache": false,
                "data": function (d) {
                    d.customSearch = customSearchData();
                }
            },
            "stripeClasses": [],
            "pagingType": "full_numbers",
            "paging": true,
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, 250, 500], [10, 25, 50, 100, 250, 500]],
            "stateSave": false,
            "info": false,
            "filter": false,
            "columnDefs": [
                {"targets": [0], "width": "5%"},
                {"targets": [1], "width": "10%"},
                {"targets": [2], "width": "15%"},
                {"targets": [3], "width": "20%"},
                {"targets": [4], "width": "50%"},
                {"orderable": false, "targets": 0},
                {className: "redirect text-center vertical-middle", "targets": [1, 2, 3]},
                {className: "redirect text-left vertical-middle", "targets": [4]},
                {className: "text-center vertical-middle td-council-logo", "targets": [0]}
            ],
            "processing": true,
            "language": {
                "emptyTable": "There are no data available",
                "processing": '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>',
                "lengthMenu": "Show _MENU_ <span class='length-menu-text'><i class='fa fa-spinner fa-spin'></i></span>"
            },
            "order": [[2, "desc"]],
        });

        table.on('draw', function () {
            $("html, body").animate({ scrollTop: 0 }, "fast");
            $('.timeago').timeago();
            // initialized on click redirect event
            $('.toggleInfo').click(function () {

                var tr = $(this);
                var row = table.row(tr);

                if (row.child.isShown()) {
                    // This row is already open - close it
                    $('.card-slider', row.child()).slideUp(function () {
                        row.child.hide();
                        tr.removeClass('shown');
                    });
                    tr.addClass('clicked');
                }
                else {
                    // Open this row (the format() function would return the data to be shown)
                    if (table.row('.shown').length) {
                        $('.redirect', table.row('.shown').node()).click();
                    }

                    $dasId = $(this).attr('id');
                    {#window.location.href = "{{ url('leads/') }}" + $dasId + "/view?from=leads";#}

                    console.log('for accordion', $dasId);
                    row.child(format($dasId)).show();
//          $('.clicked').removeClass('clicked');
                    tr.addClass('shown');
                    $('.card-slider', row.child()).slideDown();

                    setTimeout(function () {
                        // get Documents and Parties
                        $.ajax({
                            url: '{{ url('search/getDocumentsAndParties?ajax=1') }}',
                            type: 'POST',
                            data: {
                                dasId: $dasId,
                                from: 'leads'
                            },
                            dataType: 'json'
                        }).done(function (response) {
                            console.log(response);


                            // check if da is saved
                            if (response.saved == true) {
                                $('#star-' + $dasId).removeClass('ion-ios7-star-outline').addClass('ion-ios7-star starred').attr('data-starred', true);
                            } else {
                                $('#star-' + $dasId).removeClass('ion-ios7-star starred').addClass('ion-ios7-star-outline').attr('data-starred', false);
                            }

                            // create table
                            if (response.documents.length > 0) {
                                $docTable = '<table class="table>">';
                                $docTable += '<tbody>';
                                for ($d = 0; $d < response.documents.length; $d++) {
                                    $docTable += '<tr><td><a target="_blank" href="' + response.documents[$d].url + '">' + response.documents[$d].name + '</a></td></tr>';
                                }
                                $docTable += '</tbody>';
                                $docTable += '</table>';
                            } else {
                                $docTable = 'No documents available.';
                            }

                            $('#card-document-container-' + $dasId).html($docTable);
                            $partTable = '<table class="table">';
                            $partTable += '<tbody>';
                            $partTable += '<tr><td><strong>Ref.</strong></td><td>' + response.info.councilReference + '</td></tr>';
                            if (response.addresses.length > 0) {
                                for ($a = 0; $a < response.addresses.length; $a++) {
                                    $partTable += '<tr><td><strong>Address</strong></td><td class="break-word"><a target="_blank" href="https://www.google.com/maps/place/' + response.addresses[$a] + '">' + response.addresses[$a] + '</a></td></tr>';
                                }
                            }
                            if (response.parties.length > 0) {
                                for ($p = 0; $p < response.parties.length; $p++) {
                                    $partTable += '<tr><td><strong>' + response.parties[$p].role + '</strong></td><td class="break-word"><a target="_blank" href="https://www.google.com.au/search?newwindow=1&q=' + response.parties[$p].name + '">' + response.parties[$p].name + '</a></td></tr>';
                                }
                            }
                            $partTable += '<tr><td><strong>Link</strong></td><td class="break-word"><a href="' + response.info.councilUrl + '" target="_blank">' + response.info.councilUrl + '</a></td></tr>';
                            $partTable += '</tbody>';
                            $partTable += '</table>';
                            $('#card-party-container-' + $dasId).html($partTable);


                            if (response.notes != false) {
                                $notesHtml = response.notes.note;
                                $('#note-btn-' + $dasId).attr('data-id', response.notes.id);
                            } else {
                                $notesHtml = '';
                                $('#note-btn-' + $dasId).attr('data-id', 0);
                            }
                            $('#note-btn-' + $dasId).attr('data-target-id', 'ta-' + $dasId);
                            $('#note-btn-' + $dasId).attr('data-das-id', $dasId);
                            $('#card-notes-container-' + $dasId).html('<textarea id="ta-' + $dasId + '" rows="3" placeholder="Add notes" class="form-control mrg-btm-20">' + $notesHtml + '</textarea>');


                            getNewAlertCount();

                        });

                        $('.noteBtn').unbind().on('click', function () {
                            $btn = $(this);
                            $btn.html('<i class="fa fa-spinner fa-spin"></i>');
                            $btn.prop('disabled', true);

                            $id = $btn.attr('data-id');
                            $dasId = $btn.attr('data-das-id');
                            $target = $btn.attr('data-target-id');
                            $note = $('#' + $target).val();
                            $.ajax({
                                url: '{{ url('search/saveDaNotes?ajax=1') }}',
                                type: 'POST',
                                data: {
                                    id: $id,
                                    dasId: $dasId,
                                    note: $note
                                },
                                dataType: 'json'
                            }).done(function (response) {
                                if (response == true) {
                                    showNotification('Note successfully saved', 'success');
                                } else {
                                    showNotification('Ops. Something went wrong please try again.', 'error');
                                }

                                $btn.prop('disabled', false);
                                $btn.html('Save');
                            });
                        });


                        $('.shareBtn').unbind().click(async function () {
                            $btn = $(this);
                            $btn.html('<i class="fa fa-spinner fa-spin font-size-20"></i>');
                            $btn.prop('disabled', true);
                            $dasId = $(this).attr('data-id');
                            $.ajax({
                                url: '{{ url('notifications/getUsersEmail?ajax=1') }}',
                                type: 'GET',
                                data: {
                                    columnClass: 'col-sm-12'
                                },
                                dataType: 'json'
                            }).done(function (response) {
                                $('#shareContainer').html(response.share);
                                $('#shareModal').modal('show');
                                $('#sendShareBtn').attr('data-id', $dasId);
                                $btn.html('<i class="ion-ios7-upload-outline font-size-20"></i>');
                                $btn.prop('disabled', false);
                            });
                        });

                        $('.star').unbind().on('click', function () {
                            $starIcon = $(this).find('.star-icon');
                            $status = ($starIcon.attr('data-starred') == 'true' ? 1 : 2);
                            $leadId = $(this).attr('data-id');
                            console.log('star');
                            $.ajax({
                                url: '{{ url('leads/save?ajax=1') }}',
                                type: 'POST',
                                data: {"lead_id": $leadId},
                                dataType: 'json'
                            }).done(function (response) {
                                if (response.status == 'ok') {
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

                        $('.downloadPdfBtn').unbind().click(async function () {
                            $btn = $(this);
                            $btn.html('<i class="fa fa-spinner fa-spin font-size-20"></i>');
                            $btn.attr('disabled', true);
                            var dasId = $(this).attr('data-id');
                            await downloadPdfZip($btn, dasId, 1, [], 0).then(
                                async result => {
                                    if (result.length > 0) {
                                        var paths = [];
                                        var indexCount = 0
                                        var nextIndex = 0;
                                        $btn.html('<div style="width: 100px; padding: 1px;"><div class="progress progress-striped active">' +
                                            '<div class="progress-bar progress-bar-btn"  role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">' +
                                            '<span class="percent-text">0%</span>' +
                                            '</div>' +
                                            '</div></div>')
                                        while (indexCount < result.length) {
                                            await downloadPdfZip($btn, dasId, 2, result[nextIndex], nextIndex).then(
                                                async response => {
                                                    paths.push(result[nextIndex]['path']);
                                                    if (nextIndex + 1 == result.length) {
                                                        await downloadPdfZip($btn, dasId, 3, paths, 0).then(
                                                            response => console.log(response)
                                                        );
                                                    }
                                                    console.log(nextIndex, result.length)
                                                    nextIndex++;
                                                    console.log(paths);
                                                }
                                            )
                                            indexCount++;
                                            $percent = (indexCount / result.length) * 100;
                                            $('.progress-bar').css({width: $percent.toFixed(0) + '%'});
                                            $('.percent-text').html($percent.toFixed(0) + '%');
                                        }

                                    }
                                }
                            );
                        });
                    }, 500);

                }


            });


            $('.length-menu-text').html(' of ' + formatNumber(table.page.info().recordsDisplay) + ' entries');

            // Context Menu
            $('.context-menu').contextmenu({
                target: '#context-menu',
                autoHide: false,
                before: function (e) {
                    $firstChildElement = $(this)[0]['$element'][0]['firstElementChild'].parentNode;
                    $id = $firstChildElement.getAttribute('id');

                    $('.sendTo').attr('data-id', $id);
                    return true;
                },
                onItem: function (context, e) {
                    $className = e['currentTarget']['className'];
                    if ($className.indexOf('sendTo') != -1) {
                        console.log(true);
                    } else {
                        return false;
                    }


                }

            });

            setTimeout(function () {
                $('#dt-opt').removeAttr('style');
            }, 3000);
        });


        // daterange input
        $('#date-range-picker').daterangepicker({
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                'Last 7 Days': [moment().subtract('days', 6), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')],
                'Last Six Months': [moment().subtract('month', 6).startOf('month'), moment()]
            },
            locale: {
                format: 'DD/MM/Y'
            },
            opens: "left",
            startDate: moment().subtract('month', 6),
            endDate: moment()
        }, function (start, end) {
            startDate = moment(start).format('Y-MM-DD HH:mm:ss');
            endDate = moment(end).format('Y-MM-DD HH:mm:ss');
            //    table.ajax.reload();
        });

        // cost range

        $('.cost-select').change(function () {
            $max = parseInt($('#cost-to').val());
            $min = parseInt($('#cost-from').val());
            console.log('max', $max, 'min', $min);
            minCost = ($min > $max ? $max : $min);
            maxCost = ($max < $min ? $min : $max);
            // table.ajax.reload();
        });


        // checkbox filter

        $('.checkbox-filter').click(function () {
            searchAddresses = $('#input_search_addresses').is(':checked');
            literalSearch = $('#input_literal_search').is(':checked');
            excludePhrase = $('#input_exclude_phrase').is(':checked');
            metadata = $('#input_metadata').is(':checked');
            ///  table.ajax.reload();
        });


        // councils

        let branch_all = [];

        function formatResult(state) {
            if (!state.id) {
                var btn = $('<div class="text-right"><button id="all-branch" style="margin-right: 10px;" class="btn btn-default">Select All</button><button id="clear-branch" class="btn btn-default">Clear All</button></div>')
                return btn;
            }

            branch_all.push(state.id);
            var id = 'state' + state.id;
            var checkbox = $('<div class="checkbox-select"><input id="' + id + '" type="checkbox" ' + (state.selected ? 'checked' : '') + '><label for="checkbox1">' + state.text + '</label></div>', {id: id});
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

        if (!isMobile) {
            $('#filter1').select2({
                placeholder: "Search All",
                closeOnSelect: false,
                width: '100%',
                templateResult: formatResult,
            })

            $("#councils").select2({
                dropdownParent: $('#filterModal'),
                placeholder: "All",
                allowClear: true,
                closeOnSelect: false,
                width: '100%',
                templateResult: formatResult,
            });


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
        }

        $("#councils").removeClass('display-none');
        $("#filter1").removeClass('display-none');

        $('#councils').change(function () {
            councils = $(this).val();
            // table.ajax.reload();
        })


        // filter on key down

        $('#searchFilter').keyup(function () {
            filter = $(this).val();
            $('#searchFilterModal').val(filter);
            table.ajax.reload();
        });

        $('#filter1').change(function () {
            filterBy = $(this).val();
            console.log(filterBy);
            if (filterBy != null) {
                if (filterBy.indexOf('applicant') != -1) {
                    reInitTable([0])
                } else {
                    reInitTable([0, 4]);
                }

            } else {
                filterBy = ['applicant', 'description'];
                reInitTable([0, 4])
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


        // seenFilter button
        $("#table-filter").change(function () {
            table.draw();
        });

        // Mark as read button
        $("#bulk-markasread").click(function () {
            bulkMarkSeen(true);
        });

        // Mark as unread button
        $("#bulk-markasunread").click(function () {
            bulkMarkSeen(false);
        });

        // Move to Leads button
        $("#bulk-move-to-leads").click(function () {
            bulkUpdateLeadStatus(1);
        });

        // Move to Saved button
        $("#bulk-move-to-saved").click(function () {
            bulkUpdateLeadStatus(2);
        });

        // Export to CSV button
        $("#bulk-exportcsv").click(function () {

            var leadIds = $("input[name='select-row']").map(function () {
                if ($(this).is(":checked")) {

                    var row = $(this).parent().parent();
                    return +row.attr("id").split("_")[1];
                }
            }).get();
            var form = $(document.createElement('form'));
            $(form).attr("action", "{{ url('leads/bulkExportCsv') }}");
            $(form).attr("method", "POST");
            $(form).css("display", "none");
            leadIds.forEach(function (leadId) {

                var inputLeadId = $("<input>")
                    .attr("type", "number")
                    .attr("name", "lead_ids[]")
                    .val(leadId);
                $(form).append($(inputLeadId));
            });
            form.appendTo(document.body);
            $(form).submit();
        });


        function toggleBulkActions(show) {

            var bulkActionButtons = $(".bulk-action-button");
            if (show) {
                bulkActionButtons.removeClass("disabled");
            } else {
                bulkActionButtons.addClass("disabled");
            }
        }

        function bulkMarkSeen(read) {

            var leadIds = $(".dt-checkbox:checked").map(function () {

                var tableRow = $(this).parents(":eq(2)");
                console.log(tableRow);
                return +tableRow.attr("id").split("_")[1];

            }).get();
            $.ajax({
                url: '{{ url('leads/') }}' + (read ? 'bulkMarkAsRead' : 'bulkMarkAsUnread'),
                type: 'POST',
                data: {
                    "lead_ids": leadIds
                },
                dataType: 'json',
            }).done(function (responseData) {

                if (responseData.status == "OK") {

                    leadIds.forEach(function (lead_id) {

                        var row = $("#lead_" + lead_id + "")
                        var checkbox = row.find(".dt-checkbox");

                        checkbox.prop("checked", false);
                        checkbox.trigger("change");

                        if (read) {
                            row.removeClass();
                        } else {
                            row.removeClass().addClass("bg-unread");
                        }

                    })

                }

            });
        }

        function bulkUpdateLeadStatus(status) {

            var leadIds = $("input[name='select-row']").map(function () {
                var row = $(this).parent().parent().parent();

                if ($(this).is(":checked")) {
                    return +row.attr("id").split("_")[1];
                }
            }).get();

            $.ajax({

                url: '{{ url('leads/bulkUpdateLeadStatus') }}',
                type: 'POST',
                data: {status: +status, lead_ids: leadIds},
                dataType: 'json',
            }).done(function (responseData) {

                if (responseData.status == "OK") {
                    location.reload();
                }

            });
            $.ajax({

                url: '{{ url('leads/bulkUpdateLeadStatus') }}',
                type: 'POST',
                data: {status: +status, lead_ids: leadIds},
                dataType: 'json',
            }).done(function (responseData) {

                if (responseData.status == "OK") {
                    location.reload();
                }

            });
        }


        if ($searchHistory != null) {
            if ($searchHistory.filter != null) {
                $('#searchFilter').val($searchHistory.filter).trigger('keyup');
            }
            if ($searchHistory.councils != null) {
                if ($searchHistory.councils.length > 0) {
                    $("#councils").val($searchHistory.councils).trigger('change');
                }
            }


            if ($searchHistory.startDate != null && $searchHistory.endDate != null) {
//        $('#date-range-picker').data('daterangepicker').setStartDate(moment($searchHistory.startDate).format('DD-MM-Y HH:mm:ss'));
//        $('#date-range-picker').data('daterangepicker').setEndDate(moment($searchHistory.endDate).format('DD-MM-Y HH:mm:ss'));
            }
            if ($searchHistory.maxCost != null) {
                if ($searchHistory.maxCost > 50000000) {
                    $('#cost-to').val(50000000).trigger('change');
                } else {
                    $('#cost-to').val($searchHistory.maxCost).trigger('change');
                }
                $('#cost-from').val($searchHistory.minCost).trigger('change');
            }


            if ($searchHistory.metadata != null) {
                if ($searchHistory.metadata == true) {
                    $('#input_metadata').prop('checked', true);
                }
            }

            if ($searchHistory.searchAddresses != null) {
                if ($searchHistory.searchAddresses == true) {
                    $('#input_search_addresses').prop('checked', true);
                }
            }

            if ($searchHistory.literalSearch != null) {
                if ($searchHistory.literalSearch == true) {
                    $('#input_literal_search').prop('checked', true);
                }
            }

            if ($searchHistory.excludePhrase != null) {
                if ($searchHistory.excludePhrase == true) {
                    $('#input_exclude_phrase').prop('checked', true);
                }
            }

        }


        $('.select2 span').addClass('needsclick');

        $('.refineSearchBtn').click(function () {
            $('#searchFilter').val($('#searchFilterModal').val());
            filter = $('#searchFilterModal').val();
            table.ajax.reload();
            $('#filterModal').modal('hide');
        });

        $('.clearBtn').click(function () {
            localStorage.removeItem("customSearchLeads");
            $('#searchFilter').val('');
            $('#searchAddress').val('');
            $('#searchFilterModal').val('');
            $("#councils").val('').trigger('change');
            $('#cost-to').val(50000000).trigger('change');
            $('#cost-from').val(0).trigger('change');
            $('#input_metadata').prop('checked', false);
            $('#input_search_addresses').prop('checked', false);
            $('#input_literal_search').prop('checked', false);
            $('#input_exclude_phrase').prop('checked', false);
            table.ajax.reload();

        });


        function customSearchData() {

            localStorage.setItem("customSearchLeads", JSON.stringify({
                "startDate": startDate,
                "endDate": endDate,
                "maxCost": maxCost,
                "minCost": minCost,
                "maxCostValue": maxCostValue,
                "councils": councils,
                "filter": filter,
                "filterBy": filterBy,
                "searchAddresses": searchAddresses,
                "literalSearch": literalSearch,
                "excludePhrase": excludePhrase,
                "metadata": metadata,

            }));

            return {
                "startDate": startDate,
                "endDate": endDate,
                "maxCost": maxCost,
                "minCost": minCost,
                "maxCostValue": maxCostValue,
                "councils": councils,
                "filter": filter,
                "filterBy": filterBy,
                "searchAddresses": searchAddresses,
                "literalSearch": literalSearch,
                "excludePhrase": excludePhrase,
                "metadata": metadata,

            }
        }


    });


    function format(d) {
        $template = '<div class="row card-slider">' +
            '<div class="col-sm-12 mrg-btm-10">' +
            '<button data-id="' + d + '" class="btn btn-default pull-right downloadPdfBtn"  title="Download PDF"><i class="fa fa-download font-size-20"></i></button>' +
            '<button data-id="' + d + '" class="btn btn-default pull-right mrg-right-10 shareBtn"  title="Share"><i class="ion-ios7-upload-outline font-size-20"></i></button>' +
            '<button data-id="' + d + '" class="btn btn-default pull-right star mrg-right-10" title="Save"><i id="star-' + d + '" class="star-icon ion-ios7-star-outline font-size-20"></i></button>' +
            '</div>' +
            '<div class="col-sm-6 col-xs-12">' +
            '<div class="card child-card">' +
            '<div class="card-block break-word">' +
            '<h4 class="card-title">Documents</h4>' +
            '<div id="card-document-container-' + d + '" class="card-container"><i class="fa fa-spinner fa-spin"></i></div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="col-sm-6 col-xs-12">' +
            '<div class="card child-card">' +
            '<div class="card-block">' +
            '<h4 class="card-title">Details</h4>' +
            '<div id="card-party-container-' + d + '" class="card-container"><i class="fa fa-spinner fa-spin"></i></div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="col-sm-12 col-xs-12">' +
            '<div class="card child-card">' +
            '<div class="card-block">' +
            '<h4 class="card-title">Notes</h4>' +
            '<div id="card-notes-container-' + d + '" class="notes-container"><i class="fa fa-spinner fa-spin"></i></div>' +
            '<div><button id="note-btn-' + d + '" class="noteBtn btn btn-primary">Save</button></div>'
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>';
        // `d` is the original data object for the row
        return $template;
    }

    function openLink(elem) {
        $id = elem.getAttribute('data-id');
        $action = elem.getAttribute('data-action');
        console.log($id, $action);
        $param = '';
        if ($action != '_blank') {
            $param = "location=yes,height=" + screen.availHeight + ",width=" + screen.availWidth + ",scrollbars=yes,status=yes";
        }
        window.open("{{ url('leads/') }}" + $id + "/view?from=leads", $action, $param);
    }


</script>
