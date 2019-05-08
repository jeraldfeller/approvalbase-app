<!DOCTYPE html>
<html lang="en">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
        <title>{% if page_title is defined %}{{ page_title }}{% endif %}</title>
        <link rel="shortcut icon" href="{{ url("assets/images/logo/favicon.png") }}">

        {% block extra_css %}
        {% endblock %}



        {% include "_templates/_head.css.volt" %}
        {% include "_templates/_head.js.volt" %}


        {% block extra_js %}
        {% endblock %}


    </head>

    <body>

        <div>
            <!-- Side Navigation Menu -->
            <aside class="side-navigation-wrapper enter-right" data-no-scrollbar data-animation="push-in">
                <div class="side-navigation-scroll-pane">
                    <div class="side-navigation-inner">
                        <div class="side-navigation-header">
                            <div class="navigation-hide side-nav-hide">
                                <a href="#">
                                    <span class="icon-cancel medium"></span>
                                </a>
                            </div>
                        </div>
                        <nav class="side-navigation nav-block">
                            <ul>
                                <li class="current">
                                    <a href="{{ url('') }}" class="contains-sub-menu">Home</a>
                                </li>
                            </ul>
                        </nav>
                        <div class="side-navigation-footer">
                            <p class="copyright no-margin-bottom">&copy; 2017 Civilytica.</p>
                        </div>
                    </div>
                </div>
            </aside>
            <!-- Side Navigation Menu End -->
            <div class="wrapper reveal-side-navigation">
                <div class="wrapper-inner">

                    <!-- Header -->
                    <header class="header header-absolute header-fixed-on-mobile header-transparent" data-helper-in-threshold="1000" data-helper-out-threshold="1300" data-sticky-threshold="1000" data-bkg-threshold="900" data-compact-threshold="900">
                        <div class="header-inner">
                            <div class="row nav-bar">
                                <div class="column width-12 nav-bar-inner">
                                    <div class="logo">
                                        <div class="logo-inner">
                                            <a href="{{ url('') }}"><img src="{{ url("front-end/assets/images/logo-blue.png") }}" alt="Approval Base"/></a>
                                            <a href="{{ url('') }}"><img src="{{ url("front-end/assets/images/logo-white.png") }}" alt="Approval Base"/></a>
                                        </div>
                                    </div>
                                    <nav class="navigation nav-block primary-navigation nav-right sub-menu-indicator">
                                        <ul>
                                            {% if !user %}
                                                <li><a href="{{ url('pricing') }}">Pricing</a></li>
                                                <li><a href="{{ url('login') }}">Login</a></li>
                                                <li class="mrg-left-12">
                                                    <div class="v-align-middle">
                                                        <a href="{{ url('signup') }}" class="medium button rounded no-page-fade no-label-on-mobile no-margin-bottom ">
                                                            <span>Start Free Trial</span></a>
                                                    </div>
                                                </li>
                                            {% elseif user %}
                                                <li style="padding-right: 10px; ">Welcome back, {{ loggedInUser.getName() }} </li>
                                                <li>
                                                    <div class="v-align-middle">
                                                        <a class="button medium rounded no-page-fade no-label-on-mobile no-margin-bottom " href="{{ url('leads') }}"><span>Dashboard</span></a>
                                                    </div>
                                                </li>
                                            {% endif %}
                                            </li>
                                            <li class="aux-navigation hide">
                                                <!-- Aux Navigation -->
                                                <a href="#" class=" navigation-show side-nav-show nav-icon">
                                                    <span class="icon-menu"></span>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </header>
                    <!-- Header End -->

                    <!-- Content -->
                    {% block content  %}
                    {% endblock %}
                    <!-- Content End -->

                    <!-- Footer -->
                    <footer class="footer footer-light with-border">
                        <div class="footer-top">
                            <div class="row flex">
                                <div class="column width-9">
                                    <div class="row two-columns-on-tablet">
                                        <div class="column width-3 left center-on-mobile">
                                            <div class="widget">
                                                <h3 class="widget-title mb-30">Product</h3>
                                                <ul>
                                                    <li><a href="#">Careers</a></li>
                                                    <li><a href="#">Press</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="column width-3 left center-on-mobile">
                                            <div class="widget">
                                                <h3 class="widget-title mb-30">Company</h3>
                                                <ul>
                                                    <li><a href="#">Overview</a></li>
                                                    <li><a href="#">Features</a></li>
                                                    <li><a href="#">Pricing</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="column width-3 left center-on-mobile">
                                            <div class="widget">
                                                <h3 class="widget-title mb-30">Resources</h3>
                                                <ul>
                                                    <li><a href="#">FAQs</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="column width-3 left center-on-mobile">
                                            <div class="widget">
                                                <h3 class="widget-title mb-30">Contact</h3>
                                                <ul>
                                                    <li><a href="#">Email us</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="column width-3 right center-on-mobile">
                                    <div class="widget">
                                        <div class="footer-logo">
                                            <a href="{{ url('') }}"><img src="{{ url("front-end/assets/images/logo.png") }}" alt="Approval Base"/></a>
                                            <a href="{{ url('') }}"><img src="{{ url("front-end/assets/images/logo-white.png") }}" alt="Approval Base"/></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="footer-bottom center-on-mobile">
                            <div class="row">
                                <div class="column width-12">
                                    <div class="footer-bottom-inner">
                                        <p class="copyright pull-left clear-float-on-mobile">&copy; ApprovalBase. All Rights Reserved</p> <a href="#" class="scroll-to-top pull-right clear-on-mobile" data-no-hide>Back Top</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </footer>
                    <!-- Footer End -->

                </div>
            </div>
        </div>
    </body>
</html>
