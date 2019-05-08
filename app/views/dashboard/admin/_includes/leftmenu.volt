{# Admin Dashboard / Index #}
<div class="menu-section">
    <h3>ADMIN</h3>
    <ul>
        {% set isActive = (_ns == "Aiden\Controllers\Admin" and _cn == "index" and (_an == "index" or _an == "")) %}

        {# Leads #}
        {% set isActive = (_ns == "Aiden\Controllers\Admin" and _cn == "leads" and _an == "index") %}
        <li class="nav-item">
            <a href="{{ url("admin/leads") }}" class="{% if isActive == true %} active{% endif %}">
                <i class="ion-ios7-plus-empty"></i>
                <span>Alerts </span>
            </a>
        </li>



        {# Phrases #}
        {% set isActive = (_ns == "Aiden\Controllers\Admin" and _cn == "phrases" and (_an == "index" or _an == "")) %}
        <li class="nav-item">
            <a href="{{ url("admin/phrases") }}" class="{% if isActive == true %} active{% endif %}">
                <i class="ion-ios7-pricetag"></i>
                <span class="title">Filters</span>
            </a>
        </li>

        {# Users #}
        {% set isActive = (_ns == "Aiden\Controllers\Admin" and _cn == "users" and (_an == "index" or _an == "")) %}
        <li class="nav-item">
            <a href="{{ url("admin/users") }}" class="{% if isActive == true %} active{% endif %}">
                <i class="ion-ios7-person"></i>
                <span class="title">Users</span>
            </a>
        </li>
        {# Councils #}
        {% set isActive = (_ns == "Aiden\Controllers\Admin" and _cn == "councils" and (_an == "index" or _an == "")) %}
        <li class="nav-item">
            <a href="{{ url("admin/councils") }}" class="{% if isActive == true %} active{% endif %}">
                <i class="ion-ios7-briefcase"></i>
                <span class="title">Councils</span>
            </a>
        </li>
    </ul>
</div>