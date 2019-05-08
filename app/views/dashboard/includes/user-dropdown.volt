<div class="pull-right main-sidebar" id="sidebar-dark-user">
    <div class="current-user">
        <a href="{{ url('leads') }}" class="name">
            <img class="avatar" src="{{ user['imageUrl'] }}"/>
            <span class="user-name">
    {{ user['firstName'] }} {{ user['lastName'] }}
                <i class="fa fa-chevron-down"></i>
    </span>
        </a>
        <ul class="menu">
            <li>
                <a href="{{ url('account-profile') }}">Account settings</a>
            </li>
            <li>
                <a href="{{ url('billing') }}">Billing</a>
            </li>
            <li>
                <a href="{{ url('notifications') }}">Notifications</a>
            </li>
            <li>
                <a href="{{ url('support') }}">Help / Support</a>
            </li>
            <li>
                <a href="{{ url("login/destroy") }}">Sign out</a>
            </li>
        </ul>
    </div>
</div>