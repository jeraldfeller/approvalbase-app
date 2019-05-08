{% extends "_templates/base.volt" %}
{% block content %}
    <div id="content">
        <div class="menubar">
            <div class="sidebar-toggler visible-xs">
                <i class="ion-navicon"></i>
            </div>
            <div class="options">
                <a href="{{ url('admin/users') }}">&larr; Back to Users</a>
            </div>
        </div>
        {% include "includes/flashMessages.volt" %}


        <div class="mrg-top-10">
            <div class="row">

                <div class="col-lg-6">

                    <div class="card">

                        <div class="card-block">

                            <h4 class="card-title">User</h4>

                            {% if loggedInUser.getImageUrl() != null %}
                                <div class="text-center">
                                    <img src="{{ url(user.getImageUrl()) }}" class="img-circle img-thumbnail">
                                </div>
                            {% endif %}

                            <form class="form" role="form" method="post" action="{{ url('admin/users/' ~ loggedInUser.getId() ~ '/update') }}">

                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" class="form-control" placeholder="Enter name" value="{{ loggedInUser.getName()|e }}" id="input_name" name="input_name" disabled>
                                </div>
                                <div class="form-group">
                                    <label>Email address</label>
                                    <input type="email" class="form-control" placeholder="Enter email" value="{{ loggedInUser.getEmail() }}" id="input_email" name="input_email" disabled>
                                </div>

                                <div class="form-group">
                                    <label>Level</label>
                                    <select class="form-control" id="input_level" name="input_level"{% if loggedInUser.getLevel() == constant("Aiden\Models\Users::LEVEL_ADMINISTRATOR") %} disabled{% endif %}>
                                        <option value="{{ constant("Aiden\Models\Users::LEVEL_REGISTERED") }}"{% if loggedInUser.getLevel() == constant("Aiden\Models\Users::LEVEL_REGISTERED") %} selected{% endif %}>Registered</option>
                                        <option value="{{ constant("Aiden\Models\Users::LEVEL_USER") }}"{% if loggedInUser.getLevel() == constant("Aiden\Models\Users::LEVEL_USER") %} selected{% endif %}>User</option>
                                        <option value="{{ constant("Aiden\Models\Users::LEVEL_ADMINISTRATOR") }}"{% if loggedInUser.getLevel() == constant("Aiden\Models\Users::LEVEL_ADMINISTRATOR") %} selected{% endif %}>Administrator</option>
                                    </select>
                                </div>


                                <div class="form-group action">
                                    <input type="submit" class="btn btn-success" value="Save changes">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">

                    <div class="card">
                        <div class="card-block">

                            <h4 class="card-title">Phrases</h4>
                            {% if loggedInUser.Phrases.count() > 0 %}
                                <table class="table">
                                    <tbody>
                                    {% for phrase in loggedInUser.Phrases %}
                                        <tr>
                                            <td>{{ phrase.getPhrase() }}</td>
                                            <td>{{ phrase.getCaseSensitiveCheckboxHtml(true) }}</td>
                                            <td>{{ phrase.getLiteralSearchCHeckboxHtml(true) }}</td>
                                            <td>{{ phrase.getExcludePhraseCheckboxHtml(true) }}</td>
                                        </tr>
                                    {% endfor %}
                                    </tbody>
                                </table>
                            {% else %}

                                <p>This user hasn't created any phrases yet.</p>

                            {% endif %}

                        </div>
                    </div>


                </div>



            </div>
        </div>
    </div>
{% endblock %}



