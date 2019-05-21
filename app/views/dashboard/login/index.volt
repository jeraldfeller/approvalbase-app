{% extends "_templates/signin_base.volt" %}
{% block content %}

        <h3>Welcome back!</h3>
        <div class="content">
            <div class="form-response show center mt-20">{% include "includes/flashMessages.volt" %}</div>
            {{ form('login/do', 'method' : 'post', "class" : "login-form") }}
                <div class="fields">
                    <strong>Email address</strong>
                    {{ form.render('email', ['class' : 'form-control', 'placeholder' : 'name@company.com']) }}
                </div>
                <div class="fields">
                    <strong>Password</strong>
                    {{ form.render('password', ['class' : 'form-control']) }}
                </div>
                <div class="info">
                    <label>
                        <input type="checkbox" name="remember" checked/>
                        Remember me
                    </label>
                </div>
                <div class="actions">
                    {{ form.render('submit', ['value' : 'Sign in to your account', 'class' : 'btn btn-primary btn-sm']) }}
                </div>
            {{ endform() }}
        </div>

        <div class="bottom-wrapper">
            <div class="message" style="padding: 10px !important;">
                <span>Don't have an account?</span>
                <a href="{{ url("signup") }}">Sign up here</a>.
            </div>
        </div>


    <script>
        localStorage.removeItem('user_meta');
    </script>
{% endblock %}
