<div class="blockpost rowodd firstpost">
    <h2><span><span class="conr">#{$forum_message_number}</span> {$forum_item_date}</span></h2>
    <div class="box">
        <div class="inbox">
            <div class="postbody">
                <div class="postleft">
                    <dl>
                        <dt><strong><a href="{$url}/user/id{$forum_user_id}">{$forum_user_nick}</a></strong></dt>
                        <dd><img src="{$url}/upload/user/avatar/small/{$forum_poster_avatar}" /></dd>
                        <dd class="usertitle">{$lang::forum_post_group}: <strong>{$forum_user_group}</strong></dd>
                        <dd><span>{$lang::forum_post_msgcount}: {$forum_user_posts}</span></dd>
                    </dl>
                </div>
                <div class="postright">
                    <div class="postmsg">
                        <p id="post-{$forum_message_number}">{$forum_item_message}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="inbox">
            <div class="postfoot clearb">
                <div class="postfootright">
                    <ul>
                        <li><span><a href="{$url}/forum/{$forum_delete_item}">Delete</a></span></li>
                        <li><span><a href="{$url}/forum/{$forum_edit_item}">Edit</a></span></li>
                        <li><span><a href="#forum_post_end" onclick="forumQuote('{$forum_message_number}')">Quote</a></span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>