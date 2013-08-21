{$if com.forum.new_in_forum}
<tr class="rowodd inew">
{$/if}
{$if !com.forum.new_in_forum}
<tr class="rowodd">
{$/if}
    <td class="tcl">
        {$if com.forum.new_in_forum}
        <div class="icon icon-new"></div>
        {$/if}
        {$if !com.forum.new_in_forum}
        <div class="icon"></div>
        {$/if}
        <div class="tclcon">
            <div>
                <h3><a href="{$url}/forum/viewforum/{$forum_item_id}">{$forum_item_title}</a> {$if com.forum.admin}<a href="{$url}/forum/deleteforum/{$forum_item_id}" title="delete forum"><i class="icon-remove"></i></a> <a href="{$url}/forum/editforum/{$forum_item_id}" title="edit forum"><i class="icon-edit"></i></a>{$/if}</h3>
                <div class="forumdesc">{$forum_item_desc}</div>
            </div>
        </div>
    </td>
    <td class="tc2">{$forum_item_threads}</td>
    <td class="tc3">{$forum_item_posts}</td>
    <td class="tcr"><a href="{$url}/forum/viewtopic/{$forum_lasttopic_id}">{$forum_last_update}</a> <span class="byuser">{$forum_last_poster}</span></td>
</tr>