{% extends "_templates/base.volt" %}
{% block content %}
<div class="content clearfix">
        <div class="section-block bkg-grey-ultralight contact-block bkg-transparent">
          <div class="row">
            <div class="column width-8 offset-2">
              <!-- Pricing/Sign Up Section -->
              <div class="">
                <div class="row">
                  <div id="Free-Trial" class="column width-12 center">
                    <h2>Free 21 Day Trial<br/>
                      <span class="lead">Full access. No credit card required.</span>
                    </h2>
                  </div>
                </div>
                <div class="row">
                  <div class="column width-10 offset-1">
                    <div class="signup-box box rounded xlarge shadow bkg-white signup-box_custom">
                      <div class="register-form-container">
                        {{ form('signup/do', 'method' : 'post', "class" : "signup-form" , "novalidate" : "true") }}
                          <div class="row merged-form-elements">
                            <div class="column width-6">
                              <div class="field-wrapper">
                                {{ form.render('name', ['class' : 'form-firstname form-element rounded medium', 'placeholder' : 'First Name', 'autofocus':'']) }}
                              </div>
                            </div>
                            <div class="column width-6">
                              <div class="field-wrapper">
                                {{ form.render('lname', ['class': 'form-firstname form-element rounded medium', 'placeholder': 'Last Name']) }}
                                <i class="icon-user"></i>
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="column width-12">
                              <div class="field-wrapper">
                                {{ form.render('email', ['class': 'form-email form-element rounded medium', 'placeholder': 'Your Business Email']) }}
                                <i class="icon-mail"></i>
                              </div>
                            </div>
                            <div class="column width-12">
                              <div class="field-wrapper">
                                  {{ form.render('websiteUrl', ['class': 'form-element rounded medium', 'placeholder': 'Your Website URL']) }}
                                placeholder="Your Website URL" required="">
                                <i class="icon-cloud"></i>
                              </div>
                            </div>
                            <div class="column width-12">
                              <div class="field-wrapper">
                                  {{ form.render('companyName', ['class': 'form-element rounded medium', 'placeholder': 'Your Company Name']) }}
                                <i class="icon-globe"></i>
                              </div>
                            </div>
                            <div class="column width-12">
                              <div class="field-wrapper">
                                  {{ form.render('companyCountry', ['class': 'form-element rounded medium', 'placeholder': 'Your Company Country']) }}
                                <i class="icon-globe"></i>
                              </div>
                            </div>
                            <div class="column width-12">
                              <div class="field-wrapper">
                                  {{ form.render('companyCity', ['class': 'form-element rounded medium', 'placeholder': 'Your Company City']) }}
                                <i class="icon-map"></i>
                              </div>
                            </div>
                            <div class="column width-12">
                              <div class="field-wrapper">
                                {{ form.render('password', ['class': 'form-email form-element rounded medium', 'placeholder': 'Password']) }}
                                <i class="icon-lock"></i>
                              </div>
                            </div>
                            <div class="column width-12">
                              <div class="field-wrapper">
                                {{ form.render('password_confirmation', ['class': 'form-email form-element rounded medium', 'placeholder': 'Password Confirmation']) }}
                                <i class="icon-lock"></i>
                              </div>
                            </div>
                            <div class="column width-12 center mt-40">
                              <p>By submitting this form, you accept our <a href="#">Terms of Service.</a></p>
                            </div>
                            <div class="column width-6 center mt-20">
                              {{ form.render('submit', ['class': 'width-12 btn-sign-up active', 'value': 'Sign Up']) }}
                            </div>
                            <div class="column width-6 center mt-20">
                              <button class="width-12 btn-sign-up mt-20">Sign In</button>
                            </div>
                            <div class="mt-40"></div>
                          </div>
                        {{ endform() }}
                        <div class="form-response show center mt-20">{% include "includes/flashMessages.volt" %}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

{% endblock %}






