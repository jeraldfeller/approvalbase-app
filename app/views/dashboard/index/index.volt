{% extends "_templates/base.volt" %}
{% block extra_css %}
    <link rel="stylesheet" type="text/css"
          href="{{ url('dashboard_assets/css/vendor/bootstrap-daterangepicker.css') }}"/>
    <link href='https://api.tiles.mapbox.com/mapbox-gl-js/v0.51.0/mapbox-gl.css' rel='stylesheet' />
    <link rel='stylesheet' href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v2.3.0/mapbox-gl-geocoder.css' type='text/css' />
    <link rel="stylesheet" type="text/css"
          href="{{ url('dashboard_assets/css/dashboard.css?v=1.9') }}"/>
    <style>
        body{
            font-size: 14px !important;
        }
        table.dataTable{
            margin-bottom: 0 !important;
        }
    </style>
{% endblock %}
{% block extra_js %}

{% endblock %}
{% block content %}
    <div id="content" style="height: auto;">
        <div class="menubar sticky">
            <div class="sidebar-toggler visible-xs">
                <i class="ion-navicon"></i>
            </div>
            {% if page_title is defined %}
                <div class="page-title">
                    <h4>{{ page_title }}</h4>
                </div>
            {% endif %}

            {% include "includes/user-dropdown.volt" %}
        </div>
        <div id="reports">
            <div class="content-wrapper">
                <div class="stats clearfix">
                    <div class="stat">
                        <label>New Alerts</label>
                        <div class="value">
                            <span class="data-alerts">
                                <i class="icomoon icomoon-spinner2 icomoon-spin"></i>
                            </span>
                            <div class="change  data-alerts-percent">
                                <i class="fa     data-alerts-percent-caret"></i>
                                <span class="data-alerts-percent-value"><i class="icomoon icomoon-spinner2 icomoon-spin"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="stat">
                        <label>Saved</label>
                        <div class="value">
                            <span class="data-alerts-saved">
                                <i class="icomoon icomoon-spinner2 icomoon-spin"></i>
                            </span>
                        </div>
                    </div>
                    <div class="stat">
                        <label>Projects</label>
                        <div class="value">
                            <span class="data-projects">
                                <i class="icomoon icomoon-spinner2 icomoon-spin"></i>
                            </span>
                            <div class="change data-projects-percent">
                                <i class="fa data-projects-percent-caret"></i>
                                <span class="data-projects-percent-value"><i class="icomoon icomoon-spinner2 icomoon-spin"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="stat">
                        <label>Value</label>
                        <div class="value data-value">
                            <i class="icomoon icomoon-spinner2 icomoon-spin"></i>
                        </div>
                    </div>
                </div>



                <!-- CHART -->
                <div class="chart">
                    <h3>
                        Projects

                        <div class="btn-group pull-right" data-toggle="buttons">
                            <label class="btn btn-default active dateFilter" data-value="year">
                                <input type="radio"  name="options" id="option1" /> Year
                            </label>
                            <label class="btn btn-default dateFilter" data-value="month">
                                <input type="radio"  name="options" id="option1" /> Month
                            </label>
                        </div>
                    </h3>
                    <div id="alerts-chart"></div>
                </div>



                <!-- table -->
                <div id="datatable-example_wrapper" class="dataTables_wrapper" role="grid">

                    <table id="datatable-example" style="width: 100%;">
                        <thead>
                        <tr>
                            <th >Region</th>
                            <th >Projects</th>
                            <th >Documents</th>
                            <th >Avg. Value</th>
                            <th >Total Value</th>
                        </tr>
                        </thead>
                        <tbody id="table-tbody">
                        <tr>
                            <td colspan="5" style="text-align: center;"><i class="icomoon icomoon-spinner2 icomoon-spin fa-2x"></i></td>
                        </tr>
                        </tbody>
                    </table>


                </div>


            </div>



        </div>
    </div>
    {% include "index/_indexJs.volt" %}
{% endblock %}
