{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_components_forum_delcat_title }}</small></h1>
<hr />
{% include 'components/forum/menu_include.tpl' %}
<p>{{ language.admin_components_forum_delcat_desc }}</p>
<form class="form-horizontal" method="post" action="">
    <div class="form-group">
        <label class="col-md-3 control-label">{{ language.admin_components_forum_delcat_cattitle }}</label>
        <div class="col-md-9">
            <input type="text" class="form-control" value="{{ forum.category.title }}" disabled>
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-offset-3 col-md-9">
            {% if notify.category.not_empty %}
                {{ notifytpl.error(language.admin_components_forum_delcat_notify_notempty) }}
            {% else %}
            <input type="submit" name="submit" value="{{ language.admin_components_forum_delcat_button_submit }}" class="btn btn-danger" />
            {% endif %}
        </div>
    </div>
</form>