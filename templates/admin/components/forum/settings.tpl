{% import 'macro/settings.tpl' as settingstpl %}
{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_components_forum_settings_title }}</small></h1>
<hr />
{% include 'components/forum/menu_include.tpl' %}
{% if notify.save_success %}
    {{ notifytpl.success(language.admin_extension_config_update_success) }}
{% endif %}
<form action="" method="post" class="form-horizontal" role="form">
    <fieldset>
        {{ settingstpl.textgroup('topic_in_forum', config.topic_in_forum, language.admin_components_forum_config_topiccount_title, language.admin_components_forum_config_topiccount_desc) }}
        {{ settingstpl.textgroup('post_in_topic', config.post_in_topic, language.admin_components_forum_config_postcount_title, language.admin_components_forum_config_postcount_desc) }}
        {{ settingstpl.textgroup('post_delay', config.post_delay, language.admin_components_forum_config_postdelay_title, language.admin_components_forum_config_postdelay_desc) }}

        <input type="submit" name="submit" value="{{ language.admin_extension_save_button }}" class="btn btn-success"/>
    </fieldset>
</form>