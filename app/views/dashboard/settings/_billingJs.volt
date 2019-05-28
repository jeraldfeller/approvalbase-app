<script src="https://checkout.stripe.com/checkout.js"></script>
<script>
    $(function(){

      $('#cancelButton').click(function(){
        $btn = $(this);
        $btn.html('<i class="fa fa-spinner fa-spin"></i>');
        $btn.prop('disabled', true);
        $.ajax({
          url: '{{ url('billing/cancelSubscription?ajax=1') }}',
          type: 'POST',
          data: {},
          dataType: 'json',
          success: function (data) {
            if(data == true){
              showNotification('Subscription successfully canceled.', 'success');
            }else{
              showNotification('Ops! Something went wrong, please try again.', 'error');
            }
            $('body').unblock();
            setTimeout(function(){
              location.href = '/billing';
            }, 1000);
            $btn.html('Cancel Subscription');
            $btn.prop('disabled', false);
          },
          error: function (data) {
            showNotification('Ops! Something went wrong, please try again.', 'error');
            $('body').unblock();
            $btn.html('Cancel Subscription');
            $btn.prop('disabled', false);
          }
        })
      });

      var handler = StripeCheckout.configure({
        key: '{{ stripeApiKey }}',
        image: '{{ url() }}dashboard_assets/images/logo-sm.png',
        locale: 'auto',
        token: function(token) {
          $('body').block({ message: '<h3><i class="fa fa-spinner fa-spin"></i> Processing....</h3>' });
          // You can access the token ID with `token.id`.
          // Get the token ID to your server-side code for use.
          console.log(token);
          $.ajax({
            url: '{{ url('billing/subscribe?ajax=1') }}',
            type: 'POST',
            data: {
              token: token.id
            },
            dataType: 'json',
            success: function (data) {
              if(data == true){
                showNotification('Payment successfully completed.', 'success');
              }else{
                showNotification('Ops! Something went wrong, please try again.', 'error');
              }
              $('body').unblock();
              location.href = '/billing';
            },
            error: function (data) {
              console.log('ER: ', data);
              showNotification('Ops! Something went wrong, please try again.', 'error');
              $('body').unblock();
            }
          })
        }
      });

      document.getElementById('customButton').addEventListener('click', function(e) {
        // Open Checkout with further options:
        handler.open({
          name: 'Approval Base',
          description: '${{ subscriptionCost }}/mo',
          amount: {{ subscriptionCost }}00,
          email: '{{ user['email'] }}'
        });
        e.preventDefault();
      });

      // Close Checkout on page navigation:
      window.addEventListener('popstate', function() {
        handler.close();
      });
    })
</script>
