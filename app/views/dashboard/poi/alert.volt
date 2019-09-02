{% extends "_templates/base.volt" %}
{% block extra_css %}
    <link rel="stylesheet" type="text/css" href="{{ url('dashboard_assets/css/mapbox.css?v=4.7.10')}}" />
    <link href='https://api.tiles.mapbox.com/mapbox-gl-js/v0.51.0/mapbox-gl.css' rel='stylesheet' />
    <link rel='stylesheet' href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v2.3.0/mapbox-gl-geocoder.css' type='text/css' />

    <style>
        .pad2 {
            padding: 20px;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }
        .marker {
            border: none;
            cursor: pointer;
            height: 26px;
            width: 26px;
            background-image: url({{ url('dashboard_assets/images/map-marker.png?v=1.3') }});
            background-color: rgba(0, 0, 0, 0);
        }

        .message{
            display: -ms-flex;
            display: -webkit-flex;
            display: flex;
        }

    </style>
{% endblock %}
{% block extra_js %}

{% endblock %}
{% block content %}
    <div id="content">
        <div class="menubar sticky">
            <div class="sidebar-toggler visible-xs">
                <i class="ion-navicon"></i>
            </div>
            {% if page_title is defined %}
                <div class="page-title display-none">
                    <h4>{{ page_title }}</h4>
                </div>
            {% endif %}
            {% include "includes/user-dropdown.volt" %}
            <div class="pull-right" style="margin-right: 190px;">
                <button class="btn btn-primary map-style-change" data-style="{{ template }}" disabled>Map view</button>
                <button class="btn btn-default map-style-change" data-style="mapbox://styles/mapbox/satellite-v9">Satellite view</button>
            </div>
        </div>
        <div id="sidebar">
            <div id="app-sidebar">
                <div class="scroll-wrapper">
                    <div class="tabs">
                        <ul>
                            <li>
                                <a href="#" class="active-da" data-type="alpha">Primary</a>
                            </li>
                            <li>
                                <a href="#" data-type="beta"> Secondary</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content tab-content-alert">
                        <div class="tab alpha active-da">
                            <div class="messages" id="listings-alpha">
                            </div>
                        </div>
                        <div class="tab beta" >
                            <div class="messages" id="listings-beta">
                            </div>
                        </div>
                    </div>

                </div>
            </div>


            <div id="app-message">
                <div class="mapbox-side-bar-toggle" title="collapse"><i class="ion-ios7-arrow-back toggle-icon"></i></div>
                <div id='map' class='map'>
                </div>
            </div>
        </div>
    </div>
    {% include "poi/_alertJs.volt" %}
{% endblock %}
