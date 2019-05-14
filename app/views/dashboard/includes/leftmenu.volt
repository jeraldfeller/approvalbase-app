<div id="sidebar-clear" class="main-sidebar">

    <div class="current-user current-user-logo">
        <a href="http://approvalbase.com/"><div class="logo logo-white logo-header" style="background-image: url('{{ url('dashboard_assets/images/logo-black.png') }}')"></div></a>
    </div>
    {% include "includes/leftmenu-user.volt" %}
    {% if loggedInUser.isAdministrator() %}
        {% include "admin/_includes/leftmenu.volt" %}
    {% endif %}
    <div class="bottom-menu hidden-sm">
        <ul>
            <li style="width: 100%;"><a href="{{ url("login/destroy") }}"><i class="ion-log-out"></i></a></li>
        </ul>
    </div>
</div>
