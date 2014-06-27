<h1>{{ extension.title }}<small>{{ language.admin_components_forum_editforum_title }}</small></h1>
<hr />
{% include 'components/forum/menu_include.tpl' %}

<form class="form-horizontal" method="post" action="">
    {% if options.is_add %}
        <div class="form-group">
            <label class="col-md-3 control-label">{{ language.admin_components_forum_editforum_catown_title }}</label>
            <div class="col-md-9">
                <select name="category_id" class="form-control">
                    {% for cat_data in forum.categorys %}
                    <option value="{{ cat_data.cat_id }}">{{ cat_data.cat_name }}</option>
                    {% endfor %}
                </select>
            </div>
        </div>
    {% endif %}
    <div class="form-group">
        <label class="col-md-3 control-label">{{ language.admin_components_forum_editforum_forumtitle }}</label>
        <div class="col-md-9">
            <input name="forum_title" type="text" class="form-control" value="{{ forum.title }}">
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">{{ language.admin_components_forum_editforum_forumdesc }}</label>
        <div class="col-md-9">
            <input name="forum_desc" type="text" class="form-control" value="{{ forum.desc }}">
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-offset-3 col-md-9">
            <input type="submit" name="submit" value="{{ language.admin_components_forum_editforum_button_submit }}" class="btn btn-success" />
        </div>
    </div>
</form>