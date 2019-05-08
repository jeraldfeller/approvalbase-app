{% extends "_templates/base.volt" %}
{% block extra_css %}
    <link rel="stylesheet" type="text/css" href="{{ url('dashboard_assets/css/vendor/shepherd.css') }}"/>
{% endblock %}
{% block extra_js %}

{% endblock %}
{% block content %}
    <div id="latest-activity">
        <div id="content">
            <div class="menubar sticky">
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
            <div class="content-wrapper">
                <div class="text-center">
                    {% include "_templates/pagination.volt" %}
                </div>


                {% for row in data %}

                    <div class="moment{% if loop.first %} first{%endif %}">
                        <div class="row event clearfix">
                            <div class="col-sm-1">
                                <div class="icon">
                                    <i class="fa fa-comment"></i>
                                </div>
                            </div>
                            <div class="col-sm-11 message">
                                {% if row['image'] is not null %}
                                    <img class="avatar" src="{{ row['image'] }}">
                                {% endif %}
                                <div class="content">
                                    <strong>{{ row['council'] }}</strong> has uploaded
                                    <a href="{{ url('leads/') }}{{ row['id'] }}/view?from=newsfeed">{{ row['reference'] }}</a>

                                    <p class="border-bottom">
                                        {{ row['description']}}
                                        {% if row['created'] is not null %}
                                            <br>
                                            <time class="timeago text-muted small" datetime="{{ row['createdC'] }}">
                                                {{ row['created'] }}
                                            </time>
                                        {% endif %}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                {% endfor %}

                <div class="text-center">
                    {% include "_templates/pagination.volt" %}
                </div>

            </div>
        </div>
    </div>

    {% include "newsfeed/_newsfeedJs.volt" %}
{% endblock %}
