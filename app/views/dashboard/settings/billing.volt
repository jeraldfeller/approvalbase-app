<div id="panel" class="billing">
    <h3>
        {{ page_title }}
    </h3>

    {% if user['subscriptionStatus'] == 'trial' OR  user['subscriptionStatus'] == 'expired'  OR  user['subscriptionStatus'] == 'canceled'  %}
        <div class="row">
            <div class="col-md-12">
                <div id="pricing">
                    <div class="pricing-wizard">
                        <div class="step-panel active choose-plan">
                            <div class="instructions">
                                <strong>Please select Purchase below</strong> to continue using ApprovalBase. Pay monthly, cancel anytime.
                            </div>

                            <div class="plans">
                                <div class="plan clearfix selected">
                                    <div class="price">
                                        $499/mo
                                    </div>
                                    <div class="info">
                                        <div class="name">
                                            Standard
                                        </div>
                                        <div class="details">
                                            Unlimited
                                        </div>
                                        <div class="select">
                                            <i class="fa fa-check"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <button id="customButton" class="btn btn-primary">Purchase</button>
            </div>
        </div>
    {% endif %}
    {% if user['subscriptionStatus'] == 'active' %}
        <div class="plan">

            <div class="current-plan">
                <div class="field">
                    <label>Plan:</label> Subscription (${{ current['amount'] }}/month)
                </div>
                <div class="field">
                    <label>Date:</label> {{ current['startDate'] }} to {{ current['endDate'] }}
                </div>
                {% set statusClass = (current['status'] == 'Active' ? 'status' : 'status-danger') %}
                <div class="field {{ statusClass }}">
                    <label>Status:</label> <span class="value">{{ current['status'] }}</span>
                </div>
            </div>


            <!-- <a class="btn btn-danger suspend-sub" href="#">Suspend my subscription</a> -->
            <div id="invoice">
                <div class="invoice-wrapper no-pdd-left">
                    <h3>Invoice</h3>
                    {% for row in invoices %}
                    <div class="payment-info odd">
                        <div class="row">
                            <div class="col-sm-6 ">
                                <span>Payment Date</span>
                                <strong>{{ row['paymentDate'] }}</strong>
                            </div>
                            <div class="col-sm-6 text-right">
                                <span>Invoice No.</span>
                                <strong>{{ row['chargeId'] }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="payment-details">
                        <div class="row">
                            <div class="col-sm-6">
                                <span>Payment From</span>
                                <strong>
                                    {{ row['firstName'] }} {{ row['lastName'] }}
                                </strong>
                            </div>
                            <div class="col-sm-6 text-right">
                                <span>Payment To</span>
                                <strong style="font-size: 15px; display: inline-block; color: #333; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; margin: 0; padding: 0 0 8px;">ApprovalBase</strong>
                                <div style="color: #222; line-height: 19px; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; margin: 0; padding: 0;">
                                    1 Bligh Street
                                    <br style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; margin: 0; padding: 0;" />
                                    Sydney NSW 2000
                                    Austrailia
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="line-items">
                        <div class="headers clearfix">
                            <div class="row">
                                <div class="col-xs-6">Description</div>
                                <div class="col-xs-6 text-right">Unit Price</div>
                            </div>
                        </div>
                        <div class="items">
                            <div class="row item">
                                <div class="col-xs-6 desc">
                                    ApprovalBase Monthly Subscription
                                </div>
                                <div class="col-xs-6 amount text-right">
                                   ${{ row['amount'] }}
                                </div>
                            </div>
                        </div>
                        <div class="total text-right">
                            <div class="field grand-total">
                                Total <span>${{ row['amount'] }}</span>
                            </div>
                        </div>

                    </div>
                    {% endfor %}
                </div>
            </div>


        </div>
        <div class="col-md-12 no-pdd-left">
            <button id="cancelButton" class="btn btn-danger">Cancel Subscription</button>
        </div>
        <div class="col-md-12 no-pdd-left mrg-top-20">
            If you have any questions, please contact us at <a href="mailto:support@approvalbase.com" class="">support@approvalbase.com</a>
        </div>
    {% endif %}

</div>

{% include "settings/_billingJs.volt" %}