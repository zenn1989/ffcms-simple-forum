{$notify}
<form action="{$self_url}" method="post">
    {$lang::forum_admin_cat_name}: <input type="text" name="forum_title" value="{$forum_save_title}" /><br />
    <input type="submit" name="forum_submit" value="{$lang::global_send_button}" class="btn btn-success"/>
</form>