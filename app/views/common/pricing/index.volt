{% extends "_templates/base.volt" %}
{% block content %}
<div class="content clearfix">
    <div class="section-block intro-title-1 xsmall">
        <div class="row">
            <div class="column width-12">
                <div class="title-container">
                    <div class="title-container-inner">
                        <div class="row flex">
                            <div class="column width-6 v-align-middle">
                                <div>
                                    <h1 class="mb-0">Pricing</h1>
                                    <p class="lead mb-0 mb-mobile-20"></p>
                                </div>
                            </div>
                            <div class="column width-6 v-align-middle">
                                <div>
                                    <ul class="breadcrumb inline-block mb-0 pull-right clear-float-on-mobile">
                                        <li>
                                            <a href="{{ url('') }}">Home</a>
                                        </li>
                                        <li>
                                            Pricing
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="section-block bkg-grey-ultralight">
        <div class="row flex boxes two-columns-on-tablet">
            <div class="column width-3 left"></div>
            <div class="column width-6 left">
                <div class="pricing-table rounded large style-1 columns-1 mb-0 border-grey-light">
                    <div class="pricing-table-column">
                        <div class="pricing-table-header bkg-white color-charcoal">
                            <h2 class="weight-semi-bold pull-left color-charcoal">Premium</h2>
                            <div class="pricing-table-price pull-right">
                                <h4>
                                    <span class="currency"></span>FREE
                                    <span class="interval mt-10">BETA</span>
                                </h4>
                            </div>
                        </div>
                        <hr class="no-margins">
                        <div class="pricing-table-text bkg-white">
                            <p>Full access. No credit card required.</p>
                            <ul class="mb-0">
                                <li>All inclusive</li>
                                <li>Unlimited users in your company</li>
                                <li>Unlimted phrases</li>
                                <li>Unlimted searches</li>
                            </ul>
                        </div>
                        <div class="pricing-table-footer bkg-white">
                            <a href="{{ url('signup') }}" class="button rounded bkg-blue bkg-hover-blue color-white color-hover-white mb-mobile-40">Start Free Trial</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
  
{% endblock %}






