<div class="alert alert-danger">{$lang::forum_admin_del_for_danger}</div>
<p>Форум: <strong>{$forum_title}</strong></p>

<form action="{$self_url}" method="post">
    {$lang::forum_admin_moveto}: <select name="forum_moveto">{$forum_options}</select><br />
    <input type="submit" name="forum_submit" value="Удалить" class="btn btn-danger" />
</form>