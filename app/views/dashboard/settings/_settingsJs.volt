<script type="text/javascript">
    function changeAvatar(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('.profile-photo').css({
                    'backgroundImage': "url('"+e.target.result+"')"
                });
                // $("form").submit();
            };

            reader.readAsDataURL(input.files[0]);
        }
    }
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
              // $('.avatar').attr('src', $json.avatar+"?"+Math.random());
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
  });

</script>