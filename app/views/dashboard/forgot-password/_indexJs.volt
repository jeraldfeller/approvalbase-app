<script>
    $(function () {
        $('#submit').click(function () {
            $btn = $(this);
            $btn.html('<i class="fa fa-spinner fa-spin"></i>');
            $btn.attr('disabled', true);
            $email = $('#email').val();
            if ($email == '' || $email == ' ') {
                showNotification('Please provide email address.', 'info');
                $('#email').focus();
                $btn.attr('disabled', false);
                $btn.html('Submit');
            } else {
                $.ajax({
                    url: '{{ url('forgot-password/sendForgotPasswordEmail?ajax=1') }}',
                    type: 'POST',
                    data: {
                        email: $email
                    },
                    dataType: 'json'
                }).done(function (response) {
                    if (response.success == true) {
                        showNotification(response.message, 'success');
                    } else {
                        showNotification(response.message, 'error');
                    }
                    $btn.attr('disabled', false);
                    $btn.html('Submit');
                });
            }
        });

        $('#submitChange').click(function () {
            $btn = $(this);
            $btn.html('<i class="fa fa-spinner fa-spin"></i>');
            $btn.attr('disabled', true);
            $pass1 = $('#password').val();
            $pass2 = $('#password2').val();
            $id = {{ usersId }};
            if ($pass1 !== '' && $pass2 !== '') {
                if ($pass1 == $pass2) {
                    $.ajax({
                        url: '{{ url('forgot-password/changePasswordConfirm?ajax=1') }}',
                        type: 'POST',
                        data: {
                            id: $id,
                            password: $pass1
                        },
                        dataType: 'json'
                    }).done(function (response) {
                        if (response.success == true) {
                            showNotification(response.message, 'success');

                            $('.content').html('<h3 style="margin-top: 0px !important;" class="text-left">Success.</h3>'+
                            '<p class="text-left">'+
                                '<a href="/login">Login here to continue.</a>'+
                            '</p>'
                            );
                        } else {
                            showNotification(response.message, 'error');
                        }
                    });
                } else {
                    showNotification('New password and Retype password does not match.', 'info');
                }
            } else {
                showNotification('New password and Retype password cannot be empty.', 'info');
            }

            $btn.attr('disabled', false);
            $btn.html('Submit');
        });
    });
</script>
