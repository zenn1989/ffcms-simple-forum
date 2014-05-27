<h1>{{ language.forum_setimportant_title }}</h1>
<hr />
<blockquote>{{ forum.thread.title }}</blockquote>
<p>{{ language.forum_setimportant_desc }}</p>
<form action="" method="post">
    <input type="submit" name="submit_important" class="btn btn-danger" value="{{ language.forum_setimportant_submit }}">
    <a href="{{ system.url }}/forum/viewtopic/{{ forum.thread.id }}" class="btn btn-info">{{ language.forum_setimportant_cancel }}</a>
</form>