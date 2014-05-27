<h1>{{ language.forum_global_title }} - {{ language.forum_stream_title }}</h1>
<hr />
<ol class="breadcrumb">
    <li><a href="{{ system.url }}">{{ language.forum_breadcrumb_general }}</a></li>
    <li><a href="{{ system.url }}/forum/">{{ language.forum_breadcrumb_main }}</a></li>
    <li class="active">{{ language.forum_breadcrumb_updates }}</li>
</ol>
<ul class="nav nav-tabs nav-justified">
    <li><a href="{{ system.url }}/forum/">{{ language.forum_tab_main }}</a></li>
    <li class="active"><a href="{{ system.url }}/forum/stream.html">{{ language.forum_tab_updates }}</a></li>
</ul>
<div class="panel panel-info">
    <div class="panel-heading">
        {{ language.forum_stream_title }}
    </div>
    <div class="panel-body">
        {% for action in forum.stream %}
            {% if action.new_thread %}
                <p><i class="fa fa-plus-circle"></i> {{ action.updater }} {{ language.forum_stream_create }} <a href="{{ system.url }}/forum/viewtopic/{{ action.thread_id }}">{{ action.title }}</a> <span class="label label-primary pull-right">{{ action.time }}</span></p>
                <hr />
            {% else %}
                <p><i class="fa fa-comment-o"></i> {{ action.updater }} {{ language.forum_stream_answer }} <a href="{{ system.url }}/forum/viewtopic/{{ action.thread_id }}">{{ action.title }}</a> <span class="label label-success pull-right">{{ action.time }}</span></p>
                <hr />
            {% endif %}
        {% endfor %}
    </div>
</div>