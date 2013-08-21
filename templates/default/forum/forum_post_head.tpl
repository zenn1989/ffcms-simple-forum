<div class="row">
<div class="span8">
    <div class="pull-right">{$forum_dynamic_pagination}</div>
</div>
</div>
{$forum_posts_list}
<div class="row">
    <div class="span8">
        <div class="pull-right">{$forum_dynamic_pagination}</div>
    </div>
</div>
<hr />
{$jsurl {$url}/resource/wysibb/jquery.wysibb.min.js}
{$jsurl {$url}/resource/wysibb/lang/{$language}.js}
{$cssurl {$url}/resource/wysibb/theme/default/wbbtheme.css}
{$if com.forum.can_post}
<script>
    $(document).ready(function () {
        $(".wysibb-editor").wysibb({img_uploadurl: "{$url}/api.php?action=commentupload", lang: "{$language}"})
    });
    function forumQuote(itemid) {
        $(document).ready(function() {
            var postvalue = $('#forumpost').val();
            var quotevalue = $('#post-'+itemid).text();
            $('#forumpost').val(postvalue+'[quote]'+quotevalue+'[/quote]');
        });
        return;
    }
</script>
{$notify}
<div>
    <form action="{$self_url}" method="post">
        <textarea style="width: 95%;" placeHolder="Your message" class="wysibb-editor" rows="5" name="forum_message" id="forumpost">{$forum_text}</textarea>
        <input type="submit" class="btn btn-success pull-right" value="{$lang::global_send_button}" name="forum_submit" />
    </form>
</div>
<p>&nbsp;</p>
{$/if}
{$if !com.forum.can_post}
<div class="alert alert-warning">{$lang::forum_newtopic_noauth}</div>
{$/if}