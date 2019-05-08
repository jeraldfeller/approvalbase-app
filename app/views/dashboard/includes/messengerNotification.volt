<script>

      // Notifications
      Messenger.options = {
        extraClasses: 'messenger-fixed messenger-on-top messenger-on-right',
        theme: 'flat'
      }

      function showNotification(message, type){
        if(type == 'error'){
          if(message == ''){
            message = 'Ops! something went wrong please try again.';
          }
        }
        Messenger().post({
          message: message,
          type: type,
          showCloseButton: true
        });
      }

</script>