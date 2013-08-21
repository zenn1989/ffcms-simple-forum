<?php
class com_forum_back implements backend
{

    public function install()
    {
        global $language, $constant, $database;
        $dir = "forum";
        $enabled = 0;
        $stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_components (`dir`, `enabled`) VALUES (?, ?)");
        $stmt->bindParam(1, $dir, PDO::PARAM_STR);
        $stmt->bindParam(2, $enabled, PDO::PARAM_INT);
        $stmt->execute();
        $stmt = null;
        $lang_front = array(
            'ru' => array(
                'forum_delete_item' => 'Внимание! Удаление сообщения на форуме.',
                'forum_delete_msg' => 'Вы уверены что хотите удалить данное сообщение на форуме? Если это первое сообщение в теме то будут удалены все зависимые сообщения.',
                'forum_delete_btn' => 'Удалить',
                'forum_create_title' => 'Заголовок сообщения',
                'forum_newtopic_noauth' => 'Для того чтобы отвечать на темы на форуме вам необходимо зарегистрироваться',
                'forum_post_group' => 'Группа',
                'forum_post_msgcount' => 'Сообщений',
                'forum_main_newmsg' => 'Новые сообщения',
                'forum_main_breadcrumb' => 'Главная форума',
                'forum_cat_forum' => 'Форум',
                'forum_cat_thread' => 'Темы',
                'forum_cat_posts' => 'Сообщения',
                'forum_cat_lastmsg' => 'Последнее сообщение',
                'forum_thread_topic' => 'Сообщение',
                'forum_thread_replaes' => 'Ответы',
                'forum_thread_views' => 'Просмотры',
                'forum_thread_lastmsg' => 'Последний ответ',
                'forum_bread_delete' => 'Удаление сообщения',
                'forum_bread_edit' => 'Редактирование сообщения',
                'forum_add_title_incorrent' => 'Заголовок сообщения некоректен.',
                'forum_add_text_incorrent' => 'Текст сообщения некоректен.',
                'forum_bread_create' => 'Создание темы',
                'forum_error_spam' => 'Вы слишком часто отправляете сообщения на форум. Подождите.',
                'forum_seo_title' => 'Форум',
                'forum_admin_delcat' => 'Удаление категории',
                'forum_admin_addcat' => 'Добавление категории',
                'forum_admin_editcat' => 'Редактирование категории',
                'forum_admin_addforum' => 'Создание форума',
                'forum_admin_delforum' => 'Удаление форума',
                'forum_admin_editforum' => 'Редактирование форума',
                'forum_notify_update_data' => 'Форумные данные обновлены',
                'forum_main_admin_act' => 'Панель администратора',
                'forum_main_add_cat' => 'Добавить раздел',
                'forum_main_add_forum' => 'Добавить форум',
                'forum_admin_cat_name' => 'Название',
                'forum_admin_del_danger' => 'Внимание! После удаления категорию невозможно будет восстановить!',
                'forum_admin_moveto' => 'Переместить сообщения в',
                'forum_admin_del_button' => 'Удалить',
                'forum_admin_for_title' => 'Название',
                'forum_admin_for_desc' => 'Описание',
                'forum_admin_for_cat' => 'Категория',
                'forum_admin_del_for_danger' => 'Внимание! После удаления форум будет невозможно восстановить!'
            ),
            'en' => array(
                'forum_delete_item' => 'Attention! Removing message on forum!',
                'forum_delete_msg' => 'Are you sure you want to delete this message in the forum? If this is the first post in the topic will be deleted, all dependent messages.',
                'forum_delete_btn' => 'Delete',
                'forum_create_title' => 'Topic title',
                'forum_newtopic_noauth' => 'To replay on topics you must register on forum',
                'forum_post_group' => 'Group',
                'forum_post_msgcount' => 'Messages',
                'forum_main_newmsg' => 'New messages',
                'forum_main_breadcrumb' => 'Forum main',
                'forum_cat_forum' => 'Forum',
                'forum_cat_thread' => 'Topics',
                'forum_cat_posts' => 'Messages',
                'forum_cat_lastmsg' => 'Last post',
                'forum_thread_topic' => 'Message',
                'forum_thread_replaes' => 'replies',
                'forum_thread_views' => 'Views',
                'forum_thread_lastmsg' => 'Last replay',
                'forum_bread_delete' => 'Delete message',
                'forum_bread_edit' => 'Edit message',
                'forum_add_title_incorrent' => 'Topic title is incorrent',
                'forum_add_text_incorrent' => 'Topic text is incorrent',
                'forum_bread_create' => 'Create topic',
                'forum_error_spam' => 'You too often send messages to the forum. Wait.',
                'forum_seo_title' => 'Forum',
                'forum_admin_delcat' => 'Delete category',
                'forum_admin_addcat' => 'Add category',
                'forum_admin_editcat' => 'Edit category',
                'forum_admin_addforum' => 'Create forum',
                'forum_admin_delforum' => 'Delete forum',
                'forum_admin_editforum' => 'Edit forum',
                'forum_notify_update_data' => 'Forum data is updated',
                'forum_main_admin_act' => 'Admin panel',
                'forum_main_add_cat' => 'Add category',
                'forum_main_add_forum' => 'Add forum',
                'forum_admin_cat_name' => 'Name',
                'forum_admin_del_danger' => 'Attention! After remove you cant restore category data!',
                'forum_admin_moveto' => 'Move messages to',
                'forum_admin_del_button' => 'Delete',
                'forum_admin_for_title' => 'Name',
                'forum_admin_for_desc' => 'Description',
                'forum_admin_for_cat' => 'Category',
                'forum_admin_del_for_danger' => 'Attention! After remove you cant restore forum data!'
            )
        );
        $lang_back = array(
            'ru' => array(
                'admin_component_forum.name' => 'Форум',
                'admin_component_forum.desc' => 'Реализация форума на вашем сайте по адресу /forum/',
                'admin_component_forum_settings' => 'Настройки',
                'admin_component_forum_config_delay_title' => 'Задержка',
                'admin_component_forum_config_delay_desc' => 'Задержка между 2мя сообщениями от 1го пользователя в секундах'
            ),
            'en' => array(
                'admin_component_forum.name' => 'Forum',
                'admin_component_forum.desc' => 'Realise forum functional in your site on /forum/',
                'admin_component_forum_settings' => 'Settings',
                'admin_component_forum_config_delay_title' => 'Delay',
                'admin_component_forum_config_delay_desc' => 'Delay between 2 messages from 1 user in seconds'
            )
        );
        $language->addLinesLanguage($lang_front);
        $language->addLinesLanguage($lang_back, true);
        $database->con()->exec("CREATE TABLE IF NOT EXISTS `{$constant->db['prefix']}_com_forum_category` (
                              `cat_id` int(36) NOT NULL AUTO_INCREMENT,
                              `cat_name` varchar(512) NOT NULL,
                              PRIMARY KEY (`cat_id`)
                            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
        $database->con()->exec("CREATE TABLE IF NOT EXISTS `{$constant->db['prefix']}_com_forum_item` (
                              `forum_id` int(36) NOT NULL AUTO_INCREMENT,
                              `forum_name` varchar(512) NOT NULL,
                              `forum_desc` varchar(2048) NOT NULL,
                              `depend_id` int(36) NOT NULL DEFAULT '0',
                              `category` int(36) NOT NULL,
                              `forum_threads` int(36) NOT NULL DEFAULT '0',
                              `forum_posts` int(36) NOT NULL DEFAULT '0',
                              `forum_lastposter` varchar(36) NOT NULL,
                              `forum_lastupdate` int(16) NOT NULL,
                              `forum_lasttopic_id` int(36) NOT NULL,
                              PRIMARY KEY (`forum_id`)
                            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
        $database->con()->exec("CREATE TABLE IF NOT EXISTS `{$constant->db['prefix']}_com_forum_post` (
                              `post_id` int(64) NOT NULL AUTO_INCREMENT,
                              `post_target_thread` int(32) NOT NULL,
                              `post_message` text NOT NULL,
                              `post_userid` int(32) NOT NULL,
                              `post_time` int(16) NOT NULL,
                              PRIMARY KEY (`post_id`)
                            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
        $database->con()->exec("CREATE TABLE IF NOT EXISTS `{$constant->db['prefix']}_com_forum_thread` (
                              `thread_id` int(64) NOT NULL AUTO_INCREMENT,
                              `thread_title` varchar(256) NOT NULL,
                              `thread_body` text NOT NULL,
                              `thread_starttime` int(16) NOT NULL,
                              `thread_updatetime` int(16) NOT NULL,
                              `thread_owner` varchar(64) NOT NULL,
                              `thread_important` int(1) NOT NULL DEFAULT '0',
                              `thread_updater` varchar(36) NOT NULL,
                              `thread_starterid` int(36) NOT NULL,
                              `thread_forum_depend` int(36) NOT NULL,
                              `thread_post_count` int(16) NOT NULL,
                              `thread_view_count` int(36) NOT NULL,
                              PRIMARY KEY (`thread_id`)
                            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
        $database->con()->exec("ALTER TABLE {$constant->db['prefix']}_user_custom ADD `forum_posts` INT(32) NOT NULL DEFAULT '0'");
        $database->con()->exec("ALTER TABLE {$constant->db['prefix']}_user_custom ADD `forum_last_message` INT(16) NOT NULL DEFAULT '0'");
    }

    public function load()
    {
        global $template, $admin, $language, $system;
        if ($admin->getAction() == "turn") {
            return $admin->turn();
        }
        $config_pharse = null;
        $work_body = null;
        $action_page_title = $admin->getExtName() . " : ";
        $stmt = null;
        if($admin->getAction() == null || $admin->getAction() == "settings") {
            $action_page_title .= $language->get('admin_component_forum_settings');

            if ($system->post('submit')) {
                $save_try = $admin->trySaveConfigs();
                if ($save_try)
                    $work_body .= $template->stringNotify('success', $language->get('admin_extension_config_update_success'), true);
                else
                    $work_body .= $template->stringNotify('error', $language->get('admin_extension_config_update_fail'), true);;
            }

            $config_form = $template->get('config_form');
            $config_set = null;

            $config_set .= $admin->tplSettingsInputText('config:post_delay', $admin->getConfig('post_delay', 'int'), $language->get('admin_component_forum_config_delay_title'), $language->get('admin_component_forum_config_delay_desc'));
            $work_body .= $template->assign('ext_form', $config_set, $config_form);
        }
        $menu_theme = $template->get('config_menu');
        $menu_link = null;
        $menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id=' . $admin->getID() . '&action=settings', $language->get('admin_component_forum_settings')), $menu_theme);
        $body_form = $template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $template->get('config_head'));
        return $body_form;

    }
}


?>