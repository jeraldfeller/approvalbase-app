{% extends "_templates/auth_base.volt" %}
{% block content %}
<div>
    <div class="signup-box box rounded xlarge bkg-white shadow">
        <h3 class="center">Sign in to continue</h3>
        <div class="login-form-container">
            {{ form('login/do', 'method' : 'post', "class" : "login-form") }}
                <div class="row">
                    <div class="column width-12">
                        <div class="field-wrapper">
                            {{ form.label('email', ['class' : 'color-charcoal']) }}
                            {{ form.render('email', ['class' : 'form-username form-element rounded medium', 'placeholder' : 'name@company.com']) }}
                        </div>
                    </div>
                    <div class="column width-12">
                        <div class="field-wrapper">
                            {{ form.label('password', ['class' : 'color-charcoal']) }}
                            {{ form.render('password', ['class' : 'form-password form-element rounded medium']) }}
                        </div>
                    </div>
                    <div class="column width-6">
                        <div class="field-wrapper pt-0 pb-20 left">
                            <input id="checkbox-1" class="form-element checkbox rounded" name="remember_me" value="1" v-model="remember_me" type="checkbox">
                            <label for="checkbox-1" class="checkbox-label no-margins">Remember Me</label>
                        </div>
                    </div>
                    <div class="column width-6">
                        <div class="field-wrapper pt-0 pb-20 right">
                            <a class="text-small" href="#">I forgot my password</a>
                        </div>
                    </div>
                    <div class="column width-12 center">
                        {{ form.render('submit', ['value' : 'Sign In', 'class' : 'form-submit button rounded medium bkg-green bkg-hover-theme bkg-focus-green color-white color-hover-white mb-0']) }}
                    </div>
                </div>

            {{ endform() }}
            <div class="form-response show center mt-20">{% include "includes/flashMessages.volt" %}</div>
        </div>
    </div>
    <p class="mb-20 center" style="color: #212325;">Need a Free Trial Account? <a href="{{ url('signup') }}">Click here</a></p>
</div>

{% endblock %}
