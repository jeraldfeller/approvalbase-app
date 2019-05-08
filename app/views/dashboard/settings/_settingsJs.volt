<script type="text/javascript">
  $(function () {
    $("form").submit(function(e){
      $btn = $('#save');
      $btn.html('<i class="fa fa-spinner fa-spin"></i>');
      $btn.attr('disabled', 'disabled');
      e.preventDefault();
      var formData = new FormData(this);


      $firstName = $('#firstName').val();
      $lastName = $('#lastName').val();
      $websiteUrl = $('#websiteUrl').val();
      $companyName = $('#companyName').val();
      $companyCity = $('#companyCity').val();
      $companyCountry = $('#companyCountry').val();


      $.ajax({
        type:'POST',
        url: '{{ url('account-profile/save?ajax=1') }}',
        data: formData,
        cache:false,
        contentType: false,
        processData: false,
        success:function(data){
          if(data){
            $json = JSON.parse(data);
            if($json.avatar != ''){
              $('#avatarImg').attr('src', $json.avatar+"?"+Math.random());
              $('.avatar').attr('src', $json.avatar+"?"+Math.random());

            }
            $('.user-name').html($json.name);
            {#window.location.href = '{{ url('account-profile')}}';#}
          }else{
            showNotification('Ops! Something went wrong, please try again.', 'error');
          }
          $btn.html('Save changes');
          $btn.removeAttr('disabled');
          showNotification('Profile successfully updated.', 'success');
        },
        error: function(data){
          console.log("error");
          console.log(data);
          $btn.html('Save changes');
          $btn.removeAttr('disabled');
          showNotification('Ops! Something went wrong, please try again.', 'error');
        }
      });



    });
    /*
    $('#save').click(function(){
      $btn = $(this);
      $btn.html('<i class="fa fa-spinner fa-spin"></i>');
      $btn.attr('disabled', 'disabled');

      $firstName = $('#firstName').val();
      $lastName = $('#lastName').val();
      $websiteUrl = $('#websiteUrl').val();
      $companyName = $('#companyName').val();
      $companyCity = $('#companyCity').val();
      $companyCountry = $('#companyCountry').val();

      $.ajax({
        url: '{{ url('account-profile/save?ajax=1') }}',
        type: 'POST',
        data: {
          data: {
            firstName: $firstName,
            lastName: $lastName,
            websiteUrl: $websiteUrl,
            companyName: $companyName,
            companyCity: $companyCity,
            companyCountry: $companyCountry
          }
        },
        dataType: 'json'
      }).done(function (response) {
        if(response == true){

        }else{
          alert('Ops! Something went wrong, please try again.');
        }

        $btn.html('Save changes');
        $btn.removeAttr('disabled');
      });



    });
    */
  });
</script>