{% set _ns = dispatcher.getNamespaceName() %}
{% set _cn = dispatcher.getControllerName() %}
{% set _an = dispatcher.getActionName() %}


<div class="menu-section">
    <ul>
        {# Dashboard / Index #}
        {% set isActive = (_ns == "Aiden\Controllers" and _cn == "index" and (_an == "index" or _an == "")) %}
        <li class="search-nav">
            <a href="{{ url("dashboard") }}" class="{% if isActive == true %} active{% endif %}">
                <i class="ion-stats-bars"></i>
                <span>Dashboard </span>
            </a>
        </li>

        {% if user['solution'] == 'search'  OR  user['level'] == 'Administrator' %}


            {# Search #}
            {% set isActive = (_ns == "Aiden\Controllers" and _cn == "search" and (_an == "index" or _an == "")) %}
            <li class="search-nav">
                <a href="{{ url("search") }}" class="{% if isActive == true %} active{% endif %}">
                    <i class="ion-ios7-search"></i>
                    <span>Search</span>
                </a>
            </li>


            {# Leads #}
            {% set isActive = (_ns == "Aiden\Controllers" and _cn == "leads" and _an == "index") %}
            <li class="nav-item leads-nav">
                <a href="{{ url("leads") }}" class="{% if isActive == true %} active{% endif %}">
                    <i class="ion-ios7-bell-outline"></i>
                    <span>Alerts </span>
                    <span class="badge label-purple new-alerts"></span>
                </a>
            </li>


            {# Newsfeed #}
            {% set isActive = (_ns == "Aiden\Controllers" and _cn == "newsfeed" and (_an == "index" or _an == "")) %}
            <li class="search-nav display-none">
                <a href="{{ url("newsfeed") }}" class="{% if isActive == true %} active{% endif %}">
                    <i class="fa fa-globe"></i>
                    <span>Newsfeed</span>
                </a>
            </li>



            {# Saved #}
            {% set isActive = (_ns == "Aiden\Controllers" and _cn == "leads" and _an == "indexSaved") %}
            <li class="nav-item">
                <a href="{{ url("leads/saved") }}" class="{% if isActive == true %} active{% endif %}">
                    <i class="ion-ios7-star"></i>
                    <span>Saved </span>
                </a>
            </li>



        {# Phrases #}
        {% set isActive = (_ns == "Aiden\Controllers" and _cn == "filters" and (_an == "index" or _an == "")) %}
        <li class="nav-item phrase-nav">
            <a href="{{ url("filters") }}" class="{% if isActive == true %} active{% endif %}">
                <i class="ion-ios7-pricetag"></i>
                <span class="title">Filters</span>
            </a>
        </li>
        {% endif %}

        {# Councils #}
        {% set isActive = (_ns == "Aiden\Controllers" and _cn == "councils" and (_an == "index" or _an == "")) %}
        <li class="nav-item display-none">
            <a href="{{ url("councils") }}" class="{% if isActive == true %} active{% endif %}">
                <i class="ion-ios7-briefcase"></i>
                <span class="title">Councils</span>
            </a>
        </li>

        {% if user['solution'] == 'monitor' or  user['level'] == 'Administrator' %}
            {# Poi #}
            {% set isActive = (_ns == "Aiden\Controllers" and _cn == "poi" and (_an == "index" or _an == "")) %}
            <li class="nav-item">
                <a href="{{ url("assets/primary") }}"  class="{% if isActive == true %} active{% endif %}">
                    <i class="ion-map"></i> <span>Assets</span>
                </a>
                {#<ul class="submenu">#}
                    {#<li><a href="{{ url("poi/primary") }}">Primary</a></li>#}
                    {#<li><a href="{{ url("poi/secondary") }}">Secondary</a></li>#}
                    {#<li><a href="{{ url("poi/alert") }}">Alerts</a></li>#}
                    {#<li><a href="{{ url("poi/alert/saved") }}">Saved</a></li>#}
                {#</ul>#}
            </li>
            <li class="nav-item leads-nav">
                <a href="{{ url("assets/alert") }}" class="{% if isActive == true %} active{% endif %}">
                    <i class="ion-ios7-plus-empty"></i>
                    <span>Alerts </span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url("assets/alert/saved") }}" class="{% if isActive == true %} active{% endif %}">
                    <i class="ion-ios7-star"></i>
                    <span>Saved </span>
                </a>
            </li>
        {% endif %}

    </ul>
</div>


