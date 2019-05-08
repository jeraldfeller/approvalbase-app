{% extends "_templates/base.volt" %}
{% block extra_css %}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/jquery.dataTables.min.css"
          integrity="sha256-YY1izqyhIj4W3iyJOaGWOpXDSwrHWFL4Nfk+W0LyCHE=" crossorigin="anonymous"/>
{% endblock %}

{% block content %}
    <div id="content">
        <div class="menubar sticky">
            <div class="sidebar-toggler visible-xs">
                <i class="ion-navicon"></i>
            </div>
            <div class="options">
                {% if isAdmin == 0 %}
                    <a href="{{ url(from) }}?l={{ leadId }}">&larr; Back to {{ from|capitalize }}</a>
                {% else %}
                    <a href="{{ url() }}admin/leads?l={{ leadId }}">&larr; Back to Leads</a>
                {% endif %}
            </div>
            {% include "includes/user-dropdown.volt" %}
        </div>
        {% include "includes/flashMessages.volt" %}


        <div class="mrg-top-10">
            {% if loggedInUser.isAdministrator() %}

                <div class="card bg-unread">

                    <div class="card-block">

                        <h4 class="card-title">Administrator</h4>
                        <ul>
                            <li>This development application was sent to {{ da.Users.count() }} user(s)</li>
                        </ul>

                    </div>

                </div>

            {% endif %}

            <div class="row">

                <div class="col-lg-8">

                    {# Main #}
                    <div class="card">

                        <div class="card-block">

                            {# Title #}
                            {% if da.getCouncilReferenceAlt() != null %}
                                <h4 class="card-title">{{ da.Council.getName() }} Development
                                    Application {{ da.getCouncilReference() }} ({{ da.getCouncilReferenceAlt() }})</h4>
                            {% else %}
                                <h4 class="card-title">{{ da.Council.getName() }} Development
                                    Application {{ da.getCouncilReference() }}</h4>
                            {% endif %}

                            <p class="lead">{{ da.getDescription() }}</p>

                            <table class="table">
                                <tbody>
                                <tr>
                                    <th>Council</th>
                                    <td>{{ da.Council.getName() }}</td>
                                </tr>
                                <tr>
                                    <th>Lodge date</th>
                                    <td>{{ da.getLodgeDate().format("d/m/Y") }}</td>
                                </tr>
                                <tr>
                                    <th>Estimated Cost</th>
                                    <td>${{ da.getEstimatedCost(true) }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {% if da.Documents.count() > 0 %}
                        <div class="card">
                            <div class="card-block">
                                <div class="col-sm-11 no-pdd-left">
                                    <h4 class="card-title">Documents</h4>
                                </div>
                                <div class="col-sm-1 no-pdd-right">
                                    <button data-id="{{ leadId }}" class="btn btn-default" id="downloadPdfBtn" title="Download PDF"><i class="fa fa-download"></i></button>
                                </div>

                                <table class="table">
                                    <tbody>
                                    {% for document in da.Documents %}
                                        <tr>
                                            <td>
                                                <a href="{{ document.getUrl() }}"
                                                   target="_blank">{{ document.getName() }}</a>
                                            </td>
                                            {% if document.getDate() != null %}
                                                <td>{{ document.getDate().format("d/m/Y") }}</td>
                                            {% endif %}
                                        </tr>
                                    {% endfor %}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    {% endif %}

                    {% if da.Parties.count() > 0 %}
                        <div class="card">
                            <div class="card-block">
                                <h4 class="card-title">Parties</h4>
                                <table class="table">
                                    <tbody>
                                    {% for party in da.Parties %}
                                        <tr>
                                            <th>{{ party.getRole() }}</th>
                                            <td>{{ party.getName() }}</td>
                                        </tr>
                                    {% endfor %}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    {% endif %}

                </div>

                <div class="col-lg-4">
                    {% for address in da.Addresses %}
                        <div class="card">

                            {# Could generate a Google Maps (static) map here #}
                            {#<div class="card-media">#}
                                {#<div id="map_canvas" style="width: 100%;height: 250px; "></div>#}
                            {#</div>#}

                            <div class="card-block">
                                <h4 class="card-title">Location {{ loop.index }}</h4>
                                <a target="_blank" href="https://www.google.com/maps/place/{{ address.getAddress() }}">{{ address.getAddress() }}</a>
                            </div>
                        </div>
                    {% endfor %}
                </div>

            </div>
        </div>
    </div>
    <script>
       $(document).ready(function (){
         $('#downloadPdfBtn').click(function(){
           $btn = $(this);
           $btn.html('<i class="fa fa-spinner fa-spin"></i>');
           $btn.attr('disabled', true);
           var dasId = $(this).attr('data-id');
           $.ajax({
             url: '{{ url('pdf/downloadPdf?ajax=1') }}',
             type: 'POST',
             data: {
               id: dasId,
               council: '{{ da.Council.getName() }}'
             },
             dataType: 'json'
           }).done(function (response) {
              console.log(response);
              if(response != false){
                if(response.s == 1){
                  location.href = '{{ url('pdf/download') }}?file='+encodeURI(response.file);
                }else{
                  if(response.links.length > 0){
                    for(var l = 0; l < response.links.length; l++){

                      window.open(response.links[l], '_blank');
                    }
                  }
                }

              }else{
                showNotification('', 'error');
              }
              $btn.html('<i class="fa fa-download"></i>');
              $btn.attr('disabled', false);

           });
         });
        });
    </script>
    {#<script async defer#}
            {#src="https://maps.googleapis.com/maps/api/js?key={{ googleMapAPI }}&callback=codeAddress"></script>#}
    {#<script>#}
      {#var geocoder, map;#}
      {#function codeAddress(address) {#}
        {#geocoder = new google.maps.Geocoder();#}
        {#geocoder.geocode({#}
          {#'address': '{{ address.getAddress() }}'#}
        {#}, function (results, status) {#}
          {#if (status == google.maps.GeocoderStatus.OK) {#}
            {#var myOptions = {#}
              {#zoom: 15,#}
              {#center: results[0].geometry.location,#}
              {#mapTypeId: google.maps.MapTypeId.ROADMAP#}
            {#}#}
            {#map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);#}

            {#var marker = new google.maps.Marker({#}
              {#map: map,#}
              {#position: results[0].geometry.location#}
            {#});#}
          {#}#}
        {#});#}
      {#}#}
    {#</script>#}

{% endblock %}
