{% extends "_templates/base.volt" %}
{% block extra_css %}
    <link rel="stylesheet" type="text/css"
          href="{{ url('dashboard_assets/css/vendor/bootstrap-daterangepicker.css') }}"/>
    <link href='https://api.tiles.mapbox.com/mapbox-gl-js/v0.51.0/mapbox-gl.css' rel='stylesheet' />
    <link rel='stylesheet' href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v2.3.0/mapbox-gl-geocoder.css' type='text/css' />
    <link rel="stylesheet" type="text/css"
          href="{{ url('dashboard_assets/css/dashboard.css?v=1.9') }}"/>
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
                <div class="page-title display-none">
                    <h4>{{ page_title }} {{ solution }}</h4>
                </div>
            {% endif %}

            {% include "includes/user-dropdown.volt" %}
            <div class="pull-right date-range-picker-home" style="margin-right: 190px;">
                <div class="date-range">
                    <div class="input-group input-group-sm step-date">
					  	<span class="input-group-addon">
					  		<i class="fa fa-calendar-o"></i>
					  	</span>
                        <input type="text" id="date-range-picker" class="form-control"
                               placeholder="{{ defaultDateRange[0] }} - {{ defaultDateRange[1] }}"/>
                    </div>
                </div>
            </div>
        </div>
        <div id="dashboard">
            <div class="content-wrapper">
                <div class="metrics clearfix">
                    <div class="metric">
                        <span class="field">Applications</span>
                        <span class="data data-applications"><i class="icomoon icomoon-spinner2 icomoon-spin"></i></span>
                    </div>
                    <div class="metric">
                        <span class="field">Alerts</span>
                        <span class="data data-alerts"><i class="icomoon icomoon-spinner2 icomoon-spin"></i></span>
                    </div>
                    <div class="metric">
                        <span class="field">Councils</span>
                        <span class="data data-councils"><i class="icomoon icomoon-spinner2 icomoon-spin"></i></span>
                    </div>
                    <div class="metric">
                        <span class="field">Construction Value</span>
                        <span class="data data-value"><i class="icomoon icomoon-spinner2 icomoon-spin"></i></span>
                    </div>
                </div>

                <div class="chart">
                    <h3>
                        Documents Searched

                        <div class="total pull-right hidden-xs">
                            <span class="data-documents"><i class="icomoon icomoon-spinner2 icomoon-spin"></i></span> total
                            <div class="change up">
                                <i class="fa data-documents-inc-dec"></i>
                                <span class="data-documents-increase"><i class="icomoon icomoon-spinner2 icomoon-spin"></i></span>
                            </div>
                        </div>
                    </h3>
                    <div id="documents-chart">
                        <i class="icomoon icomoon-spinner2 icomoon-spin fa-2x"></i>
                    </div>
                </div>


                <div class="charts-half clearfix " >
                    <div class="chart pull-left">
                        <h3>
                            Alerts
                            <div class="total pull-right hidden-xs">
                                <span class="data-alerts"><i class="icomoon icomoon-spinner2 icomoon-spin"></i></span> total
                                <div class="change up">
                                    <i class="fa data-alerts-inc-dec"></i>
                                    <span class="data-alerts-increase"><i class="icomoon icomoon-spinner2 icomoon-spin"></i></span>
                                </div>
                            </div>
                        </h3>
                        <div id="alerts-chart"></div>
                    </div>
                    {% if solution == 'monitor' %}
                        <div class="chart pull-right">
                            <h3>
                                Region
                            </h3>
                            <div id="map" class='region-map'></div>
                        </div>
                    {% endif %}
                    <div class="col-sm-6">
                        <div class="referrals councils-wrapper">
                            <h3>Sources</h3>
                            <div id="councils-container">

                            </div>
                        </div>
                    </div>
                </div>


            </div>


            <div class="row">
                {#<div class="col-sm-6">#}
                {#<div class="barchart">#}
                {#<h3>Applications Save</h3>#}
                {#<div id="applications-saved-chart"></div>#}
                {#</div>#}
                {#</div>#}

            </div>

        </div>
    </div>
    {% include "index/_indexJs.volt" %}
{% endblock %}
