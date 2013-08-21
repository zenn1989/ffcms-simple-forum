<div id="brdmain">
    <div class="blocktable">
        <h2><span>{$forum_category_name}</span> {$if com.forum.admin}<a href="{$url}/forum/deletecat/{$forum_category_id}" title="delete category"><i class="icon-remove"></i></a> <a href="{$url}/forum/editcat/{$forum_category_id}" title="edit category"><i class="icon-edit"></i></a>{$/if}</h2>
        <div class="box">
            <div class="inbox">
                <table cellspacing="0">
                    <thead>
                    <tr>
                        <th class="tcl" scope="col">{$lang::forum_cat_forum}</th>
                        <th class="tc2" scope="col">{$lang::forum_cat_thread}</th>
                        <th class="tc3" scope="col">{$lang::forum_cat_posts}</th>
                        <th class="tcr" scope="col">{$lang::forum_cat_lastmsg}</th>
                    </tr>
                    </thead>
                    <tbody>
                        {$forum_item_list}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>