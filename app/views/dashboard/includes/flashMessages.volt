{% if flashSession.has("error") %}
    <div class="alert alert-danger alert-dismissible" role="alert">
        {{ flashSession.output(true) }}
    </div>
{% endif %}
{% if flashSession.has("success") %}
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ flashSession.output(true) }}
    </div>
{% endif %}
{% if flashSession.has("notice") %}
    <div class="alert alert-info alert-dismissible" role="alert">
        {{ flashSession.output(true) }}
    </div>
{% endif %}


