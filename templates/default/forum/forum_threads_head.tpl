{$if com.forum.can_post}
<div>
    <a href="{$url}/forum/newthread/{$forum_item_id}" class="btn pull-right">Создать тему</a>
</div><br />
{$/if}
<div id="brdmain">
    <div id="vf" class="blocktable">
        <h2><span>{$forum_item_name}</span></h2>

        <div class="box">
            <div class="inbox">
                <table cellspacing="0">
                    <thead>
                    <tr>
                        <th class="tcl" scope="col">{$lang::forum_thread_topic}</th>
                        <th class="tc2" scope="col">{$lang::forum_thread_replaes}</th>
                        <th class="tc3" scope="col">{$lang::forum_thread_views}</th>
                        <th class="tcr" scope="col">{$lang::forum_thread_lastmsg}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {$forum_thread_item}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div>
    {$forum_threads_pagination}
</div>
