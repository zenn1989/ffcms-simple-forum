{% import 'macro/notify.tpl' as notifytpl %}
<script type="text/javascript" src="{{ system.script_url }}/resource/wysibb/jquery.wysibb.min.js"></script>
{% if system.lang in ['ar', 'cn', 'de', 'en', 'fr', 'pl', 'tr', 'ua', 'vn'] %}
    <script type="text/javascript" src="{{ system.script_url }}/resource/wysibb/lang/{{ system.lang }}.js"></script>
{% endif %}
<link rel="stylesheet" href="{{ system.script_url }}/resource/wysibb/theme/default/wbbtheme.css" />
<script>
    $(document).ready(function () {
        $(".wysibb-editor").wysibb({img_uploadurl: "{{ system.script_url }}/api.php?iface=front&object=wysibbimage&dir=forum", lang: "{{ system.lang }}"})
    });
</script>
<h1>{{ language.forum_addthread_title }}</h1>
<hr />
<ol class="breadcrumb">
    <li><a href="{{ system.url }}">{{ language.forum_breadcrumb_general }}</a></li>
    <li><a href="{{ system.url }}/forum/">{{ language.forum_breadcrumb_main }}</a></li>
    <li class="active">{{ language.forum_breadcrumb_newthread }}</li>
</ol>
{% if notify.forum_add_title_incorrent %}
    {{ notifytpl.error(language.forum_addthread_error_title) }}
{% endif %}
{% if notify.forum_add_text_incorrent %}
    {{ notifytpl.error(language.forum_addthread_error_text) }}
{% endif %}
{% if notify.forum_error_spam %}
    {{ notifytpl.error(language.forum_addthread_error_spam) }}
{% endif %}
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-success">
            <div class="panel-heading">
                {{ language.forum_addthread_title }}
            </div>
            <div class="panel-body">
                <form class="form-horizontal" role="form" method="post">
                    <div class="form-group">
                        <label class="col-md-3">{{ language.forum_addthread_form_title }}</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="forum_title" value="{{ saveform.title }}">
                        </div>
                    </div>
                    <textarea class="form-control wysibb-editor" rows="8" name="forum_message">{{ saveform.text }}</textarea>
                    <input type="submit" name="forum_submit" class="btn btn-success pull-right" value="{{ language.forum_addthread_form_submit }}">
                </form>
            </div>
        </div>
    </div>
</div>