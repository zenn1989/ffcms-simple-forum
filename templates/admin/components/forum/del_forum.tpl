<h1>{{ extension.title }}<small>{{ language.admin_components_forum_delforum_title }}</small></h1>
<hr />
{% include 'components/forum/menu_include.tpl' %}
<p>{{ language.admin_components_forum_delforum_desc }}</p>
<form class="form-horizontal" method="post" action="">
    <div class="form-group">
        <label class="col-md-3 control-label">{{ language.admin_components_forum_delforum_forumtitle }}</label>
        <div class="col-md-9">
            <input type="text" class="form-control" value="{{ forum.title }}" disabled>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">{{ language.admin_components_forum_delforum_recepient }}</label>
        <div class="col-md-9">
            <select name="forum_moveto" class="form-control">
                <option value="0">{{ language.admin_components_forum_delforum_opt_delall }}</option>
                {% for forummove in forum.moveto %}
                <option value="{{ forummove.forum_id }}">{{ forummove.cat_name }} -> {{ forummove.forum_name }}</option>
                {% endfor %}
            </select>
            <p class="help-block">{{ language.admin_components_forum_delforum_rec_desc }}</p>
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-offset-3 col-md-9">
            <input type="submit" name="submit" value="{{ language.admin_components_forum_delforum_button_submit }}" class="btn btn-danger" />
        </div>
    </div>
</form>