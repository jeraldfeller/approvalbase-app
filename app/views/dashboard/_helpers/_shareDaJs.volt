<script type="text/javascript">
$(function () {
  $('#sendShareBtn').click(function(){
    $btn = $(this);
    $btn.html('<i class="fa fa-spinner fa-spin"></i>');
    $btn.prop('disabled', true);
    $dasId = $btn.attr('data-id');
    $emails = $(".input-email").map(function() {
      if($(this).val() != ''){
        return $(this).val();
      }
    }).get();

    if($emails.length > 0){

      $.ajax({
        url: '{{ url('helpers/shareDa?ajax=1') }}',
        type: 'POST',
        data: {
          emails: $emails,
          dasId: $dasId
        },
        dataType: 'json'
      }).done(function (response) {
        if(response == true){
          showNotification('Application shared successfully.','success');
          $('#shareModal').modal('hide');
        }else{
          if(response.status == false){
            showNotification(response.message, 'info');
          }else{
            showNotification('Ops. Something went wrong please try again.', 'error');
          }
        }
        $btn.html('Share');
        $btn.prop('disabled', false);
      });

    }else {
      showNotification('Please put at least one email','error');
    }
  });
})
</script>