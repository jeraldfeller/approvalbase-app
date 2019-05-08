{% set _ns = dispatcher.getNamespaceName() %}
{% set _cn = dispatcher.getControllerName() %}
{% set _an = dispatcher.getActionName() %}

{# Dashboard / Index #}
{% set isActive = (_ns == "Aiden\Controllers" and _cn == "index" and (_an == "index" or _an == "")) %}
<li class="nav-item{% if isActive == true %} active{% endif %}">
    <a class="mrg-top-30" href="{{ url("") }}">
        <span class="icon-holder">
            <i class="ti-search"></i>
        </span>
        <span class="title">Search</span>
    </a>
</li>

{# Leads #}
{% set isActive = (_ns == "Aiden\Controllers" and _cn == "leads" and _an == "index") %}
<li class="nav-item{% if isActive == true %} active{% endif %}">
    <a href="{{ url("leads") }}">
        <span class="icon-holder">
            <i class="ti-plus"></i>
        </span>
        <span class="title">
            Leads <span class="leads-label label label-warning">{{ userTotalLeads_1 }}</span>
        </span>
    </a>
</li>

{# Saved #}
{% set isActive = (_ns == "Aiden\Controllers" and _cn == "leads" and _an == "indexSaved") %}
<li class="nav-item{% if isActive == true %} active{% endif %}">
    <a href="{{ url("leads/saved") }}">
        <span class="icon-holder">
            <i class="ti-bookmark"></i>
        </span>
        <span class="title">
            Saved <span class="leads-label label label-warning">{{ userTotalLeads_2 }}</span>
        </span>
    </a>
</li>

{# Phrases #}
{% set isActive = (_ns == "Aiden\Controllers" and _cn == "phrases" and (_an == "index" or _an == "")) %}
<li class="nav-item{% if isActive == true %} active{% endif %}">
    <a href="{{ url("phrases") }}">
        <span class="icon-holder">
            <i class="ti-tag"></i>
        </span>
        <span class="title">Phrases</span>
    </a>
</li>
