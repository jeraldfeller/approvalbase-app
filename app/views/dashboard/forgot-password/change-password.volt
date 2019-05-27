{% extends "_templates/signin_base.volt" %}
{% block content %}

    <style>
        ::-webkit-input-placeholder { text-align:left; }
        input:-moz-placeholder { text-align:left; }
    </style>
    <div class="content">
        <h3 class="text-left">Forgot your password?</h3>
        <p class="text-left">
            Enter a new password for your {{ email }} account.
        </p>
        <div class="fields mrg-top-20">
            <input type="password" class="form-control" id="password" placeholder="New password">
        </div>
        <div class="fields mrg-top-20">
            <input type="password" class="form-control" id="password2" placeholder="Retype password">
        </div>
        <div class="fields mrg-top-20 text-right">
            <buttom class="btn btn-primary" id="submitChange">Submit</buttom>
        </div>
    </div>

    {% include "includes/messengerNotification.volt" %}
    {% include "forgot-password/_indexJs.volt" %}
{% endblock %}
