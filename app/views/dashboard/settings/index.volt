{% extends "_templates/base.volt" %}

{% block content %}
    <div id="account">
        <div id="content">

            <div id="sidebar">
                <div class="sidebar-toggler visible-xs">
                    <i class="ion-navicon"></i>
                </div>

                <h3>My account</h3>
                <ul class="menu">
                    {% set isActive = (_ns == "Aiden\Controllers" and _cn == "settings" and (_an == "index" or _an == "")) %}
                    <li>
                        <a href="{{ url('account-profile') }}" class="{% if isActive == true %} active{% endif %}">
                            <i class="ion-ios7-person-outline"></i>
                            Profile
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('billing') }}">
                            <i class="ion-card"></i>
                            Billing
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('notifications') }}">
                            <i class="ion-ios7-email-outline"></i>
                            Notifications
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('support') }}">
                            <i class="ion-ios7-help-outline"></i>
                            Support
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Content -->

            {% if page_title == 'Profile settings' %}
                {% include "settings/profile.volt" %}
                {% elseif page_title == 'Contact form' %}
                    {% include "settings/support.volt" %}
                {% elseif page_title == 'Billing' %}
                    {% include "settings/billing.volt" %}
                {% elseif page_title == 'Notifications' %}
                    {% include "settings/notifications.volt" %}
            {% endif %}


        </div>
    </div>

{% endblock %}

