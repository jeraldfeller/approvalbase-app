<script src="{{ url("dashboard_assets/js/vendor/bootstrap-contextmenu.js") }}"></script>
<script type="text/javascript">
  $(function () {
    var table = $('#dt-opt').DataTable({
      "serverSide": true,
        "responsive": true,
      "ajax": {
        "url": "{{ url("admin/datatables/users") }}",
        "data": function (d) {
          d.tableFilter = $('#table-filter').val();
        }
      },
      "stripeClasses": [],
      "pageLength": 25,
      "pagingType": "full_numbers",
      "lengthMenu": [[10, 25, 50, 100, 250, 500], [10, 25, 50, 100, 250, 500]],
      "stateSave": true,
      "columnDefs": [
        {"targets": [0], "orderable": false},
        {"targets": [0], "width": "5%"},
        {"targets": [1], "width": "25%"},
        {"targets": [0, 1, 2, 3, 4], "className": "text-center vertical-middle"},
      ],
      "language": {
        "emptyTable": "There are no users available"
      },
      "order": [[1, "asc"]]

    });

      table.on('draw', function () {
          // Context Menu
          $('.context-menu').contextmenu({
              target: '#context-menu',
              autoHide: false,
              before: function (e) {
                  $firstChildElement = $(this)[0]['$element'][0]['firstElementChild'].parentNode;
                  $id = $firstChildElement.getAttribute('id');
                  $class = $firstChildElement.getAttribute('class');
                  $class = $class.split(' ');
                  $email = $class[1];
                  $('.userContext').attr('data-id', $id);
                  $('.userContext').attr('data-email', $email);

                  return true;
              },
              onItem: function (context, e) {
                  $className = e['currentTarget']['className'];
                  $className = $className.split(' ');
                  $className = $className[0];

                  switch ($className) {
                      case 'reactivate':
                          $email = $('.reactivate').attr('data-email');
                          $id = $('.reactivate').attr('data-id');
                          $('.usersModalBodyText').html('Are you sure do you want to reactivate free trial to <strong>'+$email+'</strong>?');
                          $('.btnAction').attr('data-id', $id);
                          $('.btnAction').attr('data-action', 'reactivate');
                          $('#usersModal').modal('show');
                          break;
                      case 'sendEmail':
                          $email = $('.sendEmail').attr('data-email');
                          $id = $('.reactivate').attr('data-id');
                          $('.usersModalBodyText').html('Send welcome email to this <strong>'+$email+'</strong>?');
                          $('.btnAction').attr('data-id', $id);
                          $('.btnAction').attr('data-action', 'sendEmail');
                          $('#usersModal').modal('show');
                          break;
                      case 'delete':
                          $email = $('.delete').attr('data-email');
                          $id = $('.reactivate').attr('data-id');
                          $('.usersModalBodyText').html('Are you sure do you want to delete <strong>'+$email+'</strong>?');
                          $('.btnAction').attr('data-action', 'delete');
                          $('.btnAction').attr('data-id', $id);
                          $('#usersModal').modal('show');
                          break;
                  }
              }

          });


          setTimeout(function () {
              $('#dt-opt').removeAttr('style');
          }, 3000);
      });


      $('.btnAction').click(function(){
         $btn = $(this);
         $btn.prop('disabled', true);
         $btn.html('<i class="fa fa-spinner fa-spin"></i>');
         $action = $btn.attr('data-action');
         $id = $btn.attr('data-id');
         switch ($action) {
             case 'reactivate':
                 $.ajax({
                     url: '{{ url('admin/users/reactivateFreeTrial?ajax=1') }}',
                     type: 'POST',
                     data: {
                         id: $id
                     },
                     dataType: 'json',
                     success: function(e){
                        if(e.success == true){
                            showNotification(e.message, 'success');
                        }else{
                            showNotification(e.message, 'info');
                        }

                        $('#usersModal').modal('hide');
                        table.ajax.reload();
                     },
                     error: function (e){
                         showNotification('', 'error');
                     }
                 })
                 break;
             case 'sendEmail':
                 $.ajax({
                     url: '{{ url('admin/users/sendEmail?ajax=1') }}',
                     type: 'POST',
                     data: {
                         id: $id
                     },
                     dataType: 'json',
                     success: function(e){
                         if(e.success == true){
                             showNotification(e.message, 'success');
                         }else{
                             showNotification(e.message, 'info');
                         }
                         $('#usersModal').modal('hide');
                     },
                     error: function (e){
                         showNotification('', 'error');
                     }
                 })
                 break;
             case 'delete':
                 $.ajax({
                     url: '{{ url('admin/users/deleteUser?ajax=1') }}',
                     type: 'POST',
                     data: {
                         id: $id
                     },
                     dataType: 'json',
                     success: function(e){
                         if(e.success == true){
                             showNotification(e.message, 'success');
                         }else{
                             showNotification(e.message, 'info');
                         }
                         $('#usersModal').modal('hide');
                         table.ajax.reload();
                     },
                     error: function (e){
                         showNotification('', 'error');
                     }
                 })
                 break;

                 $btn.prop('disabled', false);
                 $btn.html('Submit');
         }
      });


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
    });

    // Clicking on first <td>, checks the checkbox
    $("#dt-opt").on("click", "td:nth-child(1)", function () {

      var checkbox = $(this).find('>.dt-checkbox');
      checkbox.trigger("click");
    });

    // Clicking on any <td> in .lead-row, except first one
    $("#dt-opt").on("click", "td:not(:first-child)", function (e) {
      var userId = $(e.target).parent().attr("id").split("_")[1];
      location.href = "{{ url('admin/users/') }}" + userId + "/view";
    });
  });

  function userAction(elem){
      console.log(elem);
  }
</script>
