{$jsurl {$url}/resource/wysibb/jquery.wysibb.min.js}
{$jsurl {$url}/resource/wysibb/lang/{$language}.js}
{$cssurl {$url}/resource/wysibb/theme/default/wbbtheme.css}
<script>
    $(document).ready(function () {
        $(".wysibb-editor").wysibb({img_uploadurl: "{$url}/api.php?action=commentupload", lang: "{$language}"})
    });
</script>
{$notify}
<div id="forum_post_end">
    <form class="form-inline" action="{$self_url}" method="post">
        {$if com.forum.show_title}
        {$lang::forum_create_title} <input type="text" style="width: 75%;" name="forum_title" value="{$forum_save_title}" /> <br /><br />
        {$/if}
        <textarea style="width: 95%;" placeHolder="Your message" class="wysibb-editor" rows="10" name="forum_message">{$forum_save_text}</textarea>
        <input type="submit" class="btn btn-success pull-right" value="{$lang::global_send_button}" name="forum_submit" />
    </form>
</div>
<p>&nbsp;</p>