<!doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{% if page_title is defined %}{{ page_title }}{% endif %}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Place favicon.ico and apple-touch-icon(s) in the root directory -->
    <link rel="shortcut icon" href="images/favicon.ico">


    <link rel="shortcut icon" href="{{ url("assets/images/logo/favicon.png") }}">

    {% block extra_css %}
    {% endblock %}
    {% include "_templates/_head-new.css.volt" %}
    {% include "_templates/_head-new.js.volt" %}
    {% block extra_js %}
    {% endblock %}


</head>
<body>
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<!--[if lt IE 8]>
<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade
    your browser</a> to improve your experience.</p>
<![endif]-->

<nav class="navbar navbar-expand-lg navbar-dark bg-transparent" role="navigation">
    <div class="container no-override">
        <a class="navbar-brand" href="#">
            <img src="{{ url("front-end/assets/images/logo-white.png") }}" class="d-none d-lg-inline mr-2 w-38"/>
            <span class="d-md-none">ApprovalBase</span>
        </a>

        <button class="navbar-toggler" data-toggle="collapse" data-target="#navbar-collapse">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbar-collapse">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="#"  class="nav-link scroll-to-pricing">
                        Pricing
                    </a>
                </li>
                {% if !user %}
                <li class="nav-item">
                    <a href="{{ url('login') }}" class="nav-link">
                        Login
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link--rounded" href="{{ url('signup') }}">Start Free Trial</a>
                </li>
                {% elseif user %}
                    <li class="nav-item">
                        <a href="#" class="nav-link">Welcome back, {{ loggedInUser.getName() }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link--rounded" href="{{ url('dashboard') }}">Dashboard</a>
                    </li>
                {% endif %}
            </ul>
        </div>
    </div>
</nav>

<div class="index-header">
    <section class="container">
        <div class="row">
            <div class="col-lg-7 col-md-12 col-sm-12">
                <h1 style="font-size: 36px;">
                    Find Projects. Grow Business.
                    {#<img style="margin-top: -.5%;" class="d-lg-inline mr-2 w-41" src={{ url("front-end/assets/images/ab_logos/approvalbase-final-white_high_res-01-01.png") }} alt="">#}
                </h1>
                <p>
                    Access thousands of development application documents in search of new
                    opportunities to sell your product or service. Create custom phrase-based
                    alerts, receive automated email notifications and view project details and
                    documents. Our platform gives you the information you need to drive growth.
                </p>

                <div class="cta">
                    <a href="{{ url('signup') }}" class="btn btn-primary btn-lg">
                        Free trial
                    </a>
                    <a class="btn-outline popup-media" href="https://vimeo.com/155404383">
                        <i class="fa fa-play"></i>
                        See it in action
                    </a>
                </div>
            </div>
            <div class="col-md-5 macbook-container">
                <img style="margin-top: -1%;" src="{{ url('front-end/assets/images/laptop-smartmockups_jtjccwjm.png') }}"
                     class="macbook-pic fadeInScale d-none d-md-block"/>
            </div>
        </div>
    </section>
</div>

<div class="store-testimonials">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="quote clearfix">
                    <span class="quote-mark">&#8220;</span>
                    <p>
                        The platform allowed us to double our sales pipeline
                        in 6 months. We are now working across the whole
                        metro area. Thanks!
                    </p>
                    <div class="author clearfix">
                        <img src="{{ url('spacial/images/uifaces/1.jpg') }}"/>
                        <div class="name"><strong>Joel N.</strong></div>
                        <div class="star-rating"><img src="{{ url('front-end/assets/images/5-stars.png') }}"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="quote clearfix">
                    <span class="quote-mark">&#8220;</span>
                    <p>
                        This software is powerful! It has
                        already paid itself off and we are
                        constantly amazed by the
                        automated alerts. Love it!
                    </p>
                    <div class="author clearfix">
                        <img src="{{ url('spacial/images/uifaces/3.jpg') }}"/>
                        <div class="name"><strong>David R. </strong></div>
                        <div class="star-rating"><img src="{{ url('front-end/assets/images/5-stars.png') }}"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="quote clearfix">
                    <span class="quote-mark">&#8220;</span>
                    <p>
                        We can’t imagine working without
                        ApprovalBase. No training
                        required and well worth the
                        investment. Highly recommended
                    </p>
                    <div class="author clearfix">
                        <img src="{{ url('spacial/images/uifaces/4.jpg') }}"/>
                        <div class="name"><strong>George E. </strong></div>
                        <div class="star-rating"><img src="{{ url('front-end/assets/images/5-stars.png') }}"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="index-mobile-features" >
    <div id="join">
        <div class="container" >
            <header style="display: none;">
                <h3>
                    Join ApprovalBase in under 5 minutes.
                </h3>
                <div class="type-wrap">
                    <p class="color-white">
                        <span id="typed3"></span>
                    </p>
                </div>
            </header>
            <div  class="row progress-container">
                <div class="col-md-3 col-sm-12 bulleted highlighted">
                    <div class="icon-wrapper wow fadeInUp" data-wow-delay="0.1s"
                         style="visibility: visible; animation-delay: 0.1s;"><i class="icon icon-profile-male"></i></div>
                    <p class="text-center wow fadeIn" data-wow-delay="0.1s"
                       style="visibility: visible; animation-delay: 0.1s;">Create an account</p></div>
                <div class="col-md-3 col-sm-12 bulleted">
                    <div class="icon-wrapper wow fadeInUp" data-wow-delay="0.2s"
                         style="visibility: visible; animation-delay: 0.2s;"><i class="icon icon-adjustments"></i></div>
                    <p class="text-center wow fadeIn" data-wow-delay="0.2s"
                       style="visibility: visible; animation-delay: 0.2s;">Add search phrases</p></div>
                <div class="col-md-3 col-sm-12 bulleted">
                    <div class="icon-wrapper wow fadeInUp" data-wow-delay="0.3s"
                         style="visibility: visible; animation-delay: 0.3s;"><i class="icon icon-search"></i></div>
                    <p class="text-center wow fadeIn" data-wow-delay="0.3s"
                       style="visibility: visible; animation-delay: 0.3s;">Explore results</p></div>
                <div class="col-md-3 col-sm-12 bulleted">
                    <div class="icon-wrapper wow fadeInUp" data-wow-delay="0.4s"
                         style="visibility: visible; animation-delay: 0.4s;"><i class="icon icon-target"></i></div>
                    <p class="text-center wow fadeIn" data-wow-delay="0.4s"
                       style="visibility: visible; animation-delay: 0.4s;">Win new business</p></div>
            </div>
        </div>
    </div>
</div>


<div id="pricing" class="pricing-plans" style="margin-top: 0;">
        <div class="container">
            <header style="padding: 20px 0 50px;">
                <h3>
                    Get started for free. Cancel anytime
                </h3>
            </header>
            <div class="wrapper">
                <div class="plans">
                    <div class="plan clearfix">
                        <div class="name">Free Trial</div>
                        <div class="users">14 days</div>
                        <div class="price">
                            <div class="price">
                                &nbsp;
                            </div>
                        </div>
                        <div class="choose">
                            <a href="{{ url('signup') }}">GET STARTED</a>
                        </div>
                    </div>
                    <div class="plan clearfix popular">
                        <div class="name">Business</div>
                        <div class="users">1 User</div>
                        <div class="price">
                            $1,250/mo
                        </div>
                        <div class="choose">
                            <a href="{{ url('signup') }}" class="btn btn-primary btn-lg btn-cta btn-shadow">GET STARTED</a>
                        </div>
                        <section class="plan-details">
                            <span class="flag">Most popular</span>

                        </section>
                    </div>
                    <div class="plan clearfix enterprise">
                        <div class="name">Enterprise</div>
                        <div class="users"><i class="ion ion-ios-infinite"></i> Users</div>
                        <div class="price">
                            $2,500/mo
                        </div>
                        <div class="choose">
                            <a href="{{ url('signup') }}">GET STARTED</a>
                        </div>

                    </div>

                </div>

            </div>
        </div>
</div>

<div class="customer-testimonial">
    <div class="container">
        <div class="row">
            <div class=" col-md-2 col-sm-2 col-xs-2 customer-icon-container" style="display: table; min-height: 100px;">
                <i style="font-size: 3.7em; display: table-cell; vertical-align: middle; text-align: center;" class="icon icon-profile-male"></i>
            </div>
            <div class="col-lg-10 col-md-9 col-sm-8 col-xs-10" style="font-family: inherit;">
                <p>
                    <span>“</span>
                    ApprovalBase is the perfect solution for anyone trying to find new business. The platform has
                    allowed
                    Hydratapp to connect with architects and builders who are in the early planning stages of new
                    projects.
                    This has enabled our sales team to review building plans and start a conversation with potential
                    clients
                    from an informed position. AB has become an integral part of our sales
                </p>
                <div class="author">
                    <strong>Alex B.</strong>
                    <div class="star-rating"><img src="{{ url('front-end/assets/images/5-stars.png') }}"></div>
                </div>

            </div>
        </div>
    </div>
</div>


<footer class="footer-big-menu" id="footer" style="margin-top: 0px;">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="cta text-center">
                    <h1 class="cta-title">
                        Ready to start searching? Start your free trial
                    </h1>
                    <a class="btn-shadow btn-shadow-info mr-md-1" href="{{ url('signup') }}">
                        Free Trial
                    </a>
                    <a class="btn-outline popup-media" href="https://vimeo.com/155404383">
                        <i class="fa fa-play"></i>
                        See it in action
                    </a>
                </div>
            </div>
        </div>
    </div>
    <span class="bottom">(c) 2019 ApprovalBase Inc. All rights reserved. <a href="#">support@approvalbase.com</a></span>
</footer>
<script src={{ url("front-end/assets/js/typed.js") }}></script>
<script>
  var typed3 = new Typed('#typed3', {
    strings: ['Search Restaurant', 'Search Bar', 'Search Hotel', 'Search Gym', 'Search Pharmacy', 'Search Residential', 'Search Commercial', 'Search Child care', 'Search Retail', 'Search Licensed venue', 'Search Pool', 'Search Demolition', 'Search Asbestos', 'and more'],
    typeSpeed: 80,
    backSpeed: 30,
    smartBackspace: true, // this is a default
    loop: false
  });
</script>

<script type="text/javascript">
  $(function () {
    $('.popup-media').magnificPopup({
      type: 'iframe',
      mainClass: 'mfp-fade'
    });

    $('.scroll-to-pricing').click(function(){
      $('html, body').animate({
        scrollTop: $("#pricing").offset().top
      }, 300);
    });
  })
</script>

<!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->
<script>
  // (function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]=
  // function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;
  // e=o.createElement(i);r=o.getElementsByTagName(i)[0];
  // e.src='//www.google-analytics.com/analytics.js';
  // r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));
  // ga('create','UA-XXXXX-X','auto');ga('send','pageview');
</script>
</body>
</html>