<h1>{{ extension.title }}<small>{{ language.admin_components_forum_editcat_title }}</small></h1>
<hr />
{% include 'components/forum/menu_include.tpl' %}

<form class="form-horizontal" method="post" action="">
    <div class="form-group">
        <label class="col-md-3 control-label">{{ language.admin_components_forum_editcat_cattitle }}</label>
        <div class="col-md-9">
            <input name="cat_title" type="text" class="form-control" value="{{ forum.category.title }}">
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">{{ language.admin_components_forum_editcat_priority }}</label>
        <div class="col-md-9">
            <input name="cat_order" type="text" class="form-control" value="{{ forum.category.order }}">
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-offset-3 col-md-9">
            <input type="submit" name="submit" value="{{ language.admin_components_forum_editcat_button_submit }}" class="btn btn-success" />
        </div>
    </div>
</form>