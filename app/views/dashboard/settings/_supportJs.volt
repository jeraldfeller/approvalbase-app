<script type="text/javascript">
  $(function () {
    $("form").submit(function (e) {
      $btn = $('#send');
      $btn.html('<i class="fa fa-spinner fa-spin"></i>');
      $btn.attr('disabled', 'disabled');
      e.preventDefault();

      $subject = $('#subject').val();
      $message = $('#message').val();

      $.ajax({
        url: '{{ url('account-profile/save?ajax=2') }}',
        type: 'POST',
        data: {
          subject: $subject,
          message: $message
        },
        dataType: 'json',
        success: function (data) {
          if(data == true){
            $('#subject').val('');
            $('#message').val('');
            showNotification('Message successfully sent.', 'success');
          }else{
            showNotification('Ops! Something went wrong, please try again.', 'error');
          }

          $btn.html('Send');
          $btn.removeAttr('disabled');

        },
        error: function (data) {
          $btn.html('Send');
          $btn.removeAttr('disabled');
        }
      })
    });


    $('#restartOnboardingBtn').click(function(){
        $btn = $(this);
        $btn.html('<i class="fa fa-spinner fa-spin"></i>');
        $btn.attr('disabled', 'disabled');
        $.ajax({
            url: '{{ url('account-profile/resetOnboarding') }}',
            type: 'POST',
            data: {},
            dataType: 'json',
            success: function (data) {
                if(data == true){
                    showNotification('Onboarding successfully restarted.', 'success');
                    localStorage.setItem('freshLogin', 'true');
                    $('#confirmModal').modal('hide');
                    setTimeout(function(){
                        location.href = '/search';
                    }, 1000);
                }else{
                    showNotification('Ops! Something went wrong, please try again.', 'error');
                }
                $btn.html('Restart');
                $btn.removeAttr('disabled');
            },
            error: function (data) {
                $btn.html('Restart');
                $btn.removeAttr('disabled');
            }
        })
    });
  });
</script>