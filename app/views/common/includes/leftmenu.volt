<div class="side-nav">
    <div class="side-nav-inner">
        <div class="side-nav-logo">
            <a href="index.html">
                <div class="logo logo-dark" style="background-image: url('assets/images/logo/logo.png')"></div>
                <div class="logo logo-white" style="background-image: url('assets/images/logo/logo-white.png')"></div>
            </a>
            <div class="mobile-toggle side-nav-toggle">
                <a href="">
                    <i class="ti-arrow-circle-left"></i>
                </a>
            </div>
        </div>
        <ul class="side-nav-menu scrollable ps-container ps-theme-default ps-active-y">

            {% include "includes/leftmenu-user.volt" %}

            {% if loggedInUser.isAdministrator() %}
                {% include "admin/_includes/leftmenu.volt" %}
            {% endif %}

        </ul>
    </div>
</div>
