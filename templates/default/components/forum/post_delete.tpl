<h1>{{ language.forum_postdel_title }}</h1>
<hr />
<p>{{ language.forum_postdel_desc }}</p>
<form action="" method="post">
    <input type="submit" name="submit_delete" class="btn btn-danger" value="{{ language.forum_postdel_submit }}">
    <a href="{{ system.url }}/forum/viewtopic/{{ post.thread_id }}" class="btn btn-info">{{ language.forum_postdel_cancel }}</a>
</form>