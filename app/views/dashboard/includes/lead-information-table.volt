
<div class="panel panel-default">

    <div class="panel-heading">Development Application Information</div>

    <div class="panel-body">

        <table class="table">

            <tbody>

                {# Council #}
                <tr>
                    <th>Council</th>
                    <td>{{ development_application.getCouncil().getName() }}</td>
                </tr>

                {#  description #}
                <tr>
                    <th>Description</th>
                    <td>{{ development_application.getDescription() }}</td>
                </tr>

                {# Estimated cost #}
                <tr>
                    <th>Estimated cost</th>
                        {% if development_application.getEstimatedCost()|length > 0 %}
                        <td>{{ development_application.getEstimatedCost(true) }}</td>
                    {% else %}
                        <td>Unknown</td>
                    {% endif %}
                </tr>

                {# Address #}
                <tr>
                    <th>Address</th>
                    <td>
                        {% if development_application.getAddress() != "" %}
                            <a href="https://www.google.com/maps/search/?api=1&query={{ development_application.getAddress()|url_encode }}" target="_blank">
                                {{ development_application.getAddress() }}
                            </a>
                        {% else %}
                            {{ development_application.getAddress() }}
                        {% endif %}
                    </td>
                </tr>

                {# Lodge date #}
                <tr>
                    <th>Lodge date</th>
                    <td>{{ development_application.getLodgeDate().format('d-m-Y') }}</td>
                </tr>

                {# Details i.e. council website URL #}
                <tr>
                    <th>Details</th>
                    <td>
                        <a href="{{ development_application.getCouncilUrl() }}" target="_blank">
                            View details on council website
                        </a>
                    </td>
                </tr>

                {# Detected Phrases #}
                {% if development_application.Phrases.count() > 0 %}

                    <tr>
                        <th>Phrases</th>
                        <td>
                            {% for phrase in development_application.Phrases %}

                                <a href="#" class="badge badge-primary">
                                    {{ phrase.getPhrase() }}
                                </a>

                            {% endfor %}
                        </td>
                    </tr>
                {% endif %}

                {# Council reference #}
                <tr>
                    <th class="text-muted">Reference</th>
                    <td class="text-muted">{{ development_application.getCouncilReference() }}</td>
                </tr>

            </tbody>

        </table>



    </div>

</div>
