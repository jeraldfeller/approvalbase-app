{% extends "_templates/base.volt" %}
{% block extra_css %}
    <link rel="stylesheet" type="text/css" href="{{ url('dashboard_assets/css/vendor/jquery.dataTables.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{ url('dashboard_assets/css/vendor/select2.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{ url('dashboard_assets/css/vendor/select2-bootstrap.css')}}" />
{% endblock %}
{% block content %}
    <div id="content">
        <div class="menubar">
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
        <div id="datatables">
            <div class="content-wrapper">
                <div class="col-md-6 col-lg-offset-3 mrg-btm-20 text-center">
                    <select id="councils" name="person" multiple class="item-info select2 display-none" style="width: 100%;">
                        {% for row in councils %}
                            <option value="{{ row.getId() }}">{{ row.getName() }}</option>
                        {% endfor %}
                    </select>
                </div>
                {% include "includes/flashMessages.volt" %}
                <div class="row">
                    {# Leads-table #}
                    <table id="dt-opt" class="data-table">
                        <thead>
                        <tr>
                            <th>
                                <div class="checkbox">
                                    <input id="checkbox-toggle-all" type="checkbox">
                                    <label for="checkbox-toggle-all"></label>
                                </div>
                            </th>
                            <th>Council</th>
                            <th>Description</th>
                            <th>Lodged</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    {% include "admin/leads/_leadsJs.volt" %}
{% endblock %}
