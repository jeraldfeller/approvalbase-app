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
  });
</script>
<script src="{{ url("dashboard_assets/js/common.js?v=2.0.9") }}"></script>
</body>
</html>
