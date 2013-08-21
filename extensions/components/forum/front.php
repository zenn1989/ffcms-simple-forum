<?php

if (!extension::registerPathWay('forum', 'forum')) {
    exit("Component forum cannot be registered!");
}

class com_forum_front implements com_front
{
    private $threads_on_page = 10;
    private $post_on_page = 10;
    private $pathback = null;

    public function load()
    {
        global $page, $template, $meta, $rule, $user, $language;
        $way = $page->getPathway();
        $content = null;
        $meta->add('title', $language->get('forum_seo_title'));
        if($user->get('access_to_admin') > 0) {
            $rule->add('com.forum.admin', true);
        }
        switch($way[1]) {
            case null:
            case "list":
                // список разделов и форумов
                $rule->add('com.forum.main', true);
                $content = $this->viewForumList();
                break;
            case "viewforum":
                // список топиков в форуме
                $content = $this->viewForumItem();
                break;
            case "viewtopic":
                // список сообщений в триде
                $content = $this->viewTopicItem();
                break;
            case "newthread":
                $content = $this->viewAddThread();
                break;
            case "deletethread":
                $content = $this->viewDeleteThread();
                break;
            case "deletepost":
                $content = $this->viewDeletePost();
                break;
            case "editthread":
                $content = $this->viewEditThread();
                break;
            case "editpost":
                $content = $this->viewEditPost();
                break;
            case "editforum":
                $content = $this->adminEditForum();
                break;
            case "deleteforum":
                $content = $this->adminDeleteForum();
                break;
            case "addforum":
                $content = $this->adminAddForum();
                break;
            case "editcat":
                $content = $this->adminEditCat();
                break;
            case "addcategory":
                $content = $this->adminAddCat();
                break;
            case "deletecat":
                $content = $this->adminDeleteCat();
                break;
            default:
                $content = $template->compile404();
                break;
        }
        $page->setContentPosition('body', $content);
    }

    private function adminDeleteCat()
    {
        global $template, $database, $constant, $page, $system, $framework, $language;
        if(!$this->isAdmin()) {
            return $template->compile404();
        }

        $way = $page->getPathway();
        $cat_id = (int)$way[2];
        $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_forum_category WHERE cat_id = ?");
        $stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        if($stmt->rowCount() != 1) {
            return $template->compile404();
        }
        $resCat = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = null;

        if($system->post('forum_submit')) {
            $move_to_forum = $framework->fromPost('forum_moveto')->toInt()->get();
            $stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_com_forum_item WHERE forum_id = ?");
            $stmt->bindParam(1, $move_to_forum, PDO::PARAM_INT);
            $stmt->execute();
            $resCheck = $stmt->fetch();
            $stmt = null;
            if($resCheck[0] == 1) {
                $stmt = $database->con()->prepare("SELECT forum_id FROM {$constant->db['prefix']}_com_forum_item WHERE category = ?");
                $stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
                $stmt->execute();
                $resForumIds = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $forumsRemoveIds = $system->extractFromMultyArray('forum_id', $resForumIds);
                $removeForumList = $system->altimplode(',', $forumsRemoveIds);
                $stmt = null;
                if($system->isIntList($removeForumList)) {
                    $stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_com_forum_thread SET thread_forum_depend = ? WHERE thread_forum_depend IN($removeForumList)");
                    $stmt->bindParam(1, $move_to_forum, PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                }
                $stmt = $database->con()->prepare("DELETE FROM {$constant->db['prefix']}_com_forum_item WHERE category = ?");
                $stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                $stmt = $database->con()->prepare("DELETE FROM {$constant->db['prefix']}_com_forum_category WHERE cat_id = ?");
                $stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                $system->redirect('/forum/');
            }
        }

        $theme_main = $template->get('forum_main', 'components/forum/');
        $theme_breadcrumb_inactive = $template->get('forum_breadcrumb_last', 'components/forum/');
        $theme_del_cat = $template->get('forum_admin_delete_cat', 'components/forum/');
        $theme_option = $template->get('forum_option_form', 'components/forum/');
        $this->pathback .= $template->assign('forum_breadcrumb_title', $language->get('forum_admin_delcat'), $theme_breadcrumb_inactive);

        $stmt = $database->con()->prepare("SELECT forum_id, forum_name FROM {$constant->db['prefix']}_com_forum_item WHERE category != ?");
        $stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        $resForums = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        $option_alllowed_move = null;
        foreach($resForums as $resItem) {
            $option_alllowed_move .= $template->assign(array('option_name', 'option_value'), array($resItem['forum_name'], $resItem['forum_id']), $theme_option);
        }

        $theme_del_cat = $template->assign(array('forum_options', 'forum_title'), array($option_alllowed_move, $resCat['cat_name']), $theme_del_cat);

        return $template->assign(array('forum_entery', 'forum_breadcrumbs'), array($theme_del_cat, $this->pathback), $theme_main);
    }

    private function adminAddCat()
    {
        global $template, $database, $constant, $system, $framework, $language;
        if(!$this->isAdmin()) {
            return $template->compile404();
        }

        if($system->post('forum_submit')) {
            $cat_name = $framework->fromPost('forum_title')->nohtml()->get();
            if($framework->set($cat_name)->length() > 0) {
                $stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_com_forum_category (cat_name) VALUES(?)");
                $stmt->bindParam(1, $cat_name, PDO::PARAM_STR);
                $stmt->execute();
                $system->redirect('/forum/');
            }
        }
        $theme_main = $template->get('forum_main', 'components/forum/');
        $theme_breadcrumb_inactive = $template->get('forum_breadcrumb_last', 'components/forum/');
        $theme_edit_cat = $template->get('forum_admin_edit_cat', 'components/forum/');
        $this->pathback .= $template->assign('forum_breadcrumb_title', $language->get('forum_admin_addcat'), $theme_breadcrumb_inactive);

        return $template->assign(array('forum_entery', 'forum_breadcrumbs'), array($theme_edit_cat, $this->pathback), $theme_main);
    }

    private function adminEditCat()
    {
        global $template, $page, $database, $constant, $system, $framework, $language;
        if(!$this->isAdmin()) {
            return $template->compile404();
        }
        $way = $page->getPathway();
        $cat_id = (int)$way[2];
        $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_forum_category WHERE cat_id = ?");
        $stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        if($stmt->rowCount() != 1) {
            return $template->compile404();
        }
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = null;
        if($system->post('forum_submit')) {
            $cat_name = $framework->fromPost('forum_title')->nohtml()->get();
            if($framework->set($cat_name)->length() > 0) {
                $stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_com_forum_category SET cat_name = ? WHERE cat_id = ?");
                $stmt->bindParam(1, $cat_name, PDO::PARAM_STR);
                $stmt->bindParam(2, $cat_id, PDO::PARAM_INT);
                $stmt->execute();
                $system->redirect("/forum/");
            }
        }
        $theme_main = $template->get('forum_main', 'components/forum/');
        $theme_breadcrumb_inactive = $template->get('forum_breadcrumb_last', 'components/forum/');
        $theme_edit_cat = $template->get('forum_admin_edit_cat', 'components/forum/');
        $this->pathback .= $template->assign('forum_breadcrumb_title', $language->get('forum_admin_editcat'), $theme_breadcrumb_inactive);

        $theme_edit_cat = $template->assign('forum_save_title', $res['cat_name'], $theme_edit_cat);

        return $template->assign(array('forum_entery', 'forum_breadcrumbs'), array($theme_edit_cat, $this->pathback), $theme_main);
    }

    private function adminAddForum()
    {
        global $template, $database, $constant, $system, $framework, $rule, $language;
        if(!$this->isAdmin()) {
            return $template->compile404();
        }
        $rule->add('com.forum.show_cat', true);
        if($system->post('forum_submit')) {
            $forum_title = $framework->fromPost('forum_title')->nohtml()->get();
            $forum_desc = $framework->fromPost('forum_desc')->nohtml()->get();
            $cat_id = $framework->fromPost('forum_cat')->toInt()->get();
            if($framework->set($forum_title)->length() > 0) {
                $stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_com_forum_item (`forum_name`, `forum_desc`, `category`) VALUES (?, ?, ?)");
                $stmt->bindParam(1, $forum_title, PDO::PARAM_STR);
                $stmt->bindParam(2, $forum_desc, PDO::PARAM_STR);
                $stmt->bindParam(3, $cat_id, PDO::PARAM_INT);
                $stmt->execute();
                $system->redirect('/forum/');
            }
        }
        $theme_main = $template->get('forum_main', 'components/forum/');
        $theme_breadcrumb_inactive = $template->get('forum_breadcrumb_last', 'components/forum/');
        $theme_edit_forum = $template->get('forum_admin_edit_forum', 'components/forum/');
        $theme_option = $template->get('forum_option_form', 'components/forum/');
        $this->pathback .= $template->assign('forum_breadcrumb_title', $language->get('forum_admin_addforum'), $theme_breadcrumb_inactive);

        $stmt = $database->con()->query("SELECT * FROM {$constant->db['prefix']}_com_forum_category");
        $resCat = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        $options_cat = null;
        foreach($resCat as $catSelect) {
            $options_cat .= $template->assign(array('option_name', 'option_value'), array($catSelect['cat_name'], $catSelect['cat_id']), $theme_option);
        }

        $theme_edit_forum = $template->assign('forum_option_cats', $options_cat, $theme_edit_forum);

        return $template->assign(array('forum_entery', 'forum_breadcrumbs'), array($theme_edit_forum, $this->pathback), $theme_main);
    }

    private function adminDeleteForum()
    {
        global $template, $page, $database, $constant, $system, $framework, $language;
        if(!$this->isAdmin()) {
            return $template->compile404();
        }
        $notify = null;
        $way = $page->getPathway();
        $forum_id = (int)$way[2];
        $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_forum_item WHERE forum_id = ?");
        $stmt->bindParam(1, $forum_id, PDO::PARAM_INT);
        $stmt->execute();
        if($stmt->rowCount() != 1) {
            return $template->compile404();
        }
        $res = $stmt->fetch();
        $stmt = null;

        if($system->post('forum_submit')) {
            $move_to_id = $framework->fromPost('forum_moveto')->toInt()->get();
            $stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_com_forum_item WHERE forum_id = ?");
            $stmt->bindParam(1, $move_to_id, PDO::PARAM_INT);
            $stmt->execute();
            $check_res = $stmt->fetch();
            $stmt = null;
            if($check_res[0] == 1 && $move_to_id != $forum_id) {
                $stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_com_forum_thread SET thread_forum_depend = ? WHERE thread_forum_depend = ?");
                $stmt->bindParam(1, $move_to_id, PDO::PARAM_INT);
                $stmt->bindParam(2, $forum_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                $stmt = $database->con()->prepare("DELETE FROM {$constant->db['prefix']}_com_forum_item WHERE forum_id = ?");
                $stmt->bindParam(1, $forum_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                $system->redirect("/forum/");
            }
        }

        $theme_main = $template->get('forum_main', 'components/forum/');
        $theme_breadcrumb_inactive = $template->get('forum_breadcrumb_last', 'components/forum/');
        $theme_delete_forum = $template->get('forum_admin_delete_forum', 'components/forum/');
        $theme_option = $template->get('forum_option_form', 'components/forum/');
        $this->pathback .= $template->assign('forum_breadcrumb_title', $language->get('forum_admin_delforum'), $theme_breadcrumb_inactive);

        $stmt = $database->con()->prepare("SELECT forum_id, forum_name FROM {$constant->db['prefix']}_com_forum_item WHERE forum_id != ?");
        $stmt->bindParam(1, $forum_id, PDO::PARAM_INT);
        $stmt->execute();
        $resForums = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        $option_alllowed_move = null;
        foreach($resForums as $resItem) {
            $option_alllowed_move .= $template->assign(array('option_name', 'option_value'), array($resItem['forum_name'], $resItem['forum_id']), $theme_option);
        }

        $theme_delete_forum = $template->assign(array('notify', 'forum_title', 'forum_options'), array($notify, $res['forum_name'], $option_alllowed_move), $theme_delete_forum);

        return $template->assign(array('forum_entery', 'forum_breadcrumbs'), array($theme_delete_forum, $this->pathback), $theme_main);
    }

    private function adminEditForum()
    {
        global $template, $page, $database, $constant, $system, $framework, $language;
        if(!$this->isAdmin()) {
            return $template->compile404();
        }
        $notify = null;
        $way = $page->getPathway();
        $forum_id = (int)$way[2];
        $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_forum_item WHERE forum_id = ?");
        $stmt->bindParam(1, $forum_id, PDO::PARAM_INT);
        $stmt->execute();
        if($stmt->rowCount() != 1) {
            return $template->compile404();
        }
        $res = $stmt->fetch();
        $stmt = null;
        if($system->post('forum_submit')) {
            $new_title = $framework->fromPost('forum_title')->nohtml()->get();
            $new_desc = $framework->fromPost('forum_desc')->nohtml()->get();
            $stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_com_forum_item SET forum_name = ?, forum_desc = ? WHERE forum_id = ?");
            $stmt->bindParam(1, $new_title, PDO::PARAM_STR);
            $stmt->bindParam(2, $new_desc, PDO::PARAM_STR);
            $stmt->bindParam(3, $forum_id, PDO::PARAM_INT);
            $stmt->execute();
            // обновляем
            $res['forum_name'] = $new_title;
            $res['forum_desc'] = $new_desc;
            $notify .= $template->stringNotify('success', $language->get('forum_notify_update_data'));
        }
        $theme_main = $template->get('forum_main', 'components/forum/');
        $theme_breadcrumb_inactive = $template->get('forum_breadcrumb_last', 'components/forum/');
        $theme_edit_forum = $template->get('forum_admin_edit_forum', 'components/forum/');
        $this->pathback .= $template->assign('forum_breadcrumb_title', $language->get('forum_admin_editforum'), $theme_breadcrumb_inactive);

        $theme_edit_forum = $template->assign(array('forum_save_title', 'forum_save_desc', 'notify'), array($res['forum_name'], $res['forum_desc'], $notify), $theme_edit_forum);

        return $template->assign(array('forum_entery', 'forum_breadcrumbs'), array($theme_edit_forum, $this->pathback), $theme_main);
    }

    private function viewDeletePost()
    {
        global $template, $page, $database, $constant, $system, $language;
        $way = $page->getPathway();
        $post_id = (int)$way[2];
        $stmt = $database->con()->prepare("SELECT COUNT(*),post_target_thread FROM {$constant->db['prefix']}_com_forum_post WHERE post_id = ?");
        $stmt->bindParam(1, $post_id, PDO::PARAM_INT);
        $stmt->execute();
        $resPost = $stmt->fetch();
        if($resPost[0] != 1) {
            return $template->compile404();
        }
        if($system->post('submitdelete')) {
            $stmt = $database->con()->prepare("DELETE FROM {$constant->db['prefix']}_com_forum_post WHERE post_id = ?");
            $stmt->bindParam(1, $post_id, PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            $system->redirect('/forum/viewtopic/'.$resPost['post_target_thread']);
        }
        $theme_main = $template->get('forum_main', 'components/forum/');
        $theme_breadcrumb_inactive = $template->get('forum_breadcrumb_last', 'components/forum/');
        $theme_delete = $template->get('forum_delete_item', 'components/forum/');
        $this->pathback .= $template->assign('forum_breadcrumb_title', $language->get('forum_bread_delete'), $theme_breadcrumb_inactive);
        return $template->assign(array('forum_entery', 'forum_breadcrumbs'), array($theme_delete, $this->pathback), $theme_main);
    }

    private function viewDeleteThread()
    {
        global $template, $page, $database, $constant, $system, $language;
        $way = $page->getPathway();
        $thread_id = (int)$way[2];
        $stmt = $database->con()->prepare("SELECT COUNT(*),thread_forum_depend FROM {$constant->db['prefix']}_com_forum_thread WHERE thread_id = ?");
        $stmt->bindParam(1, $thread_id, PDO::PARAM_INT);
        $stmt->execute();
        $resThread = $stmt->fetch();
        $forum_depend_id = $resThread['thread_forum_depend'];
        $stmt = null;
        if($resThread[0] != 1) {
            return $template->compile404();
        }
        if($system->post('submitdelete')) {
            $stmt = $database->con()->prepare("DELETE FROM {$constant->db['prefix']}_com_forum_thread WHERE thread_id = ?");
            $stmt->bindParam(1, $thread_id, PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            $stmt = $database->con()->prepare("DELETE FROM {$constant->db['prefix']}_com_forum_post WHERE post_target_thread = ?");
            $stmt->bindParam(1, $thread_id, PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            $system->redirect('/forum/viewforum/'.$forum_depend_id);
        }
        $theme_main = $template->get('forum_main', 'components/forum/');
        $theme_breadcrumb_inactive = $template->get('forum_breadcrumb_last', 'components/forum/');
        $theme_delete = $template->get('forum_delete_item', 'components/forum/');
        $this->pathback .= $template->assign('forum_breadcrumb_title', $language->get('forum_bread_delete'), $theme_breadcrumb_inactive);
        return $template->assign(array('forum_entery', 'forum_breadcrumbs'), array($theme_delete, $this->pathback), $theme_main);
    }

    private function viewEditPost()
    {
        global $rule, $template, $page, $database, $constant, $system, $framework, $language;
        if(!$this->canEdit()) {
            return $template->compile404();
        }
        $way = $page->getPathway();
        $post_id = (int)$way[2];
        $stmt = $database->con()->prepare("SELECT COUNT(*),post_message,post_target_thread FROM {$constant->db['prefix']}_com_forum_post WHERE post_id = ?");
        $stmt->bindParam(1, $post_id, PDO::PARAM_INT);
        $stmt->execute();
        $resFound = $stmt->fetch();
        $stmt = null;
        if($resFound[0] != 1) {
            return $template->compile404();
        }
        if($system->post('forum_submit')) {
            if($framework->fromPost('forum_message')->length() > 1) {
                $post_text = $framework->fromPost('forum_message')->nohtml()->get();
                $stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_com_forum_post SET post_message = ? WHERE post_id = ?");
                $stmt->bindParam(1, $post_text, PDO::PARAM_STR);
                $stmt->bindParam(2, $post_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                $system->redirect('/forum/viewtopic/'.$resFound['post_target_thread']);
            }
        }
        $rule->add('com.forum.show_title', false);
        $theme_main = $template->get('forum_main', 'components/forum/');
        $theme_create = $template->get('forum_create_thread', 'components/forum/');
        $theme_breadcrumb_inactive = $template->get('forum_breadcrumb_last', 'components/forum/');
        $this->pathback .= $template->assign('forum_breadcrumb_title', $language->get('forum_bread_edit'), $theme_breadcrumb_inactive);
        $theme_create = $template->assign('forum_save_text', $resFound['post_message'], $theme_create);
        return $template->assign(array('forum_entery', 'forum_breadcrumbs'), array($theme_create, $this->pathback), $theme_main);
    }

    private function viewEditThread()
    {
        global $rule, $template, $page, $database, $constant, $system, $framework, $language;
        if(!$this->canEdit()) {
            return $template->compile404();
        }
        $way = $page->getPathway();
        $thread_id = (int)$way[2];
        $stmt = $database->con()->prepare("SELECT COUNT(*),thread_title,thread_body FROM {$constant->db['prefix']}_com_forum_thread WHERE thread_id = ?");
        $stmt->bindParam(1, $thread_id, PDO::PARAM_INT);
        $stmt->execute();
        $resFound = $stmt->fetch();
        $stmt = null;
        if($resFound[0] != 1) {
            return $template->compile404();
        }
        if($system->post('forum_submit')) {
            if($framework->fromPost('forum_title')->length() > 1 && $framework->fromPost('forum_message')->length() > 1) {
                $thread_title = $framework->fromPost('forum_title')->nohtml()->get();
                $thread_text = $framework->fromPost('forum_message')->nohtml()->get();
                $stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_com_forum_thread SET thread_title = ?, thread_body = ? WHERE thread_id = ?");
                $stmt->bindParam(1, $thread_title, PDO::PARAM_STR);
                $stmt->bindParam(2, $thread_text, PDO::PARAM_STR);
                $stmt->bindParam(3, $thread_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                $system->redirect('/forum/viewtopic/'.$thread_id);
            }
        }
        $rule->add('com.forum.show_title', true);
        $theme_main = $template->get('forum_main', 'components/forum/');
        $theme_create = $template->get('forum_create_thread', 'components/forum/');
        $theme_breadcrumb_inactive = $template->get('forum_breadcrumb_last', 'components/forum/');
        $this->pathback .= $template->assign('forum_breadcrumb_title', $language->get('forum_bread_edit'), $theme_breadcrumb_inactive);
        $theme_create = $template->assign(array('forum_save_title', 'forum_save_text'), array($resFound['thread_title'], $resFound['thread_body']), $theme_create);
        return $template->assign(array('forum_entery', 'forum_breadcrumbs'), array($theme_create, $this->pathback), $theme_main);
    }

    private function viewAddThread()
    {
        global $page, $template, $system, $framework, $database, $constant, $user, $rule, $language, $extension;
        $way = $page->getPathway();
        $forum_id = (int)$way[2];
        $rule->add('com.forum.show_title', true);
        $theme_create = $template->get('forum_create_thread', 'components/forum/');
        if(!$this->canPost()) {
            return $template->compile404();
        }
        if($system->post('forum_submit')) {
            $thread_title = $framework->fromPost('forum_title')->nohtml()->get();
            $thread_message = $framework->fromPost('forum_message')->nohtml()->get();
            $thread_time = time();
            $thread_owner_id = $user->get('id');
            $thread_owner_nick = $user->get('nick');
            $title_length = $framework->set($thread_title)->length();
            $notify = null;
            $lastpost_time = $user->customget('forum_last_message');
            $delay_post = $extension->getConfig('post_delay', 'forum', 'components', 'int');
            if($delay_post < 15) {
                $delay_post = 15;
            }
            if($title_length < 3 || $title_length > 128) {
                $notify .= $template->stringNotify('error', $language->get('forum_add_title_incorrent'));
            }
            if($framework->set($thread_message)->length() < 10) {
                $notify .= $template->stringNotify('error', $language->get('forum_add_text_incorrent'));
            }
            if(time()-$lastpost_time < $delay_post) {
                $notify .= $template->stringNotify('error', $language->get('forum_error_spam'));
            }
            if($notify == null) {
                $stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_com_forum_item WHERE forum_id = ?");
                $stmt->bindParam(1, $forum_id, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetch();
                $foundForumCount = $res[0];
                $stmt = null;
                // существует ли ?
                if($foundForumCount == 1) {
                    $stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_com_forum_thread (`thread_title`, `thread_body`, `thread_starttime`, `thread_updatetime`, `thread_owner`, `thread_important`, `thread_updater`, `thread_starterid`, `thread_forum_depend`)
                    VALUES(?, ?, ?, ?, ?, 0, ?, ?, ?)");
                    $stmt->bindParam(1, $thread_title, PDO::PARAM_STR);
                    $stmt->bindParam(2, $thread_message, PDO::PARAM_STR);
                    $stmt->bindParam(3, $thread_time, PDO::PARAM_INT);
                    $stmt->bindParam(4, $thread_time, PDO::PARAM_INT);
                    $stmt->bindParam(5, $thread_owner_nick, PDO::PARAM_STR);
                    $stmt->bindParam(6, $thread_owner_nick, PDO::PARAM_STR);
                    $stmt->bindParam(7, $thread_owner_id, PDO::PARAM_INT);
                    $stmt->bindParam(8, $forum_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $thread_new_id = $database->con()->lastInsertId('thread_id');
                    $stmt = null;
                    $this->updatePostCount($thread_owner_id);
                    $this->updateForumThreadCount($forum_id, $thread_owner_nick, $thread_new_id);
                    $system->redirect('/forum/viewtopic/'.$thread_new_id);
                }
            } else {
                $theme_create = $template->assign(array('forum_save_title', 'forum_save_text'), array($thread_title, $thread_message), $theme_create);
            }
        }
        $theme_create = $template->assign('notify', $notify, $theme_create);
        $theme_breadcrumb_inactive = $template->get('forum_breadcrumb_last', 'components/forum/');
        $theme_main = $template->get('forum_main', 'components/forum/');
        $this->pathback .= $template->assign('forum_breadcrumb_title', $language->get('forum_bread_create'), $theme_breadcrumb_inactive);
        return $template->assign(array('forum_entery', 'forum_breadcrumbs'), array($theme_create, $this->pathback), $theme_main);
    }

    private function viewForumList()
    {
        global $template, $database, $constant, $system;
        $theme_main = $template->get('forum_main', 'components/forum/');
        $theme_cat = $template->get('forum_category_head', 'components/forum/');
        $theme_forum = $template->get('forum_category_item', 'components/forum/');
        $forum_cat = null;
        $stmt = $database->con()->query("SELECT * FROM {$constant->db['prefix']}_com_forum_category");
        $resultCat = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        $stmt = $database->con()->query("SELECT * FROM {$constant->db['prefix']}_com_forum_item WHERE depend_id = 0");
        $resultForum = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        foreach($resultCat as $cat) {
            $forum_body = null;
            foreach($resultForum as $forum) {
                if($forum['category'] == $cat['cat_id']) {
                    $lastpost = null;
                    if($forum['forum_lastupdate'] > 0) {
                        $lastpost = $system->toDate($forum['forum_lastupdate'], 'h');
                    }
                    $forum_body .= $template->assign(array('forum_item_title', 'forum_item_desc', 'forum_item_id', 'forum_item_threads', 'forum_item_posts', 'forum_last_update', 'forum_lasttopic_id', 'forum_last_poster'),
                        array($forum['forum_name'], $forum['forum_desc'], $forum['forum_id'], $forum['forum_threads'], $forum['forum_posts'], $lastpost, $forum['forum_lasttopic_id'], $forum['forum_lastposter']),
                        $theme_forum);
                }
            }
            $forum_cat .= $template->assign(array('forum_item_list', 'forum_category_name', 'forum_category_id'), array($forum_body, $cat['cat_name'], $cat['cat_id']), $theme_cat);
        }
        return $template->assign('forum_entery', $forum_cat, $theme_main);
    }

    private function viewForumItem()
    {
        global $template, $page, $database, $constant, $system, $meta, $rule;
        $way = $page->getPathway();
        $view_forum_id = (int)$way[2];
        $view_forum_page = (int)$way[3];
        $point_start_select = $view_forum_page * $this->threads_on_page;
        $theme_main = $template->get('forum_main', 'components/forum/');
        $theme_head = $template->get('forum_threads_head', 'components/forum/');
        $theme_item = $template->get('forum_threads_item', 'components/forum/');
        $theme_breadcrumb_inactive = $template->get('forum_breadcrumb_last', 'components/forum/');
        if($this->canPost()) {
            $rule->add('com.forum.can_post', true);
        }
        $stmt = $database->con()->prepare("SELECT a.*, b.forum_id, b.forum_name FROM {$constant->db['prefix']}_com_forum_thread a, {$constant->db['prefix']}_com_forum_item b WHERE a.thread_forum_depend = ? AND a.thread_forum_depend = b.forum_id ORDER BY a.thread_updatetime DESC LIMIT ?, ?");
        $stmt->bindParam(1, $view_forum_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $point_start_select, PDO::PARAM_INT);
        $stmt->bindParam(3, $this->threads_on_page, PDO::PARAM_INT);
        $stmt->execute();
        $result_body = null;
        $current_forum_name = null;
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result_body .= $template->assign(array('forum_thread_title', 'forum_thread_starter', 'forum_thread_date', 'forum_thread_lastanswer', 'forum_thread_posts', 'forum_thread_views', 'forum_thread_id'),
            array($result['thread_title'], $result['thread_owner'], $system->toDate($result['thread_updatetime'], 'h'), $result['thread_updater'], $result['thread_post_count'], $result['thread_view_count'], $result['thread_id']),
            $theme_item);
            $current_forum_name == null ? $current_forum_name = $result['forum_name'] : null;
        }
        $this->pathback .= $template->assign('forum_breadcrumb_title', $current_forum_name, $theme_breadcrumb_inactive);
        $meta->add('title',  $current_forum_name);
        $forum_pagination_link = "forum/viewforum/" . $view_forum_id . "/";
        $pagination = $template->drowNumericPagination($view_forum_page, $this->threads_on_page, $this->threadsInForum($view_forum_id), $forum_pagination_link);
        $result_head = $template->assign(array('forum_item_name', 'forum_thread_item', 'forum_item_id', 'forum_threads_pagination'), array($current_forum_name, $result_body, $view_forum_id, $pagination), $theme_head);
        return $template->assign(array('forum_entery', 'forum_breadcrumbs'), array($result_head, $this->pathback), $theme_main);
    }

    private function viewTopicItem()
    {
        global $page, $database, $constant, $template, $user, $system, $meta, $rule, $framework, $hook, $extension, $language;
        $way = $page->getPathway();
        $view_thread_id = (int)$way[2];
        $view_thread_page = (int)$way[3];
        $theme_main = $template->get('forum_main', 'components/forum/');
        $theme_head = $template->get('forum_post_head', 'components/forum/');
        $theme_body = $template->get('forum_post_item', 'components/forum/');
        $theme_breadcrumb_active = $template->get('forum_breadcrumb', 'components/forum/');
        $theme_breadcrumb_inactive = $template->get('forum_breadcrumb_last', 'components/forum/');
        $notify = null;
        $post_message = null;
        if($system->post('forum_submit') && $framework->fromPost('forum_message')->length() > 0) {
            $post_message = $framework->fromPost('forum_message')->nohtml()->get();
            $time = time();
            $lastpost_time = $user->customget('forum_last_message');
            $delay_post = $extension->getConfig('post_delay', 'forum', 'components', 'int');
            if($time-$lastpost_time < $delay_post) {
                $notify .= $template->stringNotify('error', $language->get('forum_error_spam'));
            }
            if($this->canPost() && $notify == null) {
                $stmt = $database->con()->prepare("SELECT COUNT(*),thread_forum_depend FROM {$constant->db['prefix']}_com_forum_thread WHERE thread_id = ?");
                $stmt->bindParam(1, $view_thread_id, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetch();
                $countThreadFound = $res[0];
                $forum_id = $res[1];
                $stmt = null;
                if($countThreadFound == 1) {
                    $userid = $user->get('id');
                    $usernick = $user->get('nick');
                    $stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_com_forum_post (`post_target_thread`, `post_message`, `post_userid`, `post_time`) VALUES(?, ?, ?, ?)");
                    $stmt->bindParam(1, $view_thread_id, PDO::PARAM_INT);
                    $stmt->bindParam(2, $post_message, PDO::PARAM_STR);
                    $stmt->bindParam(3, $userid, PDO::PARAM_INT);
                    $stmt->bindParam(4, $time, PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                    $this->updatePostCount($userid);
                    $this->updateForumPostCount($forum_id, $usernick, $view_thread_id);
                    $stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_com_forum_thread SET thread_updatetime = ?, thread_updater = ? WHERE thread_id = ?");
                    $stmt->bindParam(1, $time, PDO::PARAM_INT);
                    $stmt->bindParam(2, $usernick, PDO::PARAM_STR);
                    $stmt->bindParam(3, $view_thread_id, PDO::PARAM_STR);
                    $stmt->execute();
                }
            }
        }
        $compiled_body = null;
        // стартовый мессадж в топике
        $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_forum_thread WHERE thread_id = ?");
        $stmt->bindParam(1, $view_thread_id, PDO::PARAM_INT);
        $stmt->execute();
        if($stmt->rowCount() != 1) {
            return $template->compile404();
        }
        $resultMainThread = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = null;
        $post_sql_starter = $view_thread_page * $this->post_on_page;
        // сообщения в топике
        $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_forum_post WHERE post_target_thread = ? ORDER BY post_id ASC LIMIT ?,?");
        $stmt->bindParam(1, $view_thread_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $post_sql_starter, PDO::PARAM_INT);
        $stmt->bindParam(3, $this->post_on_page, PDO::PARAM_INT);
        $stmt->execute();
        $resultPostThread = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        // название форума для breadcrumbs
        $stmt = $database->con()->prepare("SELECT forum_name FROM {$constant->db['prefix']}_com_forum_item WHERE forum_id = ?");
        $stmt->bindParam(1, $resultMainThread['thread_forum_depend']);
        $stmt->execute();
        $resultForumItem = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = null;
        $this->pathback .= $template->assign(array('forum_breadcrumb_url', 'forum_breadcrumb_title'), array('viewforum/'.$resultMainThread['thread_forum_depend'], $resultForumItem['forum_name']), $theme_breadcrumb_active);
        // загловок топика для seo title & breadcrumbs
        $this->pathback .= $template->assign('forum_breadcrumb_title', $resultMainThread['thread_title'], $theme_breadcrumb_inactive);
        $meta->add('title', $resultMainThread['thread_title']);
        // уменьшаем кол-во запросов на получение информации о пользователях, выгружаем заранее
        $userlist = $system->extractFromMultyArray('post_userid', $resultPostThread);
        $userlist = $system->arrayAdd($resultMainThread['thread_starterid'], $userlist);
        $user->listload($userlist);
        // счетчик нумерации сообщения
        $post_number = 1;
        // это не первая страница
        if($view_thread_page > 0) {
            $post_number += $post_sql_starter;
        } else {
            $delete_link = "deletethread/".$resultMainThread['thread_id'];
            $edit_link = "editthread/".$resultMainThread['thread_id'];
            $compiled_body .= $template->assign(array('forum_item_message', 'forum_poster_avatar', 'forum_user_id', 'forum_user_nick', 'forum_user_group', 'forum_message_number', 'forum_item_date', 'forum_user_posts', 'forum_delete_item', 'forum_edit_item'),
                array($hook->get('bbtohtml')->bbcode2html($resultMainThread['thread_body']), $user->buildAvatar('small', $resultMainThread['thread_starterid']), $resultMainThread['thread_starterid'], $user->get('nick', $resultMainThread['thread_starterid']), $user->get('group_name', $resultMainThread['thread_starterid']), $post_number, $system->toDate($resultMainThread['thread_starttime'], 'h'), $user->customget('forum_posts', $resultMainThread['thread_starterid']), $delete_link, $edit_link),
                $theme_body);
            $post_number++;
        }
        foreach($resultPostThread as $post) {
            $delete_link = "deletepost/".$post['post_id'];
            $edit_link = "editpost/".$post['post_id'];
            $compiled_body .= $template->assign(array('forum_item_message', 'forum_poster_avatar', 'forum_user_id', 'forum_user_nick', 'forum_user_group', 'forum_message_number', 'forum_item_date', 'forum_user_posts', 'forum_delete_item', 'forum_edit_item'),
                array($hook->get('bbtohtml')->bbcode2html($post['post_message']), $user->buildAvatar('small', $post['post_userid']), $post['post_userid'], $user->get('nick', $post['post_userid']), $user->get('group_name', $post['post_userid']), $post_number, $system->toDate($post['post_time'], 'h'), $user->customget('forum_posts', $resultMainThread['thread_starterid']), $delete_link, $edit_link),
                $theme_body);
            $post_number++;
        }
        if($compiled_body == null) {
            return $template->compile404();
        }
        if($this->canPost()) {
            $rule->add('com.forum.can_post', true);
        }
        $this->updateViewCount($view_thread_id);
        $forum_pagination_link = "forum/viewtopic/" . $view_thread_id . "/";
        $pagination = $template->drowNumericPagination($view_thread_page, $this->post_on_page, $this->postInThread($view_thread_id), $forum_pagination_link);
        if($notify != null) {
            $theme_head = $template->assign(array('notify', 'forum_text'), array($notify, $post_message), $theme_head);
        }
        $compiled_head = $template->assign(array('forum_posts_list', 'forum_dynamic_pagination'),
            array($compiled_body, $pagination),
            $theme_head);
        return $template->assign(array('forum_entery', 'forum_breadcrumbs'), array($compiled_head, $this->pathback), $theme_main);
    }

    private function canEdit()
    {
        global $user;
        return $user->get('access_to_admin') > 0 ? true : false;
    }

    private function canDelete()
    {
        global $user;
        return $user->get('access_to_admin') > 0 ? true : false;
    }

    private function canPost()
    {
        global $user;
        return $user->get('id') > 0 ? true : false;
    }

    private function isAdmin()
    {
        global $user;
        return $user->get('access_to_admin') > 0 ? true : false;
    }

    private function updateViewCount($thread_id)
    {
        global $database, $constant;
        $stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_com_forum_thread SET thread_view_count = thread_view_count+1 WHERE thread_id = ?");
        $stmt->bindParam(1, $thread_id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt = null;
    }

    private function updatePostCount($userid)
    {
        global $database, $constant;
        $time = time();
        $stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_user_custom SET forum_posts = forum_posts+1, forum_last_message = ? WHERE id = ?");
        $stmt->bindParam(1, $time, PDO::PARAM_INT);
        $stmt->bindParam(2, $userid, PDO::PARAM_INT);
        $stmt->execute();
        $stmt = null;
    }

    private function updateForumPostCount($id, $usernick, $thread_id)
    {
        global $database, $constant;
        $time = time();
        $stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_com_forum_item SET forum_posts = forum_posts+1, forum_lastposter = ?, forum_lastupdate = ?, forum_lasttopic_id = ?  WHERE forum_id = ?");
        $stmt->bindParam(1, $usernick, PDO::PARAM_STR);
        $stmt->bindParam(2, $time, PDO::PARAM_INT);
        $stmt->bindParam(3, $thread_id, PDO::PARAM_INT);
        $stmt->bindParam(4, $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt = null;
        $stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_com_forum_thread set thread_post_count = thread_post_count+1 WHERE thread_id = ?");
        $stmt->bindParam(1, $thread_id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt = null;
    }

    private function updateForumThreadCount($id, $usernick, $thread_id)
    {
        global $database, $constant;
        $time = time();
        $stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_com_forum_item SET forum_threads = forum_threads+1, forum_lastposter = ?, forum_lastupdate = ?, forum_lasttopic_id = ? WHERE forum_id = ?");
        $stmt->bindParam(1, $usernick, PDO::PARAM_STR);
        $stmt->bindParam(2, $time, PDO::PARAM_INT);
        $stmt->bindParam(3, $thread_id, PDO::PARAM_INT);
        $stmt->bindParam(4, $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt = null;
    }

    private function threadsInForum($fid)
    {
        global $database, $constant;
        $stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_com_forum_thread WHERE thread_forum_depend = ?");
        $stmt->bindParam(1, $fid, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();
        return $res[0];
    }

    private function postInThread($tid)
    {
        global $database, $constant;
        $stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_com_forum_post WHERE post_target_thread = ?");
        $stmt->bindParam(1, $tid, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();
        return $res[0];
    }
}
?>