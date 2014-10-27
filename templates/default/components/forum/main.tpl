<link rel="stylesheet" href="{{ system.theme }}/css/forum.css" />
<h1>{{ language.forum_global_title }}</h1>
<hr />
<ol class="breadcrumb">
    <li><a href="{{ system.url }}">{{ language.forum_breadcrumb_general }}</a></li>
    <li class="active">{{ language.forum_breadcrumb_main }}</li>
</ol>
<ul class="nav nav-tabs nav-justified">
    <li class="active"><a href="{{ system.url }}/forum/">{{ language.forum_tab_main }}</a></li>
    <li><a href="{{ system.url }}/forum/stream.html">{{ language.forum_tab_updates }}</a></li>
</ul>
{% for thread_id,thread_data in forum %}
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                {{ thread_data.name }}
            </div>
            <div class="panel-body panel-no-padding">
                {% if thread_data.forums %}
                <table class="table table-forum table-condensed">
                    <thead>
                    <tr>
                        <th class="col-md-7">{{ language.forum_list_part }}</th>
                        <th class="col-md-1">{{ language.forum_list_topic }}</th>
                        <th class="col-md-1">{{ language.forum_list_posts }}</th>
                        <th class="col-md-3">{{ language.forum_list_lastmsg }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {% for forum_id,forum_data in thread_data.forums %}
                    <tr>
                        <td>
                            <div class="row">
                                <div class="col-md-1">
                                    <i class="fa fa-stack fa-2x fa-envelope"></i>
                                </div>
                                <div class="col-md-11">
                                    <h2 class="forum-head"><a href="{{ system.url }}/forum/viewboard/{{ forum_data.id }}">{{ forum_data.title }}</a></h2>
                                    <p>{{ forum_data.desc }}</p>
                                </div>
                            </div>
                        </td>
                        <td>{{ forum_data.threads }}</td>
                        <td>{{ forum_data.posts }}</td>
                        <td>
                            {% if forum_data.lastposter_name != null %}
                            {{ forum_data.lastposter_name }}<br />
                            <a href="{{ system.url }}/forum/viewtopic/{{ forum_data.lasttopic_id }}">{{ forum_data.lastupdate }}</a>
                            {% else %}
                                {{ language.forum_global_empty }}
                            {% endif %}
                        </td>
                    </tr>
                    {% endfor %}
                    </tbody>
                </table>
                {% else %}
                    <p>{{ language.forum_category_empty }}</p>
                {% endif %}
            </div>
        </div>
    </div>
</div>
{% else %}
    <p class="alert alert-danger">{{ language.forum_list_empty }}</p>
{% endfor %}