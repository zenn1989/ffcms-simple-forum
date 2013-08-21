{$cssfile css/forum-flux.css}
<h3>{$lang::forum_seo_title}</h3>
<div class="pull-right"><a href="{$url}/forum/feed">{$lang::forum_main_newmsg}</a></div>
<hr />
<ul class="breadcrumb">
    <li><a href="{$url}/forum/">{$lang::forum_main_breadcrumb}</a> <span class="divider">/</span></li>
    {$forum_breadcrumbs}
</ul>
<div id="punindex" class="pun">
    {$forum_entery}
</div>
{$if com.forum.admin && com.forum.main}
<br />
<p>{$lang::forum_main_admin_act}:
    <a href="{$url}/forum/addcategory" class="btn btn-danger">{$lang::forum_main_add_cat}</a>
    <a href="{$url}/forum/addforum" class="btn btn-success">{$lang::forum_main_add_forum}</a>
</p>
{$/if}