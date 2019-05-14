<!DOCTYPE html>
<html lang="en" style="background-image: linear-gradient(-45deg,#5f6190 0,#525480 20%,#131b2e 100%); height: 100%;">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
        <title>{% if page_title is defined %}{{ page_title }}{% endif %}</title>
        <link rel="shortcut icon" href="{{ url("/favicon_b.png") }}">

        {% block extra_css %}
        {% endblock %}

        {% include "_templates/_head.css.volt" %}
        {% include "_templates/_head.js.volt" %}


        {% block extra_js %}
        {% endblock %}

        <style>
            body{
                height: auto;
            }
        </style>

    </head>

    <body id="signin" class="clear">
    <a href="http://approvalbase.com" class="logo">
        <img class="title-logo"
             src={{ url("front-end/assets/images/logo-black.png") }} alt="">
    </a>
    {% block content  %}
    {% endblock %}
    <footer class="footer-big-menu" id="footer" style="margin-top: 0px;">
        <span class="bottom">(c) 2019 ApprovalBase Inc. All rights reserved. <a href="#">support@approvalbase.com</a></span>
    </footer>
    <script>
      localStorage.removeItem('customSearch');
      localStorage.removeItem('customSearchLeads')
    </script>
    </body>
</html>
