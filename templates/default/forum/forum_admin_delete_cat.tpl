<div class="alert alert-danger">{$lang::forum_admin_del_danger}</div>
<p>Раздел: <strong>{$forum_title}</strong></p>

<form action="{$self_url}" method="post">
    {$lang::forum_admin_moveto}: <select name="forum_moveto">{$forum_options}</select><br />
    <input type="submit" name="forum_submit" value="{$lang::forum_admin_del_button}" class="btn btn-danger" />
</form>