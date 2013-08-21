{$notify}
<form action="{$self_url}" method="post">
    {$lang::forum_admin_for_title}: <input type="text" name="forum_title" value="{$forum_save_title}" /><br />
    {$lang::forum_admin_for_desc}: <input type="text" name="forum_desc" value="{$forum_save_desc}" style="width: 60%" /><br />
    {$if com.forum.show_cat}
    {$lang::forum_admin_for_cat}: <select name="forum_cat">{$forum_option_cats}</select><br />
    {$/if}
    <input type="submit" name="forum_submit" value="{$lang::global_send_button}" class="btn btn-success"/>
</form>