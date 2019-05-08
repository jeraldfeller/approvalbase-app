{% extends "_templates/base.volt" %}
{% block extra_css %}

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
            <div class="pull-right" style="margin-right: 190px;">
                <a id="bulk-subscribe" class="bulk-action-button btn btn-default disabled" href="javascript:void(0);">Subscribe</a>
                <a id="bulk-unsubscribe" class="bulk-action-button btn btn-default disabled" href="javascript:void(0);">Unsubscribe</a>
            </div>
        </div>
        <div id="datatables">
            <div class="content-wrapper">
                {% include "includes/flashMessages.volt" %}
                <div class="row">
                    {# Council table #}
                    <table id="dt-opt" class="table table-responsive-sm table-responsive-md table-responsive-lg datatables-table">
                        <thead>
                        <tr>
                            <th>
                                <div class="checkbox">
                                    <input id="checkbox-toggle-all" type="checkbox">
                                    <label for="checkbox-toggle-all"></label>
                                </div>
                            </th>
                            <th>Council</th>
                            <th>URL</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>
    {% include "councils/_councilsJs.volt" %}
{% endblock %}




