<script type="text/javascript">
    var solution = '{{ solution }}';
    $(function(){
        $('.notifications-checkbox').click(function(){
          $action = $(this).attr('data-action');
          $isChecked = $(this).is(':checked');
          console.log($action, $isChecked);

          if($isChecked == true){
            $('.emails-container').slideDown('display-none');
          }else{
            $('.emails-container').slideUp('display-none');
          }


          $.ajax({
            url: '{{ url('notifications/notificationsUpdate?ajax=1') }}',
            type: 'POST',
            data: {
              action: $action,
              value: ($isChecked == true ? 1 : 0)
            },
            dataType: 'json'
          }).done(function (response) {
            if(response == false){
              showNotification('Ops! Something went wrong, please try again.', 'error');
            }
          });
        });


          $.ajax({
            url: '{{ url('notifications/getUsersEmail?ajax=1') }}',
            type: 'GET',
            data: {},
            dataType: 'json'
          }).done(function (response) {
                $('#alertsContainer').html(response.alerts);
                $('#billingContainer').html(response.billing);
          });



        $('#saveUsersEmail').click(function(){
          $btn = $(this);
          $btn.html('<i class="fa fa-spin fa-spinner"></i>');
          $btn.attr('disabled', true);
          $emailsToUpdate = [];
          $('.input-email').each(function(index, i){
            $type = $(this).attr('data-type');
            $email = $(this).val();
            $id = $(this).attr('data-id');
              $emailsToUpdate.push({
                type: $type,
                email: $email,
                id: $id
              });
          });

          $.ajax({
            url: '{{ url('notifications/updateUsersEmail?ajax=1') }}',
            type: 'POST',
            data: {
              data: $emailsToUpdate
            },
            dataType: 'json'
          }).done(function (response) {
            if(response == true){
              showNotification('Emails successfully updated.', 'success');
              setTimeout(function(){
                location.reload();
              }, 1000);
            }else{
              showNotification('Ops! Something went wrong, please try again.', 'error');
            }

            $btn.html('Save');
            $btn.attr('disabled', false);
          });
        });

    });
</script>