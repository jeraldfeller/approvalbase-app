{% extends "_templates/base.volt" %}
{% block extra_css %}
    <link rel="stylesheet" type="text/css"
          href="{{ url('dashboard_assets/css/vendor/bootstrap-daterangepicker.css') }}"/>

    <link rel="stylesheet" type="text/css" href="{{ url('dashboard_assets/css/vendor/select2.css?v=1.2') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ url('dashboard_assets/css/vendor/select2-bootstrap.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ url('dashboard_assets/css/vendor/shepherd.css') }}"/>


{% endblock %}
{% block extra_js %}

{% endblock %}
{% block content %}
    <div id="content">
        <div class="menubar sticky">
            <div class="sidebar-toggler visible-xs">
                <i class="ion-navicon"></i>
            </div>
            {% if page_title is defined %}
                <div class="page-title display-none">
                    <h4>{{ page_title }}</h4>
                </div>
            {% endif %}

            {% include "includes/user-dropdown.volt" %}
        </div>
        <div id="datatables">
            <div class="content-wrapper">
                <div class="row mrg-btm-20 no-pdd-left no-margin-left search-area">

                    <div class="col-sm-12">
                        <div class="input-group">
                            <input type="text" class="form-control searchFilter" id="searchFilter" placeholder="Search">
                            <span class="input-group-addon btn btn-primary" data-toggle="modal" data-target="#filterModal"><i class="fa fa-sliders"></i> Filters</span>
                        </div>
                    </div>

                    <!-- Modals -->

                    <div class="modal fade" id="filterModal" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title" id="modalTitle">
                                        <i class="fa fa-sliders"></i> Filters
                                    </h4>
                                </div>
                                <div class="modal-body">
                                   <div class="row">
                                       <div class="col-sm-12 mrg-btm-20">
                                           <input type="text" class="form-control searchFilter" id="searchFilterModal" placeholder="Search">
                                       </div>
                                       <div class=" col-md-12 col-sm-12 col-xs-12 council-dropdown step-councils mrg-btm-20">
                                           <select id="councils" name="councils" multiple class="item-info select2 display-none ">
                                               {% for row in councils %}
                                                   <option value="{{ row.getId() }}">{{ row.getName() }}</option>
                                               {% endfor %}
                                           </select>
                                       </div>
                                       <div class="col-md-6  col-sm-12 col-xs-12 cost-range pull-left step-cost mrg-top-18">
                                           <div class="form-group search">
                                               <div class="col-sm-12 col-md-12 no-mrg-left no-mrg-right no-pdd-left no-pdd-right text-center">
                                                   <div class="col-md-5 no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
                                                       <select id="cost-from" class="form-control cost-select" data-smart-select>
                                                           <option value="0">Min($)</option>
                                                           <option value="250000">$250,000</option>
                                                           <option value="1000000">$1,000,000</option>
                                                           <option value="3000000">$3,000,000</option>
                                                           <option value="5000000">$5,000,000</option>
                                                           <option value="10000000">$10,000,000</option>
                                                           <option value="15000000">$15,000,000</option>
                                                           <option value="25000000">$25,000,000</option>
                                                           <option value="50000000">$50,000,000+</option>
                                                       </select>
                                                   </div>
                                                   <div class="col-md-1 no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
                                                       <span class="separator lh-2-5">-</span>
                                                   </div>
                                                   <div class="col-md-5 no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
                                                       <select id="cost-to" class="form-control cost-select" data-smart-select>
                                                           <option value="50000000">Max($)</option>
                                                           <option value="250000">$250,000</option>
                                                           <option value="1000000">$1,000,000</option>
                                                           <option value="3000000">$3,000,000</option>
                                                           <option value="5000000">$5,000,000</option>
                                                           <option value="10000000">$10,000,000</option>
                                                           <option value="15000000">$15,000,000</option>
                                                           <option value="25000000">$25,000,000</option>
                                                           <option value="50000000">$50,000,000+</option>
                                                       </select>
                                                   </div>
                                               </div>
                                           </div>
                                       </div>

                                       <div class="col-md-6 col-sm-12 col-xs-12 date-range-picker mrg-top-20 ">
                                           <div class="date-range">
                                               <div class="input-group input-group-sm step-date">
					  	<span class="input-group-addon">
					  		<i class="fa fa-calendar-o"></i>
					  	</span>
                                                   <input type="text" id="date-range-picker" class="form-control"
                                                          placeholder="{{ defaultDateRange[0] }} - {{ defaultDateRange[1] }}"/>
                                               </div>
                                           </div>
                                       </div>
                                   </div>

                                    <div class="row mrg-top-20">
                                        <div class="col-md-3 ">
                                            <div class="checkbox no-pdd-left display-inline-block">
                                                <input type="checkbox" class="checkbox-filter" id="input_metadata" name="input_case_sensitive">
                                                <label for="input_metadata">Metadata</label>
                                            </div>
                                            <div class="display-inline-block" data-toggle="tooltip" data-html="true" data-placement="bottom" title="includes applications with no PDF attachments"><i class="fa fa-question-circle"></i></div>
                                        </div>

                                        <div class="col-md-3 ">
                                            <div class="checkbox no-pdd-left">
                                                <input type="checkbox" class="checkbox-filter" id="input_case_sensitive" name="input_case_sensitive">
                                                <label for="input_case_sensitive">Case sensitive</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 ">
                                            <div class="checkbox display-inline-block no-pdd-left">
                                                <input type="checkbox" class="checkbox-filter" id="input_literal_search" name="input_literal_search">
                                                <label for="input_literal_search">Literal search</label>
                                            </div>
                                            <div class="display-inline-block" data-toggle="tooltip" data-html="true" data-placement="bottom" title="A phrase with Literal Search enabled doesn't allow
                                        the phrase to be found within other words."><i class="fa fa-question-circle"></i></div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="checkbox no-mrg-left no-pdd-left display-inline-block">
                                                <input type="checkbox" class="checkbox-filter" id="input_exclude_phrase" name="input_exclude_phrase">
                                                <label for="input_exclude_phrase">Exclude phrase</label>
                                            </div>
                                            <div class="display-inline-block" data-toggle="tooltip" data-html="true" data-placement="bottom" title="Disqualifies a DA from being added to your inbox if
                                        this phrase is found."><i class="fa fa-question-circle"></i></div>
                                        </div>
                                    </div>
                                    <div class="row mrg-top-20">
                                        <div class="col-md-1 pull-right ">
                                            <button class="btn btn-primary pull-right refineSearchBtn">Search</button>
                                        </div>
                                        <div class="col-md-1 pull-right">
                                            <button class="btn btn-default pull-right clearBtn">Clear</button>
                                        </div>
                                        <div class="col-md-2 pull-right">
                                            <button class="btn btn-primary pull-right saveCreateAlert">Save Filter & Create Alert</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="modal fade" id="shareModal" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title" id="modalTitle">
                                        <i class="fa fa-share-alt"></i> Share
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div id="shareContainer">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <div class="row">
                                        <div class="col-md-12">
                                        <button class="btn btn-primary pull-right" id="sendShareBtn">Share</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="ui">


                        <div class="modal fade welcome-modal" id="welcome-modal-first" tabindex="-1" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <header>
                                            Welcome to ApprovalBase
                                        </header>

                                        <p>
                                            Thanks for signing up! We recommend that you take the Guided Tour so we can show you around and get you set up.
                                        </p>

                                        <div class="get-started">
                                            <div class="row">
                                                <div class="col-md-4 animated fadeInDown">
                                                    <i class="ion-navigate"></i>
                                                    <a href="#" id="guided_tour_cta">Guided Tour</a>
                                                </div>
                                                <div class="col-md-4 animated fadeInDown two">
                                                    <i class="ion-wrench"></i>
                                                    <a href="/filters">Set up filters</a>
                                                </div>
                                                <div class="col-md-4 animated fadeInDown three">
                                                    <i class="ion-help-circled"></i>
                                                    <a href="https://player.vimeo.com/video/334581219?autoplay=1&api=1&player_id=html5boxiframevideo0" onclick="window.open(this.href, 'Approvalbase',
'right=20,top=20,width=500,height=500,toolbar=1,resizable=0'); return false;">Watch our tutorial </a>
                                                </div>
                                            </div>
                                        </div>

                                        <a href="#" id="skip_cta" data-dismiss="modal" class="go-dashboard">
                                            Skip and take me to the Search page
                                            <i class="fa fa-angle-double-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="modal fade welcome-modal" id="welcome-modal" tabindex="-1" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <header>
                                            Start searching!
                                        </header>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- end modals -->

                </div>

                {% include "includes/flashMessages.volt" %}



                {#Context Menu#}
                <div id="context-menu">
                    <ul class="dropdown-menu pull-left" role="menu">
                        <li onclick="openLink(this)" class="sendTo" data-action="_blank">
                            <a href="javascript:;">
                                <span>Open link in new tab</span>
                            </a>
                        </li>
                        <li onclick="openLink(this)" class="sendTo" data-action="">
                            <a href="javascript:;">
                                <span>Open link in new window</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- end context menu -->

                <div class="row overflow-x-scroll">
                    {# Search Table #}
                    <table id="dt-opt" class="table-hover">
                        <thead>
                        <tr>
                            <th></th>
                            <th>Council</th>
                            <th>Uploaded</th>
                            <th>Construction Value</th>
                            <th>Description</th>
                        </tr>
                        </thead>
                        <tbody class="tbody">
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>
    {% include "search/_searchJs.volt" %}
    {% include "_helpers/_shareDaJs.volt" %}
{% endblock %}
