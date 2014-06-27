<h1>{{ extension.title }}<small>{{ language.admin_components_forum_category_title }}</small></h1>
<hr />
{% include 'components/forum/menu_include.tpl' %}
<div class="row">
    <div class="col-md-12">
        <div class="pull-right">
            <a href="?object=components&action=forum&make=catadd" class="btn btn-primary">{{ language.admin_components_forum_list_add_cat }}</a>
            <a href="?object=components&action=forum&make=forumadd" class="btn btn-success">{{ language.admin_components_forum_list_add_forum }}</a>
        </div>
    </div>
</div>
{% for cat_order,cat_data in forum %}
<div class="panel panel-default">
    <div class="panel-heading">
        {{ language.admin_components_forum_list_cat_title }}: {{ cat_data.name }} <span class="pull-right label label-danger">index: {{ cat_order }}</span>
        <a href="?object=components&action=forum&make=catedit&id={{ cat_data.id }}"><i class="fa fa-pencil"></i></a>
        <a href="?object=components&action=forum&make=catdel&id={{ cat_data.id }}"><i class="fa fa-trash-o"></i></a>
    </div>
    <div class="panel-body">
    {% for forum_id,forum_data in cat_data.forums %}
        <div class="row">
            <div class="col-md-9">
                <strong>{{ forum_data.title }}</strong>
                <p>{{ forum_data.desc }}</p>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <a href="?object=components&action=forum&make=forumedit&id={{ forum_data.id }}"><i class="fa fa-pencil fa-lg"></i></a>
                    <a href="?object=components&action=forum&make=forumdel&id={{ forum_data.id }}"><i class="fa fa-trash-o fa-lg"></i></a>
                </div>
            </div>
        </div>
        <hr />
    {% else %}
        <p class="alert alert-warning">{{ language.admin_components_forum_list_empty }}</p>
    {% endfor %}
    </div>
</div>
{% endfor %}