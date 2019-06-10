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
                    <table id="dt-opt" class="table-hover">
                        <thead>
                        <tr>
                            <th>
                                <div class="checkbox">
                                    <input id="checkbox-toggle-all" type="checkbox">
                                    <label for="checkbox-toggle-all"></label>
                                </div>
                            </th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Level</th>
                            <th>Registered</th>
                            <th>Last login</th>
                        </tr>
                        </thead>
                        <tbody class="tbody">
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>


    <div class="modal fade" id="usersModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="modalTitle">
                        <i class="fa fa-user"></i> User
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                           <p class="usersModalBodyText"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-md-12">
                            <button class="btn btn-primary pull-right btnAction">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {#Context Menu#}
    <div id="context-menu">
        <ul class="dropdown-menu pull-left" role="menu">
            <li class="reactivate userContext" data-action="reactivate">
                <a href="javascript:;">
                   <i class="fa fa-check-circle-o"></i> <span>Reactivate free trial</span>
                </a>
            </li>
            <li class="sendEmail userContext" data-action="sendEmail">
                <a href="javascript:;">
                    <i class="fa fa-send"></i> <span>Send welcome email</span>
                </a>
            </li>
            <li class="delete userContext" data-action="delete">
                <a href="javascript:;">
                    <i class="fa fa-trash-o"></i> <span>Delete</span>
                </a>
            </li>
        </ul>
    </div>
    <!-- end context menu -->
    {% include "admin/users/_usersJs.volt" %}
{% endblock %}


