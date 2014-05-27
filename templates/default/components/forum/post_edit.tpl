<h1>{{ language.forum_postedit_title }}</h1>
<hr />
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
<div class="row" id="forum-form">
    <div class="col-md-12">
        <form action="" method="post" class="form-horizontal">
            <textarea id="forum-textarea" class="form-control wysibb-editor" rows="5" name="forum_message">{{ forum.post.text }}</textarea>
            <div class="pull-right">
            <input type="submit" name="forum_submit" class="btn btn-danger" value="{{ language.forum_postedit_submit }}">
            <a href="{{ system.url }}/forum/viewtopic/{{ forum.thread.id }}" class="btn btn-info">{{ language.forum_postedit_cancel }}</a>
            </div>
        </form>
    </div>
</div>