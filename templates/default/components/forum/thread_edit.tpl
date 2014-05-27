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
<h1>{{ language.forum_threadedit_title }}</h1>
<hr />
<div class="row">
    <div class="col-md-12">
        <form class="form-horizontal" role="form" method="post">
            <div class="form-group">
                <label class="col-md-3">{{ language.forum_threadedit_form_title }}</label>

                <div class="col-md-9">
                    <input type="text" class="form-control" name="forum_title" value="{{ forum.thread.title }}">
                </div>
            </div>
            <textarea class="form-control wysibb-editor" rows="8" name="forum_message">{{ forum.thread.text }}</textarea>
            <div class="pull-right">
                <input type="submit" name="forum_submit" class="btn btn-success" value="{{ language.forum_threadedit_submit }}">
                <a href="{{ system.url }}/forum/viewtopic/{{ forum.thread.id }}" class="btn btn-info">{{ language.forum_threadedit_cancel }}</a>
            </div>
        </form>
    </div>
</div>