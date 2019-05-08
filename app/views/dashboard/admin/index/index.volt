{% extends "_templates/base.volt" %}
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
        {% include "includes/flashMessages.volt" %}
        {% if page.items|length > 0 %}

            {% if totalUsers is defined or totalLeads is defined %}
                <div class="row mrg-top-10">
                    {% if totalUsers is defined %}
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h2 class="card-title">Users</h2>
                                    <p>{{ totalUsers }}</p>
                                </div>
                            </div>
                        </div>
                    {% endif %}

                    {% if totalLeads is defined %}
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h2 class="card-title">Leads</h2>
                                    <p>{{ totalLeads }}</p>
                                </div>
                            </div>
                        </div>
                    {% endif %}

                </div>
            {% endif %}

            {% if topCouncils is defined %}

                {% set combinedTotal = 0 %}
                {% for council in topCouncils %}
                    {% set combinedTotal = combinedTotal + council.Das|length %}
                {% endfor %}

                <div class="card">

                    <div class="card-body">

                        <h2 class="card-title">Top {{ topCouncils.count() }} councils</h2>
                        {% for council in topCouncils %}
                            {% set percentageOfLeads = round((council.relatedDas * 100) / combinedTotal) %}

                            <div class="referral">
                            <span>
                                {{ council.getName() }}
                                <div class="pull-right">
                                    <span class="data">{{ council.relatedDas }}</span>  {{ percentageOfLeads }}%
                                </div>
                            </span>
                                <div class="progress progress-primary progress-lg">
                                    <div class="progress-bar" role="progressbar" style="width: {{ percentageOfLeads }}%">
                                    </div>
                                </div>
                            </div>

                        {% endfor %}

                    </div>
                </div>

            {% endif %}
            <div id="steps">
                {% for development_application in page.items %}

                    {% set current_date = development_application.getCreated() %}

                    <div class="steps">
                        <div class="step clearfix done">
                            <div class="info">
                  <span class="number">
                    {% if development_application.Council.getLogoUrl()|length > 0 %}
                        <img class="thumb-img" src="{{ development_application.Council.getLogoUrl() }}" />
                    {% else %}
                        <img class="thumb-img" src="{{ url("aiden-assets/images/aiden-anonymous.jpg") }}" />
                    {% endif %}
                  </span>
                                <a href="" class="title no-pdd-vertical text-semibold inline-block">
                                    {{ development_application.Council.getName() }}
                                </a>
                                <span class="sub-title">
                      Uploaded a <a href="{{ url('leads/' ~ development_application.getId() ~ '/view') }}">new development application</a>
                  </span>
                                <span class="sub-title">
                      <i class="ti-timer pdd-right-5"></i>
                      <span
                          <time class="timeago" datetime="{{ current_date.format('c') }}">{{ current_date.format('d-m-Y') }}</time>
                      </span>
                                </span>
                                {% if development_application.getDescription()|length > 0 %}
                                    <div class="feed-body">
                                        {{ development_application.getDescription()|e }}
                                    </div>
                                {% endif %}
                            </div>


                        </div>
                    </div>



                {% endfor %}
            </div>
            <div class="text-center">
                {% include "includes/pagination.volt" %}
            </div>

        {% else %}

            <div class="text-center">
                <p class="lead">
                    Aaand we're done.
                </p>

            </div>

        {% endif %}
    </div>

{% endblock %}
