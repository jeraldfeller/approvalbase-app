<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
    <title>{% if page_title is defined %}{{ page_title }}{% endif %}</title>
    <link rel="shortcut icon" href="{{ url("favicon_b.png") }}">

    {% block extra_css %}
    {% endblock %}

    {% include "_templates/_head.css.volt" %}
    {% include "_templates/_head.js.volt" %}


    {% block extra_js %}
    {% endblock %}

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-139880755-1"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'UA-139880755-1');
    </script>


    <!-- Fresh chat -->
    <script src="https://wchat.freshchat.com/js/widget.js"></script>


</head>

<body>
<div id="wrapper">

    <!-- left menu -->
    {% include "includes/leftmenu.volt" %}

    <!-- Content -->
    {% block content %}
    {% endblock %}
</div>
{% include "includes/messengerNotification.volt" %}

<script>
  $(window).scroll(function(){
    var sticky = $('.sticky'),
      scroll = $(window).scrollTop();

    if (scroll >= 50) {
      sticky.addClass('fixed');
      $('.date-range-picker-home').css({display: 'none'});
      $('#content .menubar .page-title').css({float: 'none', textAlign: 'center'});
    }
    else {
      sticky.removeClass('fixed');
      $('.date-range-picker-home').css({display: 'block'});
      $('#content .menubar .page-title').css({float: 'left', textAlign: 'left'});
    }
  });
  $(function() {
      getNewAlertCount();
      refreshGetAlertCount();
      localStorage.setItem('freshLogin', false);
  });
</script>
<script src="{{ url("dashboard_assets/js/common.js?v=2.0.9") }}"></script>
<script>
  window.fcWidget.init({
    token: "73980d49-1fd2-4f1e-ad5c-74635f2a14df",
    host: "https://wchat.freshchat.com"
  });
  // Make sure fcWidget.init is included before setting these values

  // To set unique user id in your system when it is available
  window.fcWidget.setExternalId("{{ user['id'] }}");

  // To set user name
  window.fcWidget.user.setFirstName("{{ user['firstName'] }}");

  // To set user email
  window.fcWidget.user.setEmail("{{ user['email'] }}");

  // To set user properties
  window.fcWidget.user.setProperties({
    plan: "{{ user['solution'] }}",                 // meta property 1
    status: "{{ user['subscriptionStatus'] }}"                // meta property 2
  });
</script>
</body>
</html>
