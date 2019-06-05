{% extends "_templates/signin_base.volt" %}
{% block content %}
    <div id="signup">

        <h3>Create Your Account Now</h3>
        <div class="content">
            <div class="form-response show center mt-20">{% include "includes/flashMessages.volt" %}</div>
            {{ form('signup/do', 'method' : 'post', "class" : "signup-form" , "novalidate" : "true") }}
            <div class="fields">
                <strong>Your access data</strong>
                {{ form.render('email', ['class': 'form-control', 'placeholder': 'Email Address', 'autofocus':'']) }}
                {{ form.render('password', ['class': 'form-control', 'placeholder': 'Password']) }}
                {{ form.render('password_confirmation', ['class': 'form-control', 'placeholder': 'Password Confirmation']) }}
            </div>
            <div class="fields">
                <strong>Your information</strong>
                {{ form.render('name', ['class' : 'form-control', 'placeholder' : 'First Name']) }}
                {{ form.render('lname', ['class' : 'form-control', 'placeholder' : 'Last Name']) }}
                {{ form.render('mobileNumber', ['class' : 'form-control', 'placeholder' : 'Mobile Number']) }}
                {{ form.render('websiteUrl', ['class' : 'form-control', 'placeholder' : 'Your Website URL']) }}
                {{ form.render('companyName', ['class' : 'form-control', 'placeholder' : 'Your Company Name']) }}
                {{ form.render('companyCountry', ['class' : 'form-control', 'placeholder' : 'Your Company Country']) }}
                {{ form.render('companyCity', ['class' : 'form-control', 'placeholder' : 'Your Company City']) }}
                <div class="col-md-12 text-center ">
                    <span style="color: #9fa2a6; cursor: pointer;" data-toggle="modal" data-target="#emailModal">I can't see my region</span>
                </div>

                <div class="col-sm-12 no-pdd-left text-center mrg-top-10">
                        <a href="http://approvalbase.com/home-9-2/" target="_blank">Licence Agreement</a> & <a href="http://approvalbase.com/home-9-2-2/" target="_blank">Privacy Policy</a>
                </div>
                <div class="col-sm-12 no-pdd-left mrg-btm-20 text-center">
                    <label>
                        <input type="checkbox" name="iAgree" class="iAgree" value="iAgree"/>
                        I Agree
                    </label>
                </div>

            </div>

            <input type="hidden" name="solution" value="{{ solution }}">
            <div class="signup">
                {{ form.render('submit', ['class': 'btn btn-primary btn-lg signupBtn', 'value': 'Create my account']) }}
            </div>
            {{ endform() }}
        </div>

        <div class="bottom-wrapper">
            <div class="message" style="padding: 10px !important;">
                <span>Already have an account?</span>
                <a href="signin.html">Log in here</a>.
            </div>
        </div>
    </div>


<div class="modal fade" id="emailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="modalTitle">
                    Contact Us
                </h4>
            </div>
            <div class="modal-body">
                <p>support@approvalbase.com</p>
            </div>
        </div>
    </div>
</div>

    <script>
        $(function(){
          $preFilled = "{{ preFilledEmail }}";
          if($preFilled != ''){
            $('#email').val($preFilled);
          }
          $('.signupBtn').prop('disabled', true);
          $('.iAgree').click(function(){

            $isChecked = $(this).is(':checked');
            if($isChecked === true){
              $('.signupBtn').prop('disabled', false);

            }else{
              $('.signupBtn').prop('disabled', true);
            }

          });
        });
    </script>
{% endblock %}




