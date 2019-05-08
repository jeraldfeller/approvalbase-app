
{% if flashSession.has("error") %}
    <div class="box small rounded bkg-red color-white shadow">
        <span class=" title-small">{{ flashSession.output(true) }}</span>
    </div>
{% endif %}
{% if flashSession.has("success") %}
    <div class="box small rounded bkg-green color-white shadow">
        <span class=" title-small">{{ flashSession.output(true) }}</span>
    </div>
{% endif %}
{% if flashSession.has("notice") %}
    <div class="box small rounded bkg-blue color-white shadow">
        <span class=" title-small">{{ flashSession.output(true) }}</span>
    </div>
{% endif %}


