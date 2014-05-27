<h1>{{ language.forum_threaddel_title }}</h1>
<hr />
<p>{{ language.forum_threaddel_desc }}</p>
<form action="" method="post">
    <input type="submit" name="submit_delete" class="btn btn-danger" value="{{ language.forum_threaddel_submit }}">
    <a href="{{ system.url }}/forum/viewtopic/{{ thread.id }}" class="btn btn-info">{{ language.forum_threaddel_cancel }}</a>
</form>