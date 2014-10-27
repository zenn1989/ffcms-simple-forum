<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\system;
use engine\template;
use engine\admin;
use engine\database;
use engine\property;
use engine\extension;
use engine\language;

class components_forum_back extends \engine\singleton {
	
	public function _update($from) {
        if($from == '1.0.1') {
            $ru_data = array('ru' =>
                array('front' =>
                    array(
                        'forum_threadmove_title' => 'Перемещение ветки',
                        'forum_threadmove_desc' => 'Выберите новый раздел для данной ветки сообщений',
                        'forum_threadmove_label_moveto' => 'Раздел',
                        'forum_threadmove_btn_move' => 'Переместить',
                        'forum_threadmove_btn_cancel' => 'Отмена'
                    )));
            $en_data = array('en' =>
                array('front' =>
                    array(
                        'forum_threadmove_title' => 'Move thread',
                        'forum_threadmove_desc' => 'Select new forum for this thread',
                        'forum_threadmove_label_moveto' => 'Forum',
                        'forum_threadmove_btn_move' => 'Move',
                        'forum_threadmove_btn_cancel' => 'Cancel'
                    )));
            language::getInstance()->add($ru_data);
            language::getInstance()->add($en_data);
        }
        database::getInstance()->con()->query("UPDATE ".property::getInstance()->get('db_prefix')."_extensions SET `version` = '1.0.2', `compatable` = '2.0.4' WHERE dir = 'forum' AND type = 'components'");
	}
	
	public function _version() {
        return '1.0.2';
    }

    public function _compatable() {
        return '2.0.4';
    }

    public function _accessData() {
        return array(
            'admin/components/forum',
            'admin/components/forum/list',
            'admin/components/forum/catedit',
            'admin/components/forum/catdel',
            'admin/components/forum/forumedit',
            'admin/components/forum/forumdel',
            'admin/components/forum/catadd',
            'admin/components/forum/forumadd',
            'admin/components/forum/settings',
        );
    }

    public function make() {
        $content = null;
        switch(system::getInstance()->get('make')) {
            case null:
            case 'list':
                $content = $this->viewForumCategorys();
                break;
            case 'forumedit':
                $content = $this->viewForumEdit();
                break;
            case 'catedit':
                $content = $this->viewCategoryEdit();
                break;
            case 'forumadd':
                $content = $this->viewForumAdd();
                break;
            case 'catadd':
                $content = $this->viewCategoryAdd();
                break;
            case 'forumdel':
                $content = $this->viewForumDel();
                break;
            case 'catdel':
                $content = $this->viewCategoryDel();
                break;
            case 'settings':
                $content = $this->viewSettings();
                break;
        }

        template::getInstance()->set(template::TYPE_CONTENT, 'body', $content);
    }

    private function viewSettings() {
        $params = array();
        if(system::getInstance()->post('submit')) {
            if(admin::getInstance()->saveExtensionConfigs()) {
                $params['notify']['save_success'] = true;
            }
        }
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $params['config']['topic_in_forum'] = extension::getInstance()->getConfig('topic_in_forum', 'forum', extension::TYPE_COMPONENT, 'int');
        $params['config']['post_in_topic'] = extension::getInstance()->getConfig('post_in_topic', 'forum', extension::TYPE_COMPONENT, 'int');
        $params['config']['post_delay'] = extension::getInstance()->getConfig('post_delay', 'forum', extension::TYPE_COMPONENT, 'int');

        return template::getInstance()->twigRender('components/forum/settings.tpl', $params);
    }

    private function viewForumCategorys() {
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $stmt = database::getInstance()->con()->query("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_forum_category");
        $resultCat = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;
        $stmt = database::getInstance()->con()->query("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_forum_item WHERE depend_id = 0");
        $resultForum = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;
        foreach($resultCat as $cat) {
            $forum_body = null;
            $order_id = $cat['order_id'];

            $params['forum'][$order_id]['id'] = $cat['cat_id'];
            $params['forum'][$order_id]['name'] = $cat['cat_name'];

            foreach($resultForum as $forum) {
                if($forum['category'] == $cat['cat_id']) {
                    $params['forum'][$order_id]['forums'][] = array(
                        'id' => $forum['forum_id'],
                        'title' => $forum['forum_name'],
                        'desc' => $forum['forum_desc'],
                    );
                }
            }
        }

        if(sizeof($params['forum']) > 0)
            ksort($params['forum']);

        return template::getInstance()->twigRender('components/forum/list.tpl', $params);
    }

    private function viewForumEdit() {
        $forum_id = (int)system::getInstance()->get('id');
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_forum_item WHERE forum_id = ?");
        $stmt->bindParam(1, $forum_id, \PDO::PARAM_INT);
        $stmt->execute();
        if($stmt->rowCount() == 1) {
            $res = $stmt->fetch();
            $params['forum']['title'] = system::getInstance()->nohtml($res['forum_name']);
            $params['forum']['desc'] = system::getInstance()->nohtml($res['forum_desc']);
            if(system::getInstance()->post('submit') && system::getInstance()->length(system::getInstance()->post('forum_title')) > 0) {
                $save_title = system::getInstance()->nohtml(system::getInstance()->post('forum_title'));
                $save_desc = system::getInstance()->nohtml(system::getInstance()->post('forum_desc'));
                $stmt = null;
                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_forum_item SET
                        forum_name = ?, forum_desc = ? WHERE forum_id = ?");
                $stmt->bindParam(1, $save_title, \PDO::PARAM_STR);
                $stmt->bindParam(2, $save_desc, \PDO::PARAM_STR);
                $stmt->bindParam(3, $forum_id, \PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                system::getInstance()->redirect($_SERVER['PHP_SELF']."?object=components&action=forum");
            }
        } else {
            system::getInstance()->redirect($_SERVER['PHP_SELF']."?object=components&action=forum");
        }

        return template::getInstance()->twigRender('components/forum/edit_forum.tpl', $params);
    }

    private function viewCategoryEdit() {
        $cat_id = (int)system::getInstance()->get('id');
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_forum_category WHERE cat_id = ?");
        $stmt->bindParam(1, $cat_id, \PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() == 1) {
            $res = $stmt->fetch();
            $stmt = null;
            $params['forum']['category']['title'] = $res['cat_name'];
            $params['forum']['category']['order'] = $res['order_id'];
            if(system::getInstance()->post('submit') && system::getInstance()->length(system::getInstance()->post('cat_title')) > 0) {
                $title = system::getInstance()->nohtml(system::getInstance()->post('cat_title'));
                $order_index = $this->getOrderidCategory((int)system::getInstance()->post('cat_order'), $params['forum']['category']['order']);
                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_forum_category SET cat_name = ?,order_id = ? WHERE cat_id = ?");
                $stmt->bindParam(1, $title, \PDO::PARAM_STR);
                $stmt->bindParam(2, $order_index, \PDO::PARAM_INT);
                $stmt->bindParam(3, $cat_id, \PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                system::getInstance()->redirect($_SERVER['PHP_SELF']."?object=components&action=forum");
            }
        } else {
            system::getInstance()->redirect($_SERVER['PHP_SELF']."?object=components&action=forum");
        }

        return template::getInstance()->twigRender('components/forum/edit_category.tpl', $params);
    }

    private function viewForumAdd() {
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $params['options']['is_add'] = true;

        $stmt = database::getInstance()->con()->query("SELECT cat_id,cat_name FROM ".property::getInstance()->get('db_prefix')."_com_forum_category ORDER BY order_id DESC");

        $params['forum']['categorys'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;

        $available_ids = system::getInstance()->extractFromMultyArray('cat_id', $params['forum']['categorys']);

        if(system::getInstance()->post('submit') && system::getInstance()->post('category_id') > 0 && system::getInstance()->length(system::getInstance()->post('forum_title')) > 0) {
            $post_cat_id = (int)system::getInstance()->post('category_id');
            $post_cat_name = system::getInstance()->nohtml(system::getInstance()->post('forum_title'));
            $post_cat_desc = system::getInstance()->nohtml(system::getInstance()->post('forum_desc'));
            if(in_array($post_cat_id, $available_ids)) { // category is available
                $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_com_forum_item
                    (forum_name, forum_desc, depend_id, category, forum_threads, forum_posts, forum_lastposter, forum_lastupdate, forum_lasttopic_id) VALUES
                     (?, ?, 0, ?, 0, 0, '', 0, 0)");
                $stmt->bindParam(1, $post_cat_name, \PDO::PARAM_STR);
                $stmt->bindParam(2, $post_cat_desc, \PDO::PARAM_STR);
                $stmt->bindParam(3, $post_cat_id, \PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                system::getInstance()->redirect($_SERVER['PHP_SELF']."?object=components&action=forum");
            }
        }

        return template::getInstance()->twigRender('components/forum/edit_forum.tpl', $params);
    }

    private function viewCategoryAdd() {
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $params['options']['is_add'] = true;

        if(system::getInstance()->post('submit') && system::getInstance()->length(system::getInstance()->post('cat_title')) > 0) {
            $order_id = $this->getOrderidCategory((int)system::getInstance()->post('cat_order'));
            $cat_title = system::getInstance()->nohtml(system::getInstance()->post('cat_title'));
            $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_com_forum_category (cat_name,order_id) VALUES (?, ?)");
            $stmt->bindParam(1, $cat_title, \PDO::PARAM_INT);
            $stmt->bindParam(2, $order_id, \PDO::PARAM_INT);
            $stmt->execute();
            system::getInstance()->redirect($_SERVER['PHP_SELF']."?object=components&action=forum");
        }

        return template::getInstance()->twigRender('components/forum/edit_category.tpl', $params);
    }

    private function getOrderidCategory($set_order, $default_order = 0) {
        if($set_order == $default_order && $set_order != 0)
            return $set_order;
        if($set_order == 0)
            $set_order++;

        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) AS count FROM ".property::getInstance()->get('db_prefix')."_com_forum_category WHERE order_id = ?");
        $stmt->bindParam(1, $set_order, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt = null;
        $order_new_id = null;
        if($result['count'] > 0) {
            // order is always taken
            $stmt = database::getInstance()->con()->query("SELECT MAX(order_id) FROM ".property::getInstance()->get('db_prefix')."_com_forum_category");
            $res = $stmt->fetch();
            $stmt = null;
            $order_new_id = $res[0] + 1; // max order_id ++
        } else {
            $order_new_id = $set_order;
        }
        return $order_new_id;
    }

    private function viewForumDel() {
        $forum_id = (int)system::getInstance()->get('id');
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_forum_item WHERE forum_id = ?");
        $stmt->bindParam(1, $forum_id, \PDO::PARAM_INT);
        $stmt->execute();
        if($stmt->rowCount() == 1) {
            $res = $stmt->fetch();
            $stmt = null;
            $params['forum']['title'] = $res['forum_name'];
            $params['forum']['posts'] = $res['forum_posts'];
            $params['forum']['threads'] = $res['forum_threads'];
            $stmt = database::getInstance()->con()->prepare("SELECT a.forum_id,a.forum_name,a.category,b.cat_name FROM ".property::getInstance()->get('db_prefix')."_com_forum_item a,
                ".property::getInstance()->get('db_prefix')."_com_forum_category b WHERE a.category = b.cat_id AND a.forum_id != ?");
            $stmt->bindParam(1, $forum_id, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $stmt = null;
            foreach($result as $row) {
                $params['forum']['moveto'][] = array(
                    'forum_id' => $row['forum_id'],
                    'forum_name' => $row['forum_name'],
                    'cat_name' => $row['cat_name']
                );
            }
            if(system::getInstance()->post('submit')) {
                $move_to_id = (int)system::getInstance()->post('forum_moveto');
                if($move_to_id == 0) {
                    // don't save post and topics, remove post, then threads
                    $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_com_forum_post WHERE post_target_thread
                        in (SELECT thread_id FROM ".property::getInstance()->get('db_prefix')."_com_forum_thread WHERE thread_forum_depend = ?)");
                    $stmt->bindParam(1, $forum_id, \PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                    $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_com_forum_thread WHERE thread_forum_depend = ?");
                    $stmt->bindParam(1, $forum_id, \PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                } else {
                    // update recipient post_count and thread_count
                    $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_forum_item SET
                            forum_threads = forum_threads+?, forum_posts = forum_posts+? WHERE forum_id = ?");
                    $stmt->bindParam(1, $params['forum']['threads'], \PDO::PARAM_INT);
                    $stmt->bindParam(2, $params['forum']['posts'], \PDO::PARAM_INT);
                    $stmt->bindParam(3, $move_to_id, \PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                    // update info in topics - forum_depend_id
                    $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_forum_thread SET thread_forum_depend = ? WHERE thread_forum_depend = ?");
                    $stmt->bindParam(1, $move_to_id, \PDO::PARAM_INT);
                    $stmt->bindParam(2, $forum_id, \PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                }
                $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_com_forum_item WHERE forum_id = ?");
                $stmt->bindParam(1, $forum_id, \PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                system::getInstance()->redirect($_SERVER['PHP_SELF']."?object=components&action=forum");
            }
        } else {
            system::getInstance()->redirect($_SERVER['PHP_SELF']."?object=components&action=forum");
        }
        return template::getInstance()->twigRender('components/forum/del_forum.tpl', $params);
    }

    private function viewCategoryDel() {
        $cat_id = (int)system::getInstance()->get('id');
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_forum_category WHERE cat_id = ?");
        $stmt->bindParam(1, $cat_id, \PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() == 1) {
            $res = $stmt->fetch();
            $stmt = null;
            $params['forum']['category']['title'] = $res['cat_name'];
            $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_forum_item WHERE category = ?");
            $stmt->bindParam(1, $cat_id, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            $stmt = null;
            if($result[0] == 0) { // no forums in category
                if(system::getInstance()->post('submit')) {
                    $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_com_forum_category WHERE cat_id = ?");
                    $stmt->bindParam(1, $cat_id, \PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                    system::getInstance()->redirect($_SERVER['PHP_SELF']."?object=components&action=forum");
                }
            } else {
                $params['notify']['category']['not_empty'] = true;
            }
        } else {
            system::getInstance()->redirect($_SERVER['PHP_SELF']."?object=components&action=forum");
        }

        return template::getInstance()->twigRender('components/forum/del_cat.tpl', $params);
    }

    public function _install() {
        $ru_data = array('ru' =>
            array('front' =>
                array('forum_global_title' => 'Форум',
                    'forum_global_empty' => 'Нет информации',
                    'forum_breadcrumb_general' => 'Главная страница',
                    'forum_breadcrumb_main' => 'Форум',
                    'forum_breadcrumb_newthread' => 'Новая тема',
                    'forum_breadcrumb_updates' => 'Обновления',
                    'forum_tab_main' => 'Форумы',
                    'forum_tab_updates' => 'Обновления',
                    'forum_list_part' => 'Форум',
                    'forum_list_topic' => 'Темы',
                    'forum_list_posts' => 'Посты',
                    'forum_list_lastmsg' => 'Последнее сообщение',
                    'forum_list_empty' => 'Список форумов еще не задан, обратитесь к управлению компонентом.',
                    'forum_item_category' => 'Раздел',
                    'forum_item_addthread' => 'Начать тему',
                    'forum_item_topic' => 'Тема',
                    'forum_item_answers' => 'Ответы',
                    'forum_item_views' => 'Просмотры',
                    'forum_item_update' => 'Обновление',
                    'forum_item_empty' => 'В данном форуме еще нет сообщений, ваше может стать первым!',
                    'forum_topic_name' => 'Тема',
                    'forum_topic_messages' => 'Сообщений',
                    'forum_topic_spamattempt' => 'Вы отправляете сообщения слишком часто. Подождите...',
                    'forum_topic_addmessage' => 'Добавить',
                    'forum_addthread_title' => 'Начать тему',
                    'forum_addthread_error_title' => 'Длина заголовка вашего сообщения короче 3 или более 120 символов',
                    'forum_addthread_error_text' => 'Длина текста сообщения короче 10 символов',
                    'forum_addthread_error_spam' => 'Вы слишком часто отправляете сообщения. Подождите...',
                    'forum_addthread_form_title' => 'Заголовок',
                    'forum_addthread_form_submit' => 'Добавить',
                    'forum_postdel_title' => 'Удаление поста',
                    'forum_postdel_desc' => 'Вы уверены что хотите удалить этот пост? Больше предупреждений не будет.',
                    'forum_postdel_submit' => 'Удалить',
                    'forum_postdel_cancel' => 'Отмена',
                    'forum_postedit_title' => 'Редактирование сообщения',
                    'forum_postedit_submit' => 'Сохранить',
                    'forum_postedit_cancel' => 'Отмена',
                    'forum_setimportant_title' => 'Назначить важным',
                    'forum_setimportant_desc' => 'Вы уверены что хотите назначить данную ветку важной? Больше предупреждений не будет!',
                    'forum_setimportant_submit' => 'Назначить важным',
                    'forum_setimportant_cancel' => 'Отмена',
                    'forum_stream_title' => 'Лента активности',
                    'forum_stream_create' => 'создает тему',
                    'forum_stream_answer' => 'отвечает в теме',
                    'forum_threaddel_title' => 'Удаление ветки',
                    'forum_threaddel_desc' => 'Вы уверены что хотите удалить эту ветку сообщений? Больше предупреждений не будет.',
                    'forum_threaddel_submit' => 'Удалить',
                    'forum_threaddel_cancel' => 'Отмена',
                    'forum_threadedit_title' => 'Начать тему',
                    'forum_threadedit_form_title' => 'Заголовок',
                    'forum_threadedit_submit' => 'Сохранить',
                    'forum_threadedit_cancel' => 'Отмена',
                    'forum_unsetimportant_title' => 'Снять отметку важный',
                    'forum_unsetimportant_desc' => 'Вы уверены что хотите снять с данной темы отметку "важный"? Больше предупреждений не будет!',
                    'forum_unsetimportant_submit' => 'Снять отметку',
                    'forum_unsetimportant_cancel' => 'Отмена',
                    'forum_notify_blocked' => 'Вы заблокированы на форуме и не можете добавлять сообщения',
                    'forum_notify_regmsg' => 'Зарегистрируйтесь на сайте чтобы иметь возможность добавлять сообщения',
                    'forum_category_empty' => 'В данном разделе еще нет активных форумов',
                    'forum_threadmove_title' => 'Перемещение ветки',
                    'forum_threadmove_desc' => 'Выберите новый раздел для данной ветки сообщений',
                    'forum_threadmove_label_moveto' => 'Раздел',
                    'forum_threadmove_btn_move' => 'Переместить',
                    'forum_threadmove_btn_cancel' => 'Отмена'
                ),
            'back' => array(
                'admin_components_forum.name' => 'Простой Форум',
                'admin_components_forum.desc' => 'Реализация простого форума(/forum/) для сайта',
                'admin_components_forum_category_title' => 'Разделы',
                'admin_components_forum_settings_title' => 'Настройки',
                'admin_components_forum_editforum_title' => 'Редактирование форума',
                'admin_components_forum_editcat_title' => 'Редактирование категории',
                'admin_components_forum_delforum_title' => 'Удаление форума',
                'admin_components_forum_delcat_title' => 'Удаление раздела',
                'admin_components_forum_config_topiccount_title' => 'Количество тем',
                'admin_components_forum_config_topiccount_desc' => 'Количество тем, отображаемых в форуме на 1 странице',
                'admin_components_forum_config_postcount_title' => 'Количество постов',
                'admin_components_forum_config_postcount_desc' => 'Количество сообщений, отображаемых на 1 странице темы',
                'admin_components_forum_config_postdelay_title' => 'Задержка',
                'admin_components_forum_config_postdelay_desc' => 'Задержка между 2мя сообщениями от одного пользователя в секундах',
                'admin_components_forum_delcat_desc' => 'Вы уверены что хотите удалить данную категорию?',
                'admin_components_forum_delcat_cattitle' => 'Название',
                'admin_components_forum_delcat_notify_notempty' => 'Категория не пуста. Удалите все форумы из категории.',
                'admin_components_forum_delcat_button_submit' => 'Удалить',
                'admin_components_forum_delforum_desc' => 'Вы уверены что хотите удалить данный форум?',
                'admin_components_forum_delforum_forumtitle' => 'Название',
                'admin_components_forum_delforum_recepient' => 'Приемник',
                'admin_components_forum_delforum_opt_delall' => 'Удалить все темы и посты',
                'admin_components_forum_delforum_rec_desc' => 'Выберите форум, в который необходимо переместить все материалы из удаляемого форума',
                'admin_components_forum_delforum_button_submit' => 'Удалить',
                'admin_components_forum_editcat_cattitle' => 'Название',
                'admin_components_forum_editcat_priority' => 'Приоритет',
                'admin_components_forum_editcat_button_submit' => 'Сохранить',
                'admin_components_forum_editforum_catown_title' => 'Раздел',
                'admin_components_forum_editforum_forumtitle' => 'Название',
                'admin_components_forum_editforum_forumdesc' => 'Описание',
                'admin_components_forum_editforum_button_submit' => 'Сохранить',
                'admin_components_forum_list_add_cat' => 'Добавить раздел',
                'admin_components_forum_list_add_forum' => 'Добавить форум',
                'admin_components_forum_list_cat_title' => 'Раздел',
                'admin_components_forum_list_empty' => 'Форумов в данном разделе еще нет.',
            )));
        $en_data = array('en' =>
            array('front' =>
                array('forum_global_title' => 'Forum',
                    'forum_global_empty' => 'No information',
                    'forum_breadcrumb_general' => 'Main page',
                    'forum_breadcrumb_main' => 'Forum',
                    'forum_breadcrumb_newthread' => 'New topic',
                    'forum_breadcrumb_updates' => 'Updates',
                    'forum_tab_main' => 'Forums',
                    'forum_tab_updates' => 'Updates',
                    'forum_list_part' => 'Forum',
                    'forum_list_topic' => 'Topic',
                    'forum_list_posts' => 'Posts',
                    'forum_list_lastmsg' => 'Last post',
                    'forum_list_empty' => 'List of forums are not set, use component control.',
                    'forum_item_category' => 'Category',
                    'forum_item_addthread' => 'Add topic',
                    'forum_item_topic' => 'Subject',
                    'forum_item_answers' => 'Answers',
                    'forum_item_views' => 'Views',
                    'forum_item_update' => 'Update',
                    'forum_item_empty' => 'In this forum yet, there are no messages, your may be the first!',
                    'forum_topic_name' => 'Subject',
                    'forum_topic_messages' => 'Message',
                    'forum_topic_spamattempt' => 'You are sending messages too fast. Wait...',
                    'forum_topic_addmessage' => 'Add',
                    'forum_addthread_title' => 'Start a topic',
                    'forum_addthread_error_title' => 'The length of the title of your message shorter than 3 or more than 120 characters',
                    'forum_addthread_error_text' => 'The length of the message text is shorter than 10 characters',
                    'forum_addthread_error_spam' => 'You are too fast sending messages. Wait...',
                    'forum_addthread_form_title' => 'Title',
                    'forum_addthread_form_submit' => 'Add',
                    'forum_postdel_title' => 'Delete post',
                    'forum_postdel_desc' => 'Are you really want to delete this post? No more alerts will be displayed.',
                    'forum_postdel_submit' => 'Delete',
                    'forum_postdel_cancel' => 'Cancel',
                    'forum_postedit_title' => 'Edit message',
                    'forum_postedit_submit' => 'Save',
                    'forum_postedit_cancel' => 'Cancel',
                    'forum_setimportant_title' => 'Set important',
                    'forum_setimportant_desc' => 'Are you really want to set this topic important? More alerts will not be!',
                    'forum_setimportant_submit' => 'Set important',
                    'forum_setimportant_cancel' => 'Cancel',
                    'forum_stream_title' => 'Activity feed',
                    'forum_stream_create' => 'create a topic',
                    'forum_stream_answer' => 'answers in the topic',
                    'forum_threaddel_title' => 'Delete thrad',
                    'forum_threaddel_desc' => 'Are you sure to delete this message thread? No more alert will be displayed.',
                    'forum_threaddel_submit' => 'Delete',
                    'forum_threaddel_cancel' => 'Cancel',
                    'forum_threadedit_title' => 'Start a topic',
                    'forum_threadedit_form_title' => 'Title',
                    'forum_threadedit_submit' => 'Save',
                    'forum_threadedit_cancel' => 'Cancel',
                    'forum_unsetimportant_title' => 'Unset important',
                    'forum_unsetimportant_desc' => 'Are you sure to remove from the topic marker "important"? More warnings will not be!',
                    'forum_unsetimportant_submit' => 'Unset',
                    'forum_unsetimportant_cancel' => 'Cancel',
                    'forum_notify_blocked' => 'You are blocked on the forum and can not add messages',
                    'forum_notify_regmsg' => 'Register on the our website to be able to add messages',
                    'forum_category_empty' => 'This category is still have no active forums',
                    'forum_threadmove_title' => 'Move thread',
                    'forum_threadmove_desc' => 'Select new forum for this thread',
                    'forum_threadmove_label_moveto' => 'Forum',
                    'forum_threadmove_btn_move' => 'Move',
                    'forum_threadmove_btn_cancel' => 'Cancel'
                ),
            'back' => array(
                'admin_components_forum.name' => 'Simple forum',
                'admin_components_forum.desc' => 'Simple component forum(/forum/) for customers',
                'admin_components_forum_category_title' => 'Management',
                'admin_components_forum_settings_title' => 'Settings',
                'admin_components_forum_editforum_title' => 'Edit forum',
                'admin_components_forum_editcat_title' => 'Edit category',
                'admin_components_forum_delforum_title' => 'Delete forum',
                'admin_components_forum_delcat_title' => 'Delete category',
                'admin_components_forum_config_topiccount_title' => 'Topic count',
                'admin_components_forum_config_topiccount_desc' => 'Number of topics displayed in the forum on 1 page',
                'admin_components_forum_config_postcount_title' => 'Post count',
                'admin_components_forum_config_postcount_desc' => 'Number of messages displayed in the forum on 1 page themes',
                'admin_components_forum_config_postdelay_title' => 'Delay',
                'admin_components_forum_config_postdelay_desc' => 'Delay between 2 messages from one user in seconds',
                'admin_components_forum_delcat_desc' => 'Are you sure want to delete this category?',
                'admin_components_forum_delcat_cattitle' => 'Title',
                'admin_components_forum_delcat_notify_notempty' => 'Category is not empty. Remove all forums from category.',
                'admin_components_forum_delcat_button_submit' => 'Delete',
                'admin_components_forum_delforum_desc' => 'Are you sure to delete this forum?',
                'admin_components_forum_delforum_forumtitle' => 'Title',
                'admin_components_forum_delforum_recepient' => 'Receiver',
                'admin_components_forum_delforum_opt_delall' => 'Delete all topics and posts',
                'admin_components_forum_delforum_rec_desc' => 'Select the recepient forum, where we must move all topics and posts',
                'admin_components_forum_delforum_button_submit' => 'Delete',
                'admin_components_forum_editcat_cattitle' => 'Title',
                'admin_components_forum_editcat_priority' => 'Priority',
                'admin_components_forum_editcat_button_submit' => 'Save',
                'admin_components_forum_editforum_catown_title' => 'Category',
                'admin_components_forum_editforum_forumtitle' => 'Title',
                'admin_components_forum_editforum_forumdesc' => 'Description',
                'admin_components_forum_editforum_button_submit' => 'Save',
                'admin_components_forum_list_add_cat' => 'Add category',
                'admin_components_forum_list_add_forum' => 'Add forum',
                'admin_components_forum_list_cat_title' => 'Category',
                'admin_components_forum_list_empty' => 'Forums in this section not founded.',
            )));
        language::getInstance()->add($ru_data);
        language::getInstance()->add($en_data);
        $sql_query = "CREATE TABLE IF NOT EXISTS ".property::getInstance()->get('db_prefix')."_com_forum_category (
                              `cat_id` int(36) NOT NULL AUTO_INCREMENT,
                              `cat_name` varchar(512) NOT NULL,
							  `order_id` int(8) NOT NULL DEFAULT 0,
                              PRIMARY KEY (`cat_id`)
                            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
                      CREATE TABLE IF NOT EXISTS ".property::getInstance()->get('db_prefix')."_com_forum_item (
                              `forum_id` int(36) NOT NULL AUTO_INCREMENT,
                              `forum_name` varchar(512) NOT NULL,
                              `forum_desc` varchar(2048) NOT NULL,
                              `depend_id` int(36) NOT NULL DEFAULT '0',
                              `category` int(36) NOT NULL,
                              `forum_threads` int(36) NOT NULL DEFAULT '0',
                              `forum_posts` int(36) NOT NULL DEFAULT '0',
                              `forum_lastposter` varchar(36) NOT NULL default '',
                              `forum_lastupdate` int(16) NOT NULL default '0',
                              `forum_lasttopic_id` int(36) NOT NULL default '0',
                              PRIMARY KEY (`forum_id`)
                            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
                      CREATE TABLE IF NOT EXISTS ".property::getInstance()->get('db_prefix')."_com_forum_post (
                              `post_id` int(64) NOT NULL AUTO_INCREMENT,
                              `post_target_thread` int(32) NOT NULL,
                              `post_message` text NOT NULL,
                              `post_userid` int(32) NOT NULL,
                              `post_time` int(16) NOT NULL,
                              PRIMARY KEY (`post_id`)
                            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
                      CREATE TABLE IF NOT EXISTS ".property::getInstance()->get('db_prefix')."_com_forum_thread (
                              `thread_id` int(64) NOT NULL AUTO_INCREMENT,
                              `thread_title` varchar(256) NOT NULL default '',
                              `thread_body` text NOT NULL default '',
                              `thread_starttime` int(16) NOT NULL default '0',
                              `thread_updatetime` int(16) NOT NULL default '0',
                              `thread_owner` varchar(64) NOT NULL default '',
                              `thread_important` int(1) NOT NULL DEFAULT '0',
                              `thread_updater` varchar(36) NOT NULL default '',
                              `thread_starterid` int(36) NOT NULL default '0',
                              `thread_forum_depend` int(36) NOT NULL default '0',
                              `thread_post_count` int(16) NOT NULL default '0',
                              `thread_view_count` int(36) NOT NULL default '0',
                              PRIMARY KEY (`thread_id`)
                            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
                      ALTER TABLE ".property::getInstance()->get('db_prefix')."_user_custom ADD `forum_posts` INT(32) NOT NULL DEFAULT '0';
                      ALTER TABLE ".property::getInstance()->get('db_prefix')."_user_custom ADD `forum_last_message` INT(16) NOT NULL DEFAULT '0';";
        database::getInstance()->con()->exec($sql_query);
        database::getInstance()->con()->query("UPDATE ".property::getInstance()->get('db_prefix')."_user_access_level SET permissions = CONCAT(permissions,';forum/post;forum/thread') WHERE group_id = 1");
        database::getInstance()->con()->query("UPDATE ".property::getInstance()->get('db_prefix')."_user_access_level SET permissions = CONCAT(permissions,';forum/post;forum/thread;forum/moderator') WHERE group_id = 2");
        database::getInstance()->con()->query("UPDATE ".property::getInstance()->get('db_prefix')."_user_access_level SET permissions = CONCAT(permissions,';forum/post;forum/thread;forum/moderator') WHERE group_id = 3");
        $configs = 'a:3:{s:14:"topic_in_forum";s:2:"10";s:13:"post_in_topic";s:2:"10";s:10:"post_delay";s:2:"60";}';
        $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_extensions SET configs = ? WHERE dir = 'forum' AND type = 'components'");
        $stmt->bindParam(1, $configs, \PDO::PARAM_STR);
        $stmt->execute();
        $stmt = null;
    }
}