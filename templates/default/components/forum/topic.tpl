<link rel="stylesheet" href="{{ system.theme }}/css/forum.css" />
{% import 'macro/notify.tpl' as notifytpl %}
<script>
    function forumquote(id) {
        var mess = $('#message-'+id).text();
        mess = '[quote]'+mess+'[/quote]';
        $('#forum-textarea').val(mess);
    }
</script>
<h1>{{ language.forum_topic_name }}: {{ forum.topic.title }}</h1>
<hr />
<ol class="breadcrumb">
    <li><a href="{{ system.url }}">{{ language.forum_breadcrumb_general }}</a></li>
    <li><a href="{{ system.url }}/forum/">{{ language.forum_breadcrumb_main }}</a></li>
    <li><a href="{{ system.url }}/forum/viewboard/{{ forum.id }}">{{ forum.name }}</a></li>
    <li class="active">{{ forum.topic.title }}</li>
</ol>
<div class="pull-right">
    {{ pagination }}
</div>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                {{ forum.topic.title }}
            </div>
            <div class="panel-body">
                {% for post in forum.post %}
                <div class="row" id="post{{ post.number }}">
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="thumbnail">
                                <a href="{{ system.url }}/user/id{{ post.user_id }}"><img src="{{ system.script_url }}/{{ post.user_avatar }}" alt="{{ post.user_name }}" /></a>
                                <div class="caption">
                                    <strong>{{ post.user_name }}</strong><br />
                                    {{ language.forum_topic_messages }}: {{ post.user_posts }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="pull-left">
                            {{ post.post_time }}
                        </div>
                        <div class="pull-right">
                            <a href="{{ system.url }}/forum/viewtopic/{{ forum.thread.id }}#post{{ post.number }}">#{{ post.number }}</a>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="well" id="message-{{ post.number }}">{{ post.post_text }}</div>
                            </div>
                        </div>
                        {% if user.id > 0 %}
                        <div class="pull-right">
                            <a href="#forum-form" onclick="return forumquote({{ post.number }});" class="btn btn-default"><i class="fa fa-retweet"></i></a>
                            {% if user.admin %}
                                {% if post.thread_id > 0 %}
                                    <a href="{{ system.url }}/forum/delthread/{{ post.thread_id }}/{{ forum.id }}" class="btn btn-default"><i class="fa fa-trash-o"></i></a>
                                    <a href="{{ system.url }}/forum/editthread/{{ post.thread_id }}" class="btn btn-default"><i class="fa fa-pencil"></i></a>
                                    {% if post.thread_important > 0 %}
                                        <a href="{{ system.url }}/forum/unsetimportant/{{ post.thread_id }}" class="btn btn-default"><i class="fa fa-paperclip" style="color:red;"></i></a>
                                    {% else %}
                                        <a href="{{ system.url }}/forum/setimportant/{{ post.thread_id }}" class="btn btn-default"><i class="fa fa-paperclip" style="color:green;"></i></a>
                                    {% endif %}
                                {% else %}
                                    <a href="{{ system.url }}/forum/delpost/{{ post.post_id }}/{{ forum.thread.id }}/{{ forum.id }}" class="btn btn-default"><i class="fa fa-trash-o"></i></a>
                                    <a href="{{ system.url }}/forum/editpost/{{ post.post_id }}/{{ forum.thread.id }}" class="btn btn-default"><i class="fa fa-pencil"></i></a>
                                {% endif %}
                            {% endif %}
                        </div>
                        {% endif %}
                    </div>
                </div>
                    <hr />
                {% endfor %}
            </div>
        </div>
    </div>
</div>
{% if notify.forum_error_spam %}
    {{ notifytpl.error(language.forum_topic_spamattempt) }}
{% endif %}
{% if user.id > 0 %}
    {% if permission.add_post %}
        <script type="text/javascript" src="{{ system.script_url }}/resource/wysibb/jquery.wysibb.js"></script>
        {% if system.lang in ['ar', 'cn', 'de', 'en', 'fr', 'pl', 'tr', 'ua', 'vn', 'ru'] %}
            <script type="text/javascript" src="{{ system.script_url }}/resource/wysibb/lang/{{ system.lang }}.js"></script>
        {% endif %}
        <link rel="stylesheet" href="{{ system.script_url }}/resource/wysibb/theme/default/wbbtheme.css" />
        <script>
            $(document).ready(function () {
                $(".wysibb-editor").wysibb({img_uploadurl: "{{ system.script_url }}/api.php?iface=front&object=wysibbimage&dir=forum", lang: "{{ system.lang }}"})
            });
        </script>
        <div class="row" id="forum-form">
            <div class="col-md-12">
                <form action="" method="post" class="form-horizontal">
                <textarea id="forum-textarea" class="form-control wysibb-editor" rows="5" name="forum_message">{{ forum.save_form.message }}</textarea>
                <input type="submit" name="forum_submit" class="btn btn-success pull-right" value="{{ language.forum_topic_addmessage }}">
                </form>
            </div>
        </div>
    {% else %}
        {{ notifytpl.error(language.forum_notify_blocked) }}
    {% endif %}
{% else %}
    {{ notifytpl.warning(language.forum_notify_regmsg) }}
{% endif %}
{{ pagination }}