{% extends "_templates/base_new.volt" %}
{% block content %}
    {#<div class="content clearfix">#}
        {#<!-- Feature 1 Section -->#}
        {#<div class="section-block feature-1 overlap-bottom pb-0 pt-0i section-home">#}
            {#<div class="section-block feature-1 overlap-bottom pb-0 bkg-gradient-royal-garden">#}
                {#<div class="row">#}
                    {#<div class="column width-8 offset-2">#}
                        {#<div class="feature-content">#}
                            {#<div class="feature-content-inner pt-140 pt-mobile-60 center">#}
                                {#<h1 class="mb-20 color-white">#}
                                    {#Build business with#}
                                    {#<img class="title-logo"#}
                                         {#src={{ url("front-end/assets/images/logo-white.png") }} alt="">#}
                                {#</h1>#}
                                {#<p class="lead mb-30 color-white">#}
                                    {#Access thousands of development application documents in search of new#}
                                    {#opportunities. Search for key phrases, create dynamic alerts and view project#}
                                    {#documents. Our platform allows you to view, save and export valuable market#}
                                    {#intelligence#}
                                {#</p>#}
                                {#{% if !user %}#}
                                {#<div class="text-center">#}
                                    {#<a href="{{ url('signup') }}"#}
                                       {#class="button medium rounded border-white bkg-hover-navy color-white color-hover-white mb-30"#}
                                       {#data-offset="150">#}
                                        {#Start Free Trial#}
                                    {#</a>#}
                                {#</div>#}
                                {#{% endif %}#}
                                {#<div class="type-wrap">#}
                                    {#<h2 class="color-white">#}
                                        {#<span id="typed3"></span>#}
                                    {#</h2>#}
                                {#</div>#}
                            {#</div>#}
                        {#</div>#}
                    {#</div>#}

                {#</div>#}
                {#<div class="row" style="max-width: 140rem;">#}
                    {#<div class="column width-12">#}
                        {#<div class="feature-image">#}
                            {#<div class="thumbnail rounded mb-0">#}
                                {#<img src={{ url("front-end/assets/images/Screenshot-Trio.png") }} width="1800" alt=""/>#}
                            {#</div>#}
                        {#</div>#}
                    {#</div>#}
                {#</div>#}
            {#</div>#}

        {#</div>#}
        {#<!-- Feature 1 Section End -->#}

        {#<!-- section-home -->#}
        {#<!-- Mobile Section -->#}
        {#<div class="section-block pb-0 bkg-ash">#}
            {#<div class="row flex">#}
                {#<div class="column width-5 push-6 mb-mobile-40 v-align-middle">#}
                    {#<div>#}
                        {#<h3 class="color-white">#}
                            {#Monitor#}
                        {#</h3>#}
                        {#<p class="color-white">#}
                            {#Monitor every development application in metropolitan Sydney in real-time. Create custom#}
                            {#phrases specific to your business and we'll alert you instantly when we find a new project#}
                            {#or application that fits your profile#}
                        {#</p>#}
                    {#</div>#}
                {#</div>#}
                {#<div class="column width-6 pull-5 v-align-middle center">#}
                    {#<div class="thumbnail mb-0 horizon" data-animate-in="preset:slideInLeftShort;duration:1000ms;">#}
                        {#<img src={{ url("front-end/assets/images/Laptop-Mockup.png") }} width="650" alt=""/>#}
                    {#</div>#}
                {#</div>#}
            {#</div>#}
        {#</div>#}
        {#<!-- Mobile Section End -->#}

        {#<!-- Mobile Section -->#}
        {#<div class="section-block bkg-ash">#}
            {#<div class="row flex">#}
                {#<div class="column width-5 offset-1 mb-mobile-40 v-align-middle">#}
                    {#<div>#}
                        {#<h3 class="color-white">#}
                            {#Discover#}
                        {#</h3>#}
                        {#<p>#}
                            {#Discover new construction projects, restaurants, bars, hotels, gyms, pharmacies, residential#}
                            {#developments, commercial developments, child-care centres, retail fit-outs, licensed venues,#}
                            {#fast-food chains and more#}
                        {#</p>#}
                    {#</div>#}
                {#</div>#}
                {#<div class="column width-6 v-align-middle center">#}
                    {#<div class="thumbnail mb-0 horizon" data-animate-in="preset:slideInRightShort;duration:1000ms;">#}
                        {#<img src={{ url("front-end/assets/images/Laptop-Mockup.png") }} width="650" alt=""/>#}
                    {#</div>#}
                {#</div>#}
            {#</div>#}
        {#</div>#}
        {#<!-- Mobile Section End -->#}

        {#<!-- Pricing/Sign Up Section -->#}
        {#<div class="section-block replicable-content bkg-grey-ultralight pricing-padding">#}
            {#<div class="row">#}
                {#<div class="column width-12">#}
                    {#<div class="row flex boxes two-columns-on-tablet flex-container-center">#}

                        {#<div class="column width-4 left">#}
                            {#<div class="pricing-table rounded large style-3 columns-1 mb-0 border-grey-light">#}
                                {#<div class="pricing-table-column">#}
                                    {#<div class="pricing-table-header bkg-gradient-royal-garden color-white">#}
                                        {#<h2 class="color-dark">Professional</h2>#}
                                    {#</div>#}
                                    {#<div class="pricing-table-text bkg-white">#}
                                        {#<div class="pricing-table-price">#}
                                            {#<h4>#}
                                                {#<span class="currency">$</span>999#}
                                                {#<span class="interval mt-10">/ month</span>#}
                                            {#</h4>#}
                                        {#</div>#}
                                        {#<ul class="pricing-list">#}
                                            {#<li>#}
                                                {#<img class="pricing-list-icon"#}
                                                     {#src={{ url("front-end/assets/images/list-check-icon.png") }} alt="">#}
                                                {#All inclusive#}
                                            {#</li>#}
                                            {#<li>#}
                                                {#<img class="pricing-list-icon"#}
                                                     {#src={{ url("front-end/assets/images/list-check-icon.png") }} alt="">#}
                                                {#1 User#}
                                            {#</li>#}
                                            {#<li>#}
                                                {#<img class="pricing-list-icon"#}
                                                     {#src={{ url("front-end/assets/images/list-check-icon.png") }} alt="">#}
                                                {#5 Phrases#}
                                            {#</li>#}
                                            {#<li>#}
                                                {#<img class="pricing-list-icon"#}
                                                     {#src={{ url("front-end/assets/images/list-check-icon.png") }} alt="">#}
                                                {#Unlimited Searches#}
                                            {#</li>#}
                                        {#</ul>#}
                                    {#</div>#}
                                    {#<div class="pricing-table-footer bkg-white pt-0i">#}
                                        {#<a href="#"#}
                                           {#class="button rounded bkg-grey-light bkg-hover-theme color-grey color-hover-white mb-mobile-40">Select#}
                                            {#Option</a>#}
                                    {#</div>#}
                                {#</div>#}
                            {#</div>#}
                        {#</div>#}
                        {#<div class="column width-4 left">#}
                            {#<div class="pricing-table rounded large style-3 columns-1 mb-0 border-grey-light">#}
                                {#<div class="pricing-table-column">#}
                                    {#<div class="pricing-table-header bkg-gradient-purple-haze color-white">#}
                                        {#<h2>#}
                                            {#Enterprise#}
                                        {#</h2>#}
                                    {#</div>#}
                                    {#<div class="pricing-table-text bkg-white">#}
                                        {#<div class="pricing-table-price">#}
                                            {#<h4>#}
                                                {#<span class="currency">$</span>1499#}
                                                {#<span class="interval mt-10">/ month</span>#}
                                            {#</h4>#}
                                        {#</div>#}
                                        {#<ul class="pricing-list">#}
                                            {#<li>#}
                                                {#<img class="pricing-list-icon"#}
                                                     {#src={{ url("front-end/assets/images/list-check-icon.png") }} alt="">#}
                                                {#All inclusive#}
                                            {#</li>#}
                                            {#<li>#}
                                                {#<img class="pricing-list-icon"#}
                                                     {#src={{ url("front-end/assets/images/list-check-icon.png") }} alt="">#}
                                                {#Unlimited User#}
                                            {#</li>#}
                                            {#<li>#}
                                                {#<img class="pricing-list-icon"#}
                                                     {#src={{ url("front-end/assets/images/list-check-icon.png") }} alt="">#}
                                                {#Unlimited Phrases#}
                                            {#</li>#}
                                            {#<li>#}
                                                {#<img class="pricing-list-icon"#}
                                                     {#src={{ url("front-end/assets/images/list-check-icon.png") }} alt="">#}
                                                {#Unlimited Searches#}
                                            {#</li>#}
                                        {#</ul>#}
                                    {#</div>#}
                                    {#<div class="pricing-table-footer bkg-white pt-0i">#}
                                        {#<a href="#"#}
                                           {#class="button rounded bkg-grey-light bkg-hover-theme color-grey color-hover-white mb-mobile-40">Select#}
                                            {#Option</a>#}
                                    {#</div>#}
                                {#</div>#}
                            {#</div>#}
                        {#</div>#}
                    {#</div>#}
                {#</div>#}
            {#</div>#}
        {#</div>#}
        {#<!-- Pricing/Sign Up Section End -->#}

        {#<!-- Mobile Section -->#}
        {#<div class="section-block pb-50 pt-50 bkg-white">#}
            {#<div class="row flex">#}
                {#<div class="column width-6 v-align-middle center">#}
                    {#<div class="mb-0">#}
                        {#<img src={{ url("front-end/assets/images/Team-Image.jpg") }} width="650" alt=""/>#}
                    {#</div>#}
                {#</div>#}
                {#<div class="column width-5 offset-1 mb-mobile-40 v-align-middle">#}
                    {#<div>#}
                        {#<h3 class="pt-20">#}
                            {#Teams#}
                        {#</h3>#}
                        {#<p class="color-dark">#}
                            {#We believe in teamwork. Our next version of ApprovalBase will focus on creating features#}
                            {#that help teams collaborate and exchange ideas. If you have an idea about new features or#}
                            {#functionality, feel free to touch base with us and make a suggestion#}
                        {#</p>#}

                        {#<a href="#0"#}
                           {#class="button rounded bkg-grey-light bkg-hover-theme color-grey color-hover-white mb-mobile-40">Contact#}
                            {#Us</a>#}
                    {#</div>#}
                {#</div>#}
            {#</div>#}
        {#</div>#}
        {#<!-- Mobile Section End -->#}



        {#<!-- Signup Advanced Modal End -->#}
        {#<div id="login-modal" class="section-block pt-0 pb-30 background-none hide">#}

            {#<!-- Signup -->#}
            {#<div class="section-block pt-80 pb-0">#}
                {#<div class="row">#}
                    {#<div class="column width-12">#}
                        {#<div class="modal-dialog-inner left">#}
                            {#<div class="form-container">#}
                                {#<div class="row">#}
                                    {#<div class="column width-12">#}
                                        {#<h3>Sign in to continue</h3>#}
                                        {#<p class="mb-20">Need a client account? <a href="index-register.html"#}
                                                                                   {#class="fade-location">Click Here</a>#}
                                        {#</p>#}
                                        {#<div class="login-form-container">#}
                                            {#<form class="login-form" action="#" method="post" novalidate>#}
                                                {#<div class="row">#}
                                                    {#<div class="column width-12">#}
                                                        {#<div class="field-wrapper">#}
                                                            {#<label class="color-charcoal">Username/Email:</label>#}
                                                            {#<input type="text" name="login[username]"#}
                                                                   {#class="form-username form-element rounded medium"#}
                                                                   {#placeholder="JohnDoe" required>#}
                                                        {#</div>#}
                                                    {#</div>#}
                                                    {#<div class="column width-12">#}
                                                        {#<div class="field-wrapper">#}
                                                            {#<label class="color-charcoal">Password:</label>#}
                                                            {#<input type="password" name="login[password]"#}
                                                                   {#class="form-password form-element rounded medium"#}
                                                                   {#placeholder="••••••••" required>#}
                                                        {#</div>#}
                                                    {#</div>#}
                                                    {#<div class="column width-12">#}
                                                        {#<div class="field-wrapper pt-0 pb-20">#}
                                                            {#<input id="checkbox-1" class="form-element checkbox rounded"#}
                                                                   {#name="login[checkbox-1]" type="checkbox" required>#}
                                                            {#<label for="checkbox-1" class="checkbox-label no-margins">Remember#}
                                                                {#Me</label>#}
                                                        {#</div>#}
                                                    {#</div>#}
                                                    {#<div class="column width-12">#}
                                                        {#<input type="submit" value="Sign In"#}
                                                               {#class="form-submit button rounded medium bkg-green bkg-hover-theme bkg-focus-green color-white color-hover-white mb-0">#}
                                                    {#</div>#}
                                                {#</div>#}
                                            {#</form>#}
                                            {#<p class="text-small mt-20">I forgot my password - <a href="#">Remind me</a>#}
                                            {#</p>#}
                                            {#<div class="form-response show"></div>#}
                                        {#</div>#}
                                    {#</div>#}
                                {#</div>#}
                            {#</div>#}
                        {#</div>#}
                    {#</div>#}
                {#</div>#}
            {#</div>#}
            {#<!-- Signup End -->#}

        {#</div>#}
        {#<!-- Signup Advanced Modal End -->#}

    {#</div>#}
    {#<!-- Content End -->#}

    {#<script src={{ url("front-end/assets/js/typed.js") }}></script>#}
    {#<script>#}
      {#var typed3 = new Typed('#typed3', {#}
        {#strings: ['We’ve collected over 6,000 documents this week', 'Find new projects and offer your trade', 'Find new projects and offer your product', 'Find new projects and offer your service', 'Search demolition', 'Search pool', 'Search restaurant', 'Search anything', 'We’ve collected over 6,000 documents this week'],#}
        {#typeSpeed: 80,#}
        {#backSpeed: 30,#}
        {#smartBackspace: true, // this is a default#}
        {#loop: false#}
      {#});#}
    {#</script>#}
{% endblock %}






