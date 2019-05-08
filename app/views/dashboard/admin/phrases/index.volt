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
        </div>
        <div id="datatables">
            <div class="content-wrapper">
                {% include "includes/flashMessages.volt" %}
                <div class="row">
                    {# Search Table #}
                    <table id="dt-opt"
                           class="table table-responsive-sm table-responsive-md table-responsive-lg datatables-table">
                        <thead>
                        <tr>
                            <th>
                                <div class="checkbox">
                                    <input id="checkbox-toggle-all" type="checkbox">
                                    <label for="checkbox-toggle-all"></label>
                                </div>
                            </th>
                            <th>User</th>
                            <th>Phrase</th>
                            <th>Case sensitive</th>
                            <th>Literal search</th>
                            <th>Exclude phrase</th>
                            <th>Occurences</th>
                        </tr>
                        </thead>
                        <tbody class="tbody">
                        <tr><td colspan="7" class="text-center"><i class="fa fa-spinner fa-spin"></i></td></tr>
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>

    {% include "admin/phrases/_phrasesJs.volt" %}
{% endblock %}

