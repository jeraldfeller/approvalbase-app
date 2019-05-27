{% extends "_templates/signin_base.volt" %}
{% block content %}

    <style>
        ::-webkit-input-placeholder { text-align:left; }
        input:-moz-placeholder { text-align:left; }
    </style>
    <div class="content">
        <h3 class="text-left">Forgot your password?</h3>
        <p class="text-left">
            Enter your email address to reset your password. You may need to check your spam folder or unblock support@approvalbase.com.
        </p>
        <div class="fields mrg-top-20">
            <input type="email" class="form-control" id="email" placeholder="Email">
        </div>
        <div class="fields mrg-top-20 text-right">
            <buttom class="btn btn-primary" id="submit">Submit</buttom>
        </div>
    </div>

    {% include "includes/messengerNotification.volt" %}
    {% include "forgot-password/_indexJs.volt" %}
{% endblock %}
