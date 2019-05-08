<!DOCTYPE html>
<html lang="en">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
        <title>{% if page_title is defined %}{{ page_title }}{% endif %}</title>
        <link rel="shortcut icon" href="{{ url("favicon.png") }}">

        {% block extra_css %}
        {% endblock %}

        {% include "_templates/_head.css.volt" %}
        {% include "_templates/_head.js.volt" %}


        {% block extra_js %}
        {% endblock %}


    </head>

    <body>
        <div class="wrapper">
            <div class="wrapper-inner">
    
                <!-- Content -->
                <div class="content clearfix">
    
                    <!-- Login Section -->
                    <div class="section-block replicable-content window-height bkg-heavy-rain-gradient">
                        <div class="row flex v-align-middle">
                            <div class="column width-6 offset-3">
                                <div>
                                    <div class="logo mb-50">
                                        <div class="logo-inner center">
                                            <a href="{{ url('') }}"><img src="{{ url("/front-end/assets/images/logo-blue.png") }}" width="300" alt="Approval Base" /></a>
                                        </div>
                                    </div>
                                    {% block content  %}
                                    {% endblock %}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Login Section End -->
    
                </div>
                <!-- Content End -->
    
            </div>
        </div>
    </body>
</html>
