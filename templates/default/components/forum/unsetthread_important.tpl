<h1>{{ language.forum_unsetimportant_title }}</h1>
<hr />
<blockquote>{{ forum.thread.title }}</blockquote>
<p>{{ language.forum_unsetimportant_desc }}</p>
<form action="" method="post">
    <input type="submit" name="submit_important" class="btn btn-danger" value="{{ language.forum_unsetimportant_submit }}">
    <a href="{{ system.url }}/forum/viewtopic/{{ forum.thread.id }}" class="btn btn-info">{{ language.forum_unsetimportant_cancel }}</a>
</form>