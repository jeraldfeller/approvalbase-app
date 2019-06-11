{% extends "_templates/base.volt" %}
{% block extra_css %}
    <link rel="stylesheet" type="text/css" href="{{ url('dashboard_assets/css/vendor/shepherd.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ url('dashboard_assets/css/vendor/select2.css?v=1.2') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ url('dashboard_assets/css/vendor/select2-bootstrap.css') }}"/>
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
                {% include "includes/flashMessages.volt" %}
                <div class="row mrg-btm-20">
                    <div class="col-sm-12">
                        <div class="">
                            <input type="text" class="form-control searchFilter" id="searchFilter" placeholder="Search">
                            {#<span class="input-group-addon btn btn-primary" data-toggle="modal" data-target="#filterModal"><i class="fa fa-sliders"></i> Filters</span>#}
                        </div>
                    </div>
                    <div class="col-sm-12 mrg-top-20">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#createFilterModal">
                            Create New Alert Filter
                        </button>
                    </div>


                    <div class="modal fade" id="filterModal" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-md">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                        &times;
                                    </button>
                                    <h4 class="modal-title" id="modalTitle">
                                        <i class="fa fa-sliders"></i> Filters
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <input type="text" id="filter_input_phrase" name="input_phrase"
                                                       placeholder="Enter Phrase" class="form-control step-phrase"
                                                       required>
                                            </div>
                                        </div>
                                        {#<div class=" col-md-2 col-sm-12 col-xs-12  step-filter">#}
                                        {#<select id="filter1" class="select2 item-info display-none" name="filter1"#}
                                        {#multiple>#}
                                        {#<option value="applicant">Applicant</option>#}
                                        {#<option value="description">Description</option>#}
                                        {#</select>#}
                                        {#</div>#}
                                        <div class=" col-md-12 col-sm-12 col-xs-12 council-dropdown step-councils">
                                            <select id="filter_councils" name="councils" multiple class="item-info select2 display-none ">
                                                {% for row in councils %}
                                                    <option value="{{ row.getId() }}">{{ row.getName() }}</option>
                                                {% endfor %}
                                            </select>
                                        </div>

                                        <div class="col-md-12  col-sm-12 col-xs-12 cost-range pull-left step-cost mrg-top-18">
                                            <div class="form-group search">
                                                <div class="col-sm-12 col-md-12 no-mrg-left no-mrg-right no-pdd-left no-pdd-right text-center">
                                                    <div class="col-md-5 no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
                                                        <select id="filter_cost-from" class="form-control cost-select" data-smart-select>
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
                                                    <div class="col-md-2 no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
                                                        <span class="separator lh-2-5">-</span>
                                                    </div>
                                                    <div class="col-md-5 no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
                                                        <select id="filter_cost-to" class="form-control cost-select" data-smart-select>
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
                                    </div>
                                    <div class="row mrg-top-20">
                                        <div class="col-md-12 ">
                                            <div class="checkbox no-pdd-left display-inline-block">
                                                <input type="checkbox" class="checkbox-filter" id="filter_input_metadata" name="input_case_sensitive">
                                                <label for="input_metadata">Hide no-value projects</label>
                                            </div>
                                            <div class="display-inline-block" data-toggle="tooltip" data-html="true" data-placement="bottom" title="excludes projects with $0 construction value"><i class="fa fa-question-circle"></i></div>
                                        </div>

                                        <div class="col-md-12 ">
                                            <div class="checkbox no-pdd-left">
                                                <input type="checkbox" class="checkbox-filter" id="filter_input_search_addresses" name="input_search_addresses">
                                                <label for="input_case_sensitive">Search Addresses</label>
                                            </div>
                                        </div>
                                        <div class="col-md-12 ">
                                            <div class="checkbox display-inline-block no-pdd-left">
                                                <input type="checkbox" class="checkbox-filter" id="filter_input_literal_search" name="input_literal_search">
                                                <label for="input_literal_search">Literal search</label>
                                            </div>
                                            <div class="display-inline-block" data-toggle="tooltip" data-html="true" data-placement="bottom" title="A phrase with Literal Search enabled doesn't allow
                                        the phrase to be found within other words."><i class="fa fa-question-circle"></i></div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="checkbox no-mrg-left no-pdd-left display-inline-block">
                                                <input type="checkbox" class="checkbox-filter" id="filter_input_exclude_phrase" name="input_exclude_phrase">
                                                <label for="input_exclude_phrase">Exclude phrase</label>
                                            </div>
                                            <div class="display-inline-block" data-toggle="tooltip" data-html="true" data-placement="bottom" title="Disqualifies a DA from being added to your inbox if
                                        this phrase is found."><i class="fa fa-question-circle"></i></div>
                                        </div>
                                    </div>
                                    <div class="row mrg-top-20">
                                        <div class="col-md-1 pull-right mrg-left-25">
                                            <button class="btn btn-primary pull-right refineSearchBtn">Search</button>
                                        </div>
                                        <div class="col-md-1 pull-right">
                                            <button class="btn btn-default pull-right clearBtn">Clear</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="createFilterModal" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-md">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                        &times;
                                    </button>
                                    <h4 class="modal-title" id="modalTitle">
                                        <i class="fa fa-sliders"></i> New Alert Filter
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <input type="text" id="input_phrase" name="input_phrase"
                                                       placeholder="Enter Phrase" class="form-control step-phrase"
                                                       required>
                                            </div>
                                        </div>
                                        {#<div class=" col-md-2 col-sm-12 col-xs-12  step-filter">#}
                                            {#<select id="filter1" class="select2 item-info display-none" name="filter1"#}
                                                    {#multiple>#}
                                                {#<option value="applicant">Applicant</option>#}
                                                {#<option value="description">Description</option>#}
                                            {#</select>#}
                                        {#</div>#}
                                        <div class=" col-md-12 col-sm-12 col-xs-12 council-dropdown step-councils">
                                            <select id="councils" name="councils" multiple class="item-info select2 display-none ">
                                                {% for row in councils %}
                                                    <option value="{{ row.getId() }}">{{ row.getName() }}</option>
                                                {% endfor %}
                                            </select>
                                        </div>

                                        <div class="col-md-12  col-sm-12 col-xs-12 cost-range pull-left step-cost mrg-top-18">
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
                                                    <div class="col-md-2 no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
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
                                    </div>
                                    <div class="row mrg-top-20">
                                        <div class="col-md-12 ">
                                            <div class="checkbox no-pdd-left display-inline-block">
                                                <input type="checkbox" class="checkbox-filter" id="input_metadata" name="input_case_sensitive">
                                                <label for="input_metadata">Hide no-value projects</label>
                                            </div>
                                            <div class="display-inline-block" data-toggle="tooltip" data-html="true" data-placement="bottom" title="excludes projects with $0 construction value"><i class="fa fa-question-circle"></i></div>
                                        </div>

                                        <div class="col-md-12 ">
                                            <div class="checkbox no-pdd-left display-inline-block">
                                                <input type="checkbox" class="checkbox-filter" id="input_search_addresses" name="input_search_addresses">
                                                <label for="input_search_addresses">Search Addresses</label>
                                            </div>
                                            <div class="display-inline-block" data-toggle="tooltip" data-html="true" data-placement="bottom" title="Enabled for the search to include the addresses"><i class="fa fa-question-circle"></i></div>
                                        </div>
                                        <div class="col-md-12 ">
                                            <div class="checkbox display-inline-block no-pdd-left">
                                                <input type="checkbox" class="checkbox-filter" id="input_literal_search" name="input_literal_search">
                                                <label for="input_literal_search">Literal search</label>
                                            </div>
                                            <div class="display-inline-block" data-toggle="tooltip" data-html="true" data-placement="bottom" title="A phrase with Literal Search enabled doesn't allow
                                        the phrase to be found within other words."><i class="fa fa-question-circle"></i></div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="checkbox no-mrg-left no-pdd-left display-inline-block">
                                                <input type="checkbox" class="checkbox-filter" id="input_exclude_phrase" name="input_exclude_phrase">
                                                <label for="input_exclude_phrase">Exclude phrase</label>
                                            </div>
                                            <div class="display-inline-block" data-toggle="tooltip" data-html="true" data-placement="bottom" title="Disqualifies a project from being added to your inbox if
                                        this phrase is found."><i class="fa fa-question-circle"></i></div>
                                        </div>
                                    </div>
                                    <div class="row mrg-top-20">
                                        <div class="col-md-1 pull-right mrg-left-12">
                                            <button class="btn btn-primary pull-right" id="createBtn">Save</button>
                                        </div>
                                        <div class="col-md-1 pull-right">
                                            <button class="btn btn-default pull-right clearBtn">Clear</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
                {#<div class="row mrg-btm-40 no-pdd-left no-margin-left">#}

                    {#<div class="col-md-2">#}
                        {#<button id="createBtn" class="btn btn-default">Create Phrase#}
                        {#</button>#}
                    {#</div>#}
                    {#<div class="col-md-1">#}
                        {#<a id="bulk-delete" class="bulk-action-button btn btn-default disabled mrg-btm-10"#}
                           {#href="javascript:void(0);">#}
                            {#Delete#}
                        {#</a>#}
                    {#</div>#}
                {#</div>#}
                <div class="row overflow-x-scroll">
                    <div class="col-md-12">
                        {# Phrases table #}
                        <table id="dt-opt"
                               class="table-hover">
                            <thead>
                            <tr>
                                <th>Phrase</th>
                                <th>Council</th>
                                <th>Min($)</th>
                                <th>Max($)</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody class="step-table">
                            </tbody>
                            <tfoot>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Form Modal -->
                    <div class="modal fade" id="form-modal" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-md">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                        &times;
                                    </button>
                                    <h4 class="modal-title" id="modalTitle">
                                        Edit Filter
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12 text-center modal-loader">
                                            <i class="fa fa-spinner fa-spin fa-2x"></i>
                                        </div>
                                        <div class="display-none form-container no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <input type="text" id="input_phrase_edit" name="input_phrase"
                                                           placeholder="Enter Phrase" class="form-control step-phrase"
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mrg-btm-20 display-none">
                                                <select id="filter1_edit" class="select2 item-info " name="filter1"
                                                        multiple>
                                                    <option value="applicant">Applicant</option>
                                                    <option value="description">Description</option>
                                                </select>
                                            </div>
                                            <div class="col-md-12 mrg-btm-20">
                                                <select id="councils_edit" name="councils" multiple
                                                        class="item-info select2">
                                                    {% for row in councils %}
                                                        <option value="{{ row.getId() }}">{{ row.getName() }}</option>
                                                    {% endfor %}
                                                </select>
                                            </div>
                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                <div class="form-group search">
                                                    <div class="col-sm-12 col-md-12 no-mrg-left no-mrg-right no-pdd-left no-pdd-right text-center">
                                                        <div class="col-md-5 no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
                                                            <select id="cost-from_edit" name="cost-from"
                                                                    class="form-control cost-select" data-smart-select>
                                                                <option value="0">Min($)</option>
                                                                <option value="250000">$250,000</option>
                                                                <option value="1000000">$1,000,000</option>
                                                                <option value="3000000">$3,000,000</option>
                                                                <option value="5000000">$5,000,000</option>
                                                                <option value="10000000">$10,000,000</option>
                                                                <option value="15000000">$15,000,000</option>
                                                                <option value="25000000">$25,000,000</option>
                                                                <option value="50000000">$50,000,000</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2 no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
                                                            <span class="separator lh-2-5">-</span>
                                                        </div>
                                                        <div class="col-md-5 no-mrg-left no-mrg-right no-pdd-left no-pdd-right">
                                                            <select id="cost-to_edit" name="cost-to"
                                                                    class="form-control cost-select" data-smart-select>
                                                                <option value="50000000">Max($)</option>
                                                                <option value="250000">$250,000</option>
                                                                <option value="1000000">$1,000,000</option>
                                                                <option value="3000000">$3,000,000</option>
                                                                <option value="5000000">$5,000,000</option>
                                                                <option value="10000000">$10,000,000</option>
                                                                <option value="15000000">$15,000,000</option>
                                                                <option value="25000000">$25,000,000</option>
                                                                <option value="50000000">$50,000,000</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-12 ">
                                                <div class="checkbox no-mrg-left no-pdd-left">
                                                    <input type="checkbox" class="checkbox-filter"
                                                           id="input_metadata_edit" name="input_case_sensitive">
                                                    <label for="input_metadata_edit">Hide no-value projects</label>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="checkbox no-pdd-left display-inline-block">
                                                    <input type="checkbox" class="checkbox-filter" id="input_search_addresses_edit" name="input_search_addresses_edit">
                                                    <label for="input_search_addresses_edit">Hide no-value projects</label>
                                                </div>
                                                <div class="display-inline-block" data-toggle="tooltip" data-html="true" data-placement="bottom" title="excludes projects with $0 construction value"><i class="fa fa-question-circle"></i></div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="checkbox display-inline-block no-mrg-left no-pdd-left">
                                                    <input type="checkbox" id="input_literal_search_edit"
                                                           name="input_literal_search">
                                                    <label for="input_literal_search_edit">Intricate search</label>
                                                </div>
                                                <div class="display-inline-block" data-toggle="tooltip" data-html="true"
                                                     data-placement="bottom" title="A phrase with Intricate Search enabled does allow
                                        the phrase to be found within other words."><i
                                                            class="fa fa-question-circle"></i></div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="checkbox display-inline-block no-mrg-left no-pdd-left">
                                                    <input type="checkbox" id="input_exclude_phrase_edit"
                                                           name="input_exclude_phrase">
                                                    <label for="input_exclude_phrase_edit">Exclude phrase</label>
                                                </div>
                                                <div class="display-inline-block" data-toggle="tooltip" data-html="true"
                                                     data-placement="bottom" title="Disqualifies a DA from being added to your inbox if
                                        this phrase is found."><i class="fa fa-question-circle"></i></div>
                                            </div>

                                        </div>
                                    </div>


                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-danger deleteSave">Delete</button>
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success editSave">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {# Create Phrase Form #}
                    {#<div class="col-lg-3 col-md-3">#}
                    {#<div class="card">#}

                    {#<div class="card-heading border bottom">#}
                    {#<h4 class="card-title">Create Phrase</h4>#}
                    {#</div>#}
                    {#<form class="form" role="form" method="post" action="{{ url('phrases/create') }}">#}
                    {#<div class="card-body">#}



                    {#<div class="form-group">#}
                    {#<label class="control-label" for="input_phrase">Phrase</label>#}
                    {#<input type="text" id="input_phrase" name="input_phrase"#}
                    {#placeholder="Enter Phrase" class="form-control step-phrase" required>#}
                    {#</div>#}
                    {#<a id="bulk-delete" class="bulk-action-button btn btn-default disabled mrg-btm-10" href="javascript:void(0);">#}
                    {#Delete#}
                    {#</a>#}
                    {#<div class="checkbox no-mrg-left no-pdd-left ">#}
                    {#<input type="checkbox" id="input_case_sensitive" name="input_case_sensitive">#}
                    {#<label for="input_case_sensitive">Case sensitive</label>#}
                    {#</div>#}

                    {#<div class="checkbox no-mrg-left no-pdd-left mrg-top-10 display-inline-block">#}
                    {#<input type="checkbox" id="input_literal_search" name="input_literal_search">#}
                    {#<label for="input_literal_search">Literal search</label>#}
                    {#</div>#}
                    {#<div class="display-inline-block" data-toggle="tooltip" data-placement="top" title="A phrase with Literal Search enabled doesn't allow#}
                    {#the phrase to be found within other words."><i class="fa fa-question-circle"></i></div>#}
                    {#<br>#}

                    {#<div class="checkbox no-mrg-left no-pdd-left display-inline-block">#}
                    {#<input type="checkbox" id="input_exclude_phrase" name="input_exclude_phrase">#}
                    {#<label for="input_exclude_phrase">Exclude phrase</label>#}
                    {#</div>#}
                    {#<div class="display-inline-block" data-toggle="tooltip" data-placement="top" title="Disqualifies a DA from being added to your inbox if#}
                    {#this phrase is found."><i class="fa fa-question-circle"></i></div>#}

                    {#</div>#}

                    {#<div class="card-footer text-center border top">#}
                    {#<button type="submit" class="btn btn-default" href="javascript:void(0);">Create Phrase#}
                    {#</button>#}
                    {#</div>#}

                    {#</form>#}

                    {#</div>#}

                    {#</div>#}
                </div>

            </div>
        </div>
    </div>

    {% include "phrases/_phrasesJs.volt" %}
{% endblock %}





