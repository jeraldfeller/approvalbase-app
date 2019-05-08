{% if page['totalPages'] > 1 %}
    <nav class="text-xs-center">
        <ul class="pagination">

            {% if _url['amountOfGetParams'] > 0 %}
                {% set _paginationUrl = _url['completeUrl'] %}
            {% else %}
                {% set _paginationUrl = _url['baseUrl'] %}
            {% endif %}

            {# First button #}
            {% if page['current'] > 6 %}
                <li class="page-item">
                    <a class="page-link" href="{{ _paginationUrl }}">First</a>
                </li>
            {% endif %}

            {# Show previous button #}
            {% if page['current'] > 1  %}
                <li class="page-item">
                    {% if _url['amountOfGetParams'] > 0 %}
                        <a class="page-link" href="{{ _paginationUrl ~ '&page=' ~ (page['current'] - 1) }}">Previous</a>
                    {% else %}
                        <a class="page-link" href="{{ _paginationUrl ~ '?page=' ~ (page['current'] - 1) }}">Previous</a>
                    {% endif %}
                </li>
            {% endif %}

            {# If we're in the middle of the pagination #}
            {% if page['current'] - 5 > 1 %}
                {% set threePagesBack = page['current'] - 5 %}
                {% for i in threePagesBack..page['current'] - 1 %}
                    <li class="page-item">
                        {% if _url['amountOfGetParams'] > 0 %}
                            <a class="page-link" href="{{ _paginationUrl ~ '&page=' ~ i }}">{{ i }}</a>
                        {% else %}
                            <a class="page-link" href="{{ _paginationUrl ~ '?page=' ~ i }}">{{ i }}</a>
                        {% endif %}
                    </li>
                {% endfor %}

                {# Current page #}
                <li class="page-item active">
                    {% if _url['amountOfGetParams'] > 0 %}
                        <a class="page-link" href="{{ _paginationUrl ~ '&page=' ~ page['current'] }}">{{ page['current'] }}</a>
                    {% else %}
                        <a class="page-link" href="{{ _paginationUrl ~ '?page=' ~ page['current'] }}">{{ page['current'] }}</a>
                    {% endif %}
                </li>

                {# If we're at the start of the pagination #}
            {% else %}
                {% for i in 1..page['current'] %}
                    <li class="page-item{% if i is page['current'] %} active{% endif %}">
                        {% if _url['amountOfGetParams'] > 0 %}
                            <a class="page-link" href="{{ _paginationUrl ~ '&page=' ~ i }}">{{ i }}</a>
                        {% else %}
                            <a class="page-link" href="{{ _paginationUrl ~ '?page=' ~ i }}">{{ i }}</a>
                        {% endif %}
                    </li>
                {% endfor %}
            {% endif %}

            {% for i in page['current'] + 1..page['current'] + 5 %}
                {% if i > page['totalPages'] %}
                    {% break %}
                {% endif %}

                <li class="page-item">
                    {% if _url['amountOfGetParams'] > 0 %}
                        <a class="page-link" href="{{ _paginationUrl ~ '&page=' ~ i }}">{{ i }}</a>
                    {% else %}
                        <a class="page-link" href="{{ _paginationUrl ~ '?page=' ~ i }}">{{ i }}</a>
                    {% endif %}
                </li>
            {% endfor %}


            {# Next #}
            {% if page['next'] != page['current'] and page['next'] != 0 %}
                <li class="page-item">
                    {% if _url['amountOfGetParams'] > 0 %}
                        <a class="page-link" href="{{ _paginationUrl ~ '&page=' ~ page['next'] }}">Next</a>
                    {% else %}
                        <a class="page-link" href="{{ _paginationUrl ~ '?page=' ~ page['next'] }}">Next</a>
                    {% endif %}
                </li>

            {% endif %}
        </ul>
    </nav>
{% endif %}
