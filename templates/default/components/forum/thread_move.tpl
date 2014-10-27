<h1>{{ language.forum_threadmove_title }}</h1>
<hr />
<p>{{ language.forum_threadmove_desc }}:</p>
<form action="" method="post" class="form-horizontal">
    <div class="form-group">
        <label class="col-md-3 control-label">{{ language.forum_threadmove_label_moveto }}</label>
        <div class="col-md-9">
            <select name="new_forum" class="form-control">
                {% for f_id,f_name in thread.move %}
                <option value="{{ f_id }}">{{ f_name }}</option>
                {% endfor %}
            </select>
        </div>
    </div>
    <input type="submit" name="submit" class="btn btn-warning" value="{{ language.forum_threadmove_btn_move }}">
    <a href="{{ system.url }}/forum/viewtopic/{{ thread.id }}" class="btn btn-info">{{ language.forum_threadmove_btn_cancel }}</a>
</form>