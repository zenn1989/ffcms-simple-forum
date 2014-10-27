<link rel="stylesheet" href="{{ system.theme }}/css/forum.css" />
<h1>Раздел - {{ forum.name }}</h1>
<hr />
<ol class="breadcrumb">
    <li><a href="{{ system.url }}">{{ language.forum_breadcrumb_general }}</a></li>
    <li><a href="{{ system.url }}/forum/">{{ language.forum_breadcrumb_main }}</a></li>
    <li class="active">{{ forum.name }}</li>
</ol>
{% if permission.create_thread %}
<div class="row">
    <div class="col-md-12">
        <p class="pull-right">
            <a href="{{ system.url }}/forum/addthread/{{ forum.id }}" class="btn btn-success">{{ language.forum_item_addthread }}</a>
        </p>
    </div>
</div>
{% endif %}
{% if forum.topics %}
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                {{ forum.name }}
            </div>
            <div class="panel-body panel-no-padding">
                <table class="table table-forum table-bordered">
                    <thead>
                    <tr>
                        <th class="col-md-7">{{ language.forum_item_topic }}</th>
                        <th class="col-md-1">{{ language.forum_item_answers }}</th>
                        <th class="col-md-1">{{ language.forum_item_views }}</th>
                        <th class="col-md-3">{{ language.forum_item_update }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {% for thread in forum.topics %}
                        <tr>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <h2 class="forum-head"> {% if thread.important %}<i class="fa fa-paperclip"></i> {% endif %}
                                            <a href="{{ system.url }}/forum/viewtopic/{{ thread.id }}">{{ thread.title }}</a>
                                        </h2>
                                        {{ thread.owner_name }}, <em>{{ thread.create }}</em>
                                    </div>
                                </div>
                            </td>
                            <td>{{ thread.posts }}</td>
                            <td>{{ thread.views }}</td>
                            <td>
                                {% if thread.last_user != null %}
                                    {{ thread.last_user }}<br />
                                    <a href="{{ system.url }}/forum/viewtopic/{{ thread.id }}#last">{{ thread.update }}</a>
                                {% else %}
                                    Нет информации
                                {% endif %}
                            </td>
                        </tr>
                    {% endfor %}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
{% else %}
<p>{{ language.forum_item_empty }}</p>
{% endif %}
{{ pagination }}