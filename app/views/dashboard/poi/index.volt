{% extends "_templates/base.volt" %}
{% block extra_css %}
    <link rel="stylesheet" type="text/css" href="{{ url('dashboard_assets/css/mapbox.css?v=4.7.11')}}" />
    <link href='https://api.tiles.mapbox.com/mapbox-gl-js/v0.51.0/mapbox-gl.css' rel='stylesheet'/>
    <link rel='stylesheet'
          href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v2.3.0/mapbox-gl-geocoder.css'
          type='text/css'/>

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
            background-image: url({{ url('dashboard_assets/images/map-marker.png?v=1.2') }});
            background-color: rgba(0, 0, 0, 0);
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
            <div class="page-title">
                {#<h4>{{ page_title }}</h4>#}
            </div>

        {% endif %}
        {% include "includes/user-dropdown.volt" %}
        <div class="pull-right" style="margin-right: 190px;">
            {#<a class="bulk-action-button btn btn-default" id="showModal" data-action="add">Create Asset</a>#}
            <a class="btn btn-default" data-toggle="modal" data-target="#uploadModal">Upload CSV</a>

        </div>
    </div>
    <div id="sidebar">
        <div id="app-sidebar">
            <div class="scroll-wrapper">
                <div class="tabs">
                    {% set pActive = (type == 1 ? 'active-da' : '') %}
                    {% set sActive = (type == 2 ? 'active-da' : '') %}
                    <ul>
                        <li>
                            <a href="{{ url('assets/primary') }}" class="{{ pActive }}">Primary</a>
                        </li>
                        <li>
                            <a href="{{ url('assets/secondary') }}" class="{{ sActive }}">Secondary</a>
                        </li>
                    </ul>
                </div>
                <div class="messages" id="listings">
                </div>
            </div>
        </div>


        <div id="app-message">
            <div class="mapbox-side-bar-toggle" title="collapse"><i class="ion-ios7-arrow-back toggle-icon"></i></div>
            <div id='map' class='map'>
            </div>
        </div>
    </div>


    <!-- upload modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="modalTitle">
                        CSV Upload
                    </h4>
                </div>
                <form id="form">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                    <input type="file" name="importFile" id="import-file-holder">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="type" value="{{ type }}">
                        <button type="submit" class="btn btn-success btn-md pull-right" id="submitBtn">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- end upload modal -->

    <!-- Form Modal -->
    <div class="modal fade" id="form-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="modalTitle">
                        Create Asset
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 text-center modal-loader">
                            <i class="fa fa-spinner fa-spin fa-2x"></i>
                        </div>
                        <div class="display-none form-container no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <input type="text" id="input_name" name=""
                                           placeholder="Enter Asset Name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <input type="text" id="input_address" name=""
                                           placeholder="Enter Address" class="form-control" required>
                                </div>
                            </div>
                            {% if(type == 2) %}
                            <div class="col-md-2 display-none">
                                {% else %}
                                <div class="col-md-2">
                                    {% endif %}

                                    <div class="form-group">
                                        <input type="number" id="input_radius" value="0.2" name="" step=".1"
                                               placeholder="Radius" class="form-control" required>

                                    </div>

                                </div>
                                {% if(type == 1) %}
                                    <div class="col-md-1 no-pdd-left">
                                            <span style="position: absolute;
    top: 12px; font-size: 16px;">km</span>
                                    </div>
                                {% endif %}
                                <div class="col-md-9 col-sm-12 col-xs-12">
                                    <div class="form-group search">
                                        <div class="col-sm-12 col-md-12 no-mrg-left no-mrg-right no-pdd-left no-pdd-right text-center">
                                            <div class="col-md-5 no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
                                                <select id="cost-from" name="cost-from" class="form-control cost-select"
                                                        data-smart-select>
                                                    <option value="0">Any($)</option>
                                                    <option value="250000">$250,000</option>
                                                    <option value="1000000">$1m</option>
                                                    <option value="5000000">$5m</option>
                                                    <option value="15000000">$15m</option>
                                                    <option value="25000000">$25m</option>
                                                    <option value="50000000">$50m</option>
                                                    <option value="9999999999">$100m+</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2 no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
                                                <span class="separator lh-2-5">-</span>
                                            </div>
                                            <div class="col-md-5 no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
                                                <select id="cost-to" name="cost-to" class="form-control cost-select"
                                                        data-smart-select>
                                                    <option value="9999999999">Any($)</option>
                                                    <option value="250000">$250,000</option>
                                                    <option value="1000000">$1m</option>
                                                    <option value="5000000">$5m</option>
                                                    <option value="15000000">$15m</option>
                                                    <option value="25000000">$25m</option>
                                                    <option value="50000000">$50m</option>
                                                    <option value="9999999999">$100m+</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 ">
                                    <div class="checkbox no-mrg-left no-pdd-left display-inline-block">
                                        <input type="checkbox" class="checkbox-filter" id="input_metadata"
                                               name="input_case_sensitive">
                                        <label for="input_metadata">Metadata</label>
                                    </div>
                                    <div class="display-inline-block" data-toggle="tooltip" data-html="true" data-placement="bottom" title="includes applications with no PDF attachments"><i class="fa fa-question-circle"></i></div>
                                </div>
                                <input type="hidden" id="input_id">
                                <input type="hidden" id="input_latitude">
                                <input type="hidden" id="input_longitude">
                            </div>
                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-success" id="poiSave">Save</button>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="form-modal-edit" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="modalTitle">
                            Edit Asset
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 text-center modal-loader">
                                <i class="fa fa-spinner fa-spin fa-2x"></i>
                            </div>
                            <div class="display-none form-container no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <input type="text" id="input_name_edit" name=""
                                               placeholder="Enter Asset Name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <input type="text" id="input_address_edit" name=""
                                               placeholder="Enter Address" class="form-control">
                                    </div>
                                </div>
                                {% if(type == 2) %}
                                <div class="col-md-2 display-none">
                                    {% else %}
                                    <div class="col-md-2">
                                        {% endif %}
                                        <div class="form-group">
                                            <input type="number" id="input_radius_edit" name="" step=".1"
                                                   placeholder="Radius" class="form-control" required>
                                        </div>
                                    </div>
                                    {% if(type == 1) %}
                                        <div class="col-md-1 no-pdd-left">
                                            <span style="position: absolute;
    top: 12px; font-size: 16px;">km</span>
                                        </div>
                                    {% endif %}
                                    <div class="col-md-9 col-sm-12 col-xs-12">
                                        <div class="form-group search">
                                            <div class="col-sm-12 col-md-12 no-mrg-left no-mrg-right no-pdd-left no-pdd-right text-center">
                                                <div class="col-md-5 no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
                                                    <select id="cost-from_edit" name="cost-from_edit"
                                                            class="form-control cost-select" data-smart-select>
                                                        <option value="0">Any($)</option>
                                                        <option value="250000">$250,000</option>
                                                        <option value="1000000">$1m</option>
                                                        <option value="5000000">$5m</option>
                                                        <option value="15000000">$15m</option>
                                                        <option value="25000000">$25m</option>
                                                        <option value="50000000">$50m</option>
                                                        <option value="9999999999">$100m+</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2 no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
                                                    <span class="separator lh-2-5">-</span>
                                                </div>
                                                <div class="col-md-5 no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
                                                    <select id="cost-to_edit" name="cost-to_edit"
                                                            class="form-control cost-select" data-smart-select>
                                                        <option value="9999999999">Any($)</option>
                                                        <option value="250000">$250,000</option>
                                                        <option value="1000000">$1m</option>
                                                        <option value="5000000">$5m</option>
                                                        <option value="15000000">$15m</option>
                                                        <option value="25000000">$25m</option>
                                                        <option value="50000000">$50m</option>
                                                        <option value="9999999999">$100m+</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 ">
                                        <div class="checkbox no-mrg-left no-pdd-left display-inline-block">
                                            <input type="checkbox" class="checkbox-filter" id="input_metadata_edit"
                                                   name="">
                                            <label for="input_metadata_edit">Metadata</label>
                                        </div>
                                        <div class="display-inline-block" data-toggle="tooltip" data-html="true" data-placement="bottom" title="includes applications with no PDF attachments"><i class="fa fa-question-circle"></i></div>
                                    </div>
                                    <input type="hidden" id="input_id_edit">
                                </div>
                            </div>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-success" id="poiSaveEdit">Save</button>
                        </div>
                    </div>
                </div>
            </div>


        </div>
        {% include "poi/_indexJs.volt" %}
        {% endblock %}
