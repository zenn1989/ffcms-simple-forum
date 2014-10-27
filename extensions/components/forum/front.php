<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\router;
use engine\template;
use engine\database;
use engine\property;
use engine\system;
use engine\user;
use engine\extension;
use engine\permission;
use engine\meta;
use engine\language;

class components_forum_front extends \engine\singleton {

    public function make() {
        $way = router::getInstance()->shiftUriArray();
        $content = null;
        meta::getInstance()->add('title', language::getInstance()->get('forum_global_title'));
        switch($way[0]) {
            case null:
                $content = $this->viewMain();
                break;
            case 'viewboard':
                $content = $this->viewBoard($way[1], (int)$way[2]);
                break;
            case 'viewtopic':
                $content = $this->viewTopic($way[1], (int)$way[2]);
                break;
            case 'addthread':
                $content = $this->viewAddThread($way[1]);
                break;
            case 'delpost':
                $content = $this->viewPostDelete($way[1], $way[2], $way[3]);
                break;
            case 'delthread':
                $content = $this->viewThreadDelete($way[1], $way[2]);
                break;
            case 'editpost':
                $content = $this->viewPostEdit($way[1], $way[2]);
                break;
            case 'editthread':
                $content = $this->viewThreadEdit($way[1]);
                break;
            case 'setimportant':
                $content = $this->viewThreadSetImportant($way[1]);
                break;
            case 'movethread':
                $content = $this->viewThreadMove($way[1], $way[2]);
                break;
            case 'unsetimportant':
                $content = $this->viewThreadUnsetImportant($way[1]);
                break;
            case 'stream.html':
                $content = $this->viewUpdates();
                break;
        }
        template::getInstance()->set(template::TYPE_CONTENT, 'body', $content);
    }

    private function viewUpdates() {
        $params = array();

        $limit = 10;

        $stmt = database::getInstance()->con()->query("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_forum_thread ORDER BY thread_updatetime DESC LIMIT 0,".$limit);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;

        foreach($result as $row) {
            $params['forum']['stream'][] = array(
                'thread_id' => $row['thread_id'],
                'title' => $row['thread_title'],
                'updater' => $row['thread_updater'],
                'time' => system::getInstance()->toDate($row['thread_updatetime'], 'h'),
                'new_thread' => $row['thread_starttime'] == $row['thread_updatetime']
            );
        }

        meta::getInstance()->add('title', language::getInstance()->get('forum_stream_title'));

        return template::getInstance()->twigRender('components/forum/stream.tpl', $params);
    }

    private function viewThreadUnsetImportant($id) {
        if(!system::getInstance()->isInt($id) || !permission::getInstance()->have('global/owner'))
            return null;

        $params = array();

        $params['forum']['thread']['id'] = $id;

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_forum_thread WHERE thread_id = ? AND thread_important > 0");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() != 1)
            return null;

        $res = $stmt->fetch();
        $stmt = null;

        $params['forum']['thread']['title'] = $res['thread_title'];

        if(system::getInstance()->post('submit_important')) {
            $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_forum_thread SET thread_important = 0 WHERE thread_id = ?");
            $stmt->bindParam(1, $id, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            system::getInstance()->redirect('/forum/viewtopic/'.$id);
        }

        return template::getInstance()->twigRender('components/forum/unsetthread_important.tpl', $params);
    }



    private function viewThreadSetImportant($id) {
        if(!system::getInstance()->isInt($id) || !permission::getInstance()->have('global/owner'))
            return null;

        $params = array();

        $params['forum']['thread']['id'] = $id;

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_forum_thread WHERE thread_id = ? AND thread_important = 0");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() != 1)
            return null;

        $res = $stmt->fetch();
        $stmt = null;

        $params['forum']['thread']['title'] = $res['thread_title'];

        if(system::getInstance()->post('submit_important')) {
            $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_forum_thread SET thread_important = 1 WHERE thread_id = ?");
            $stmt->bindParam(1, $id, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            system::getInstance()->redirect('/forum/viewtopic/'.$id);
        }

        return template::getInstance()->twigRender('components/forum/setthread_important.tpl', $params);

    }

    private function viewThreadMove($tid, $fid) {
        if(!system::getInstance()->isInt($tid) || !system::getInstance()->isInt($fid))
            return null;
        $params = array();

        $stmt = database::getInstance()->con()->prepare("SELECT a.forum_id,a.forum_name,b.cat_name FROM `".property::getInstance()->get('db_prefix')."_com_forum_item` as a,
            `".property::getInstance()->get('db_prefix')."_com_forum_category` as b WHERE a.category = b.cat_id AND a.forum_id != ?");
        $stmt->bindParam(1, $fid, \PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() < 1)
            return null;

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;

        $params['thread']['id'] = $tid;

        foreach($result as $row) {
            $params['thread']['move'][$row['forum_id']] = $row['cat_name'] . ' - ' . $row['forum_name'];
        }

        if(system::getInstance()->post('submit')) {
            $moveto = (int)system::getInstance()->post('new_forum');
            if(array_key_exists($moveto, $params['thread']['move'])) {
                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_forum_thread SET thread_forum_depend = ? WHERE thread_id = ?");
                $stmt->bindParam(1, $moveto, \PDO::PARAM_INT);
                $stmt->bindParam(2, $tid, \PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;

                $this->reCountForum($fid);
                $this->reCountForum($moveto);
            }
            system::getInstance()->redirect('/forum/viewtopic/' . $tid);
        }
        return template::getInstance()->twigRender('components/forum/thread_move.tpl', $params);
    }

    private function viewThreadEdit($id) {
        if(!system::getInstance()->isInt($id))
            return null;
        if(!permission::getInstance()->have('global/owner'))
            return null;
        $params = array();

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_forum_thread WHERE thread_id = ?");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
        if($stmt->rowCount() == 1) {
            $res = $stmt->fetch();
            $stmt = null;
            $params['forum']['thread']['title'] = $res['thread_title'];
            $params['forum']['thread']['text'] = $res['thread_body'];
            $params['forum']['thread']['id'] = $id;
            if(system::getInstance()->post('forum_submit')) {
                $title = system::getInstance()->nohtml(system::getInstance()->post('forum_title'));
                $text = system::getInstance()->nohtml(system::getInstance()->post('forum_message'));

                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_forum_thread SET thread_title = ?, thread_body = ? WHERE thread_id = ?");
                $stmt->bindParam(1, $title, \PDO::PARAM_STR);
                $stmt->bindParam(2, $text, \PDO::PARAM_STR);
                $stmt->bindParam(3, $id, \PDO::PARAM_INT);
                $stmt->execute();

                system::getInstance()->redirect('/forum/viewtopic/'.$id);
            }
        } else {
            return null;
        }

        return template::getInstance()->twigRender('components/forum/thread_edit.tpl', $params);
    }

    private function viewPostEdit($id, $thread_id) {
        if(!system::getInstance()->isInt($id))
            return null;
        if(!permission::getInstance()->have('global/owner'))
            return null;
        $params = array();

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_forum_post WHERE post_id = ?");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
        if($stmt->rowCount() == 1) {
            $res = $stmt->fetch();
            $stmt = null;
            if(system::getInstance()->post('forum_submit')) {
                $post_message = system::getInstance()->nohtml(system::getInstance()->post('forum_message'));
                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_forum_post SET post_message = ? WHERE post_id = ?");
                $stmt->bindParam(1, $post_message, \PDO::PARAM_STR);
                $stmt->bindParam(2, $id, \PDO::PARAM_INT);
                $stmt->execute();
                system::getInstance()->redirect('/forum/viewtopic/'.$thread_id.'#post'.$id);
            }
            $params['forum']['post']['text'] = $res['post_message'];
            $params['forum']['thread']['id'] = $thread_id;
        } else {
            return null;
        }

        return template::getInstance()->twigRender('components/forum/post_edit.tpl', $params);
    }

    private function viewThreadDelete($id, $forum_id) {
        if(user::getInstance()->get('id') < 1 || !system::getInstance()->isInt($id))
            return null;
        if(!permission::getInstance()->have('global/owner'))
            return null;

        $params = array();

        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_forum_thread WHERE thread_id = ?");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();

        $res = $stmt->fetch();
        if($res[0] != 1)
            return null;

        $stmt = null;
        // deleted posts count
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_forum_post WHERE post_target_thread = ?");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();
        $stmt = null;
        $post_delete_count = $res[0];

        if(system::getInstance()->post('submit_delete')) {
            // delete thread and posts
            $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_com_forum_post WHERE post_target_thread = ?");
            $stmt->bindParam(1, $id, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_com_forum_thread WHERE thread_id = ?");
            $stmt->bindParam(1, $id, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            // get info about other threads in forum
            $this->reCountForum($forum_id);
            system::getInstance()->redirect('/forum/viewboard/'.$forum_id);
        }

        $params['forum']['id'] = $forum_id;
        $params['thread']['id'] = $id;

        return template::getInstance()->twigRender('components/forum/thread_delete.tpl', $params);
    }

    private function viewPostDelete($post, $return_thread_id, $forum_id) {
        $params = array();
        if(!system::getInstance()->isInt($post))
            return null;
        if(!permission::getInstance()->have('global/owner'))
            return null;
        $params['post']['thread_id'] = $return_thread_id;
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_forum_post WHERE post_id = ?");
        $stmt->bindParam(1, $post, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        if($result[0] != 1)
            return null;
        $stmt = null;
        if(system::getInstance()->post('submit_delete')) {
            $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_com_forum_post WHERE post_id = ?");
            $stmt->bindParam(1, $post, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            // update thread info - last post & post count
            // get new info about last post
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_forum_post WHERE post_target_thread = ? ORDER BY post_time DESC LIMIT 1");
            $stmt->bindParam(1, $return_thread_id, \PDO::PARAM_INT);
            $stmt->execute();
            if($stmt->rowCount() == 1) {
                // info about last post is available.
                $res = $stmt->fetch();
                $last_poster = $res['post_userid'];
                $last_time = $res['post_time'];
                $last_nick = user::getInstance()->get('nick', $last_poster);
                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_forum_thread SET thread_updatetime = ?, thread_updater = ?, thread_post_count = thread_post_count-1 WHERE thread_id = ?");
                $stmt->bindParam(1, $last_time, \PDO::PARAM_INT);
                $stmt->bindParam(2, $last_nick, \PDO::PARAM_STR);
                $stmt->bindParam(3, $return_thread_id, \PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
            } else {
                // no info of latest post, set thread self info
                $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_forum_thread WHERE thread_id = ?");
                $stmt->bindParam(1, $return_thread_id, \PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetch();
                $stmt = null;
                if($res[0] == 1) {
                    // update thread info
                    $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_forum_thread SET thread_updatetime = thread_starttime, thread_updater = thread_owner WHERE thread_id = ?");
                    $stmt->bindParam(1, $return_thread_id, \PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                }
            }
            $stmt = null;
            // update(rebuild) forum data - post count & last poster
            // 1st - get last info
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_forum_post
                WHERE post_target_thread in (SELECT thread_id FROM ".property::getInstance()->get('db_prefix')."_com_forum_thread WHERE thread_forum_depend = ?)
                ORDER BY post_time DESC LIMIT 1");
            $stmt->bindParam(1, $forum_id, \PDO::PARAM_INT);
            $stmt->execute();
            if($stmt->rowCount() == 1) {
                $res = $stmt->fetch();
                $stmt = null;
                $last_thread_id = $res['post_target_thread'];
                $last_poster = $res['post_userid'];
                $last_time = $res['post_time'];
                $last_nick = user::getInstance()->get('nick', $last_poster);
                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_forum_item SET forum_posts = forum_posts-1, forum_lastposter = ?, forum_lastupdate = ?, forum_lasttopic_id = ? WHERE forum_id = ?");
                $stmt->bindParam(1, $last_nick, \PDO::PARAM_STR);
                $stmt->bindParam(2, $last_time, \PDO::PARAM_INT);
                $stmt->bindParam(3, $last_thread_id, \PDO::PARAM_INT);
                $stmt->bindParam(4, $forum_id, \PDO::PARAM_INT);
                $stmt->execute();
            } else {
                // no posts in thread, select thread info
                $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_forum_thread WHERE thread_forum_depend = ? ORDER BY thread_updatetime DESC LIMIT 1");
                $stmt->bindParam(1, $forum_id, \PDO::PARAM_INT);
                $stmt->execute();
                if($stmt->rowCount() == 1) {
                    $res = $stmt->fetch();
                    $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_forum_item SET forum_posts = forum_posts-1, forum_lastposter = ?, forum_lastupdate = ?, forum_lasttopic_id = ? WHERE forum_id = ?");
                    $stmt->bindParam(1, $res['thread_updater'], \PDO::PARAM_STR);
                    $stmt->bindParam(2, $res['thread_updatetime'], \PDO::PARAM_INT);
                    $stmt->bindParam(3, $res['thread_id'], \PDO::PARAM_INT);
                    $stmt->bindParam(4, $forum_id, \PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
            system::getInstance()->redirect('/forum/viewtopic/' . $return_thread_id);
        }
        return template::getInstance()->twigRender('components/forum/post_delete.tpl', $params);
    }

    private function viewAddThread($id) {
        if(!permission::getInstance()->have('forum/thread'))
            return null;
        meta::getInstance()->add('title', language::getInstance()->get('forum_addthread_title'));
        $params = array();
        if(system::getInstance()->post('forum_submit')) {
            $thread_title = system::getInstance()->nohtml(system::getInstance()->post('forum_title'));
            $thread_message = system::getInstance()->nohtml(system::getInstance()->post('forum_message'));
            $thread_time = time();
            $thread_owner_id = user::getInstance()->get('id');
            $thread_owner_nick = user::getInstance()->get('nick');
            $title_length = system::getInstance()->length($thread_title);
            $notify = null;
            $lastpost_time = user::getInstance()->get('forum_last_message');
            $delay_post = extension::getInstance()->getConfig('post_delay', 'forum', extension::TYPE_COMPONENT, 'int');
            if(permission::getInstance()->have('forum/moderator'))
                $delay_post = 1;
            if($delay_post < 15) {
                $delay_post = 15;
            }
            if($title_length < 3 || $title_length > 128) {
                $params['notify']['forum_add_title_incorrent'] = true;
            }
            if(system::getInstance()->length($thread_message) < 10) {
                $params['notify']['forum_add_text_incorrent'] = true;
            }
            if(time()-$lastpost_time < $delay_post) {
                $params['notify']['forum_error_spam'] = true;
            }
            if(sizeof($params['notify']) < 1) {
                $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_forum_item WHERE forum_id = ?");
                $stmt->bindParam(1, $id, \PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetch();
                $foundForumCount = $res[0];
                $stmt = null;
                // существует ли ?
                if($foundForumCount == 1) {
                    $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_com_forum_thread (`thread_title`, `thread_body`, `thread_starttime`, `thread_updatetime`, `thread_owner`, `thread_important`, `thread_updater`, `thread_starterid`, `thread_forum_depend`)
                    VALUES(?, ?, ?, ?, ?, 0, ?, ?, ?)");
                    $stmt->bindParam(1, $thread_title, \PDO::PARAM_STR);
                    $stmt->bindParam(2, $thread_message, \PDO::PARAM_STR);
                    $stmt->bindParam(3, $thread_time, \PDO::PARAM_INT);
                    $stmt->bindParam(4, $thread_time, \PDO::PARAM_INT);
                    $stmt->bindParam(5, $thread_owner_nick, \PDO::PARAM_STR);
                    $stmt->bindParam(6, $thread_owner_nick, \PDO::PARAM_STR);
                    $stmt->bindParam(7, $thread_owner_id, \PDO::PARAM_INT);
                    $stmt->bindParam(8, $id, \PDO::PARAM_INT);
                    $stmt->execute();
                    $thread_new_id = database::getInstance()->con()->lastInsertId('thread_id');
                    $stmt = null;
                    $this->updatePostCount($thread_owner_id);
                    $this->updateForumThreadCount($id, $thread_owner_nick, $thread_new_id);
                    system::getInstance()->redirect('/forum/viewtopic/'.$thread_new_id);
                }
            } else {
                $params['saveform']['title'] = $thread_title;
                $params['saveform']['text'] = $thread_message;
            }
        }
        return template::getInstance()->twigRender('components/forum/addthread.tpl', $params);
    }

    private function viewTopic($id, $page = 0) {
        $params = array();
        if(system::getInstance()->post('forum_submit') && system::getInstance()->length(system::getInstance()->post('forum_message')) > 0 && permission::getInstance()->have('forum/post')) {
            $post_message = system::getInstance()->nohtml(system::getInstance()->post('forum_message'));
            $time = time();
            $lastpost_time = user::getInstance()->get('forum_last_message');
            $delay_post = extension::getInstance()->getConfig('post_delay', 'forum', extension::TYPE_COMPONENT, 'int');
            if(permission::getInstance()->have('forum/moderator'))
                $delay_post = 1;
            if($time-$lastpost_time < $delay_post) {
                $params['notify']['forum_error_spam'] = true;
                $params['forum']['save_form']['message'] = $post_message;
            }
            if(user::getInstance()->get('id') > 0 && sizeof($params['notify']) < 1) {
                $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*),thread_forum_depend FROM ".property::getInstance()->get('db_prefix')."_com_forum_thread WHERE thread_id = ?");
                $stmt->bindParam(1, $id, \PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetch();
                $countThreadFound = $res[0];
                $forum_id = $res[1];
                $stmt = null;
                if($countThreadFound == 1) {
                    $userid = user::getInstance()->get('id');
                    $usernick = user::getInstance()->get('nick');
                    $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_com_forum_post (`post_target_thread`, `post_message`, `post_userid`, `post_time`) VALUES(?, ?, ?, ?)");
                    $stmt->bindParam(1, $id, \PDO::PARAM_INT);
                    $stmt->bindParam(2, $post_message, \PDO::PARAM_STR);
                    $stmt->bindParam(3, $userid, \PDO::PARAM_INT);
                    $stmt->bindParam(4, $time, \PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt = null;
                    $this->updatePostCount($userid);
                    $this->updateForumPostCount($forum_id, $usernick, $id);
                    $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_forum_thread SET thread_updatetime = ?, thread_updater = ? WHERE thread_id = ?");
                    $stmt->bindParam(1, $time, \PDO::PARAM_INT);
                    $stmt->bindParam(2, $usernick, \PDO::PARAM_STR);
                    $stmt->bindParam(3, $id, \PDO::PARAM_STR);
                    $stmt->execute();
                }
            }
        }
        // стартовый мессадж в топике
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_forum_thread WHERE thread_id = ?");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
        if($stmt->rowCount() != 1) {
            return null;
        }
        $resultMainThread = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = null;
        $post_on_page = extension::getInstance()->getConfig('post_in_topic', 'forum', extension::TYPE_COMPONENT, 'int');
        if($post_on_page < 1)
            $post_on_page = 10;
        $post_sql_starter = $page * $post_on_page;
        // сообщения в топике
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_forum_post WHERE post_target_thread = ? ORDER BY post_id ASC LIMIT ?,?");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->bindParam(2, $post_sql_starter, \PDO::PARAM_INT);
        $stmt->bindParam(3, $post_on_page, \PDO::PARAM_INT);
        $stmt->execute();
        $resultPostThread = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;
        // название форума для breadcrumbs
        $stmt = database::getInstance()->con()->prepare("SELECT forum_id,forum_name FROM ".property::getInstance()->get('db_prefix')."_com_forum_item WHERE forum_id = ?");
        $stmt->bindParam(1, $resultMainThread['thread_forum_depend']);
        $stmt->execute();
        $resultForumItem = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt = null;
        $params['forum']['name'] = $resultForumItem['forum_name'];
        $params['forum']['id'] = $resultForumItem['forum_id'];
        $params['forum']['thread']['id'] = $id;
        $params['forum']['use_karma'] = extension::getInstance()->getConfig('use_karma', 'user', extension::TYPE_COMPONENT, 'bol');
        // уменьшаем кол-во запросов на получение информации о пользователях, выгружаем заранее
        $userlist = system::getInstance()->extractFromMultyArray('post_userid', $resultPostThread);
        $userlist = system::getInstance()->arrayAdd($resultMainThread['thread_starterid'], $userlist);
        user::getInstance()->listload($userlist);
        $params['forum']['topic']['title'] = $resultMainThread['thread_title'];
        meta::getInstance()->add('title', $params['forum']['topic']['title']);
        // счетчик нумерации сообщения
        $post_number = 1;
        // это не первая страница
        if($page > 0) {
            $post_number += $post_sql_starter;
        } else { // иначе отображаем инициирующий пост трида
            $params['forum']['post'][] = array(
                'thread_id' => $resultMainThread['thread_id'],
                'post_text' => extension::getInstance()->call(extension::TYPE_HOOK, 'bbtohtml')->bbcode2html($resultMainThread['thread_body']),
                'user_id' => $resultMainThread['thread_starterid'],
                'user_avatar' => user::getInstance()->buildAvatar('small', $resultMainThread['thread_starterid']),
                'user_name' => user::getInstance()->get('nick', $resultMainThread['thread_starterid']),
                'user_posts' => user::getInstance()->get('forum_posts', $resultMainThread['thread_starterid']),
                'user_karma' => user::getInstance()->get('karma', $resultMainThread['thread_starterid']),
                'can_change_karma' => user::getInstance()->canKarmaChange($resultMainThread['thread_starterid']),
                'user_group' => user::getInstance()->get('group_name', $resultMainThread['thread_starterid']),
                'post_time' => system::getInstance()->toDate($resultMainThread['thread_starttime'], 'h'),
                'number' => 0,
                'thread_important' => $resultMainThread['thread_important']
            );
        }
        foreach($resultPostThread as $post) {
            echo $post['message'];
            $params['forum']['post'][] = array(
                'post_id' => $post['post_id'],
                'post_text' => extension::getInstance()->call(extension::TYPE_HOOK, 'bbtohtml')->bbcode2html($post['post_message']),
                'user_id' => $post['post_userid'],
                'user_avatar' => user::getInstance()->buildAvatar('small', $post['post_userid']),
                'user_name' => user::getInstance()->get('nick', $post['post_userid']),
                'user_posts' => user::getInstance()->get('forum_posts', $post['post_userid']),
                'user_karma' => user::getInstance()->get('karma', $post['post_userid']),
                'can_change_karma' => user::getInstance()->canKarmaChange($post['post_userid']),
                'user_group' => user::getInstance()->get('group_name', $post['post_userid']),
                'post_time' => system::getInstance()->toDate($post['post_time'], 'h'),
                'number' => $post_number
            );
            $post_number++;
        }
        $this->updateViewCount($id);
        $params['pagination'] = template::getInstance()->showFastPagination($page, $post_on_page, $this->totalPostInThread($id), 'forum/viewtopic/'.$id);
        $params['permission']['add_post'] = permission::getInstance()->have('forum/post');
        return template::getInstance()->twigRender('components/forum/topic.tpl', $params);
    }

    private function viewBoard($id, $page = 0) {
        if(!system::getInstance()->isInt($id))
            return null;
        $params = array();

        $item_per_page = extension::getInstance()->getConfig('topic_in_forum', 'forum', extension::TYPE_COMPONENT, 'int');
        if($item_per_page < 1)
            $item_per_page = 10;
        $db_index = $page * $item_per_page;

        $stmt = database::getInstance()->con()->prepare("SELECT a.*, b.forum_id, b.forum_name FROM ".property::getInstance()->get('db_prefix')."_com_forum_thread a,
            ".property::getInstance()->get('db_prefix')."_com_forum_item b WHERE a.thread_forum_depend = ? AND a.thread_forum_depend = b.forum_id ORDER BY a.thread_important DESC, a.thread_updatetime DESC LIMIT ?, ?");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->bindParam(2, $db_index, \PDO::PARAM_INT);
        $stmt->bindParam(3, $item_per_page, \PDO::PARAM_INT);
        $stmt->execute();
        if($stmt->rowCount() < 1) {
            $stmt = null;
            $stmt = database::getInstance()->con()->prepare("SELECT forum_name FROM ".property::getInstance()->get('db_prefix')."_com_forum_item WHERE forum_id = ?");
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            if($stmt->rowCount() < 1)
                return null;
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt = null;
            $params['forum']['name'] = $result['forum_name'];
            $params['forum']['id'] = $id;
        } else {
            while($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $params['forum']['name'] = $result['forum_name'];
                $params['forum']['id'] = $result['forum_id'];
                $params['forum']['topics'][] = array(
                    'title' => $result['thread_title'],
                    'owner_name' => $result['thread_owner'],
                    'update' => system::getInstance()->toDate($result['thread_updatetime'], 'h'),
                    'create' => system::getInstance()->toDate($result['thread_starttime'], 'h'),
                    'last_user' => $result['thread_updater'],
                    'posts' => $result['thread_post_count'],
                    'views' => $result['thread_view_count'],
                    'id' => $result['thread_id'],
                    'important' => $result['thread_important']
                );
            }
        }
        meta::getInstance()->add('title', $params['forum']['name']);
        $stmt = null;
        $params['pagination'] = template::getInstance()->showFastPagination($page, $item_per_page, $this->totalForumThreadCount($id), 'forum/viewboard/'.$id);
        $params['permission']['create_thread'] = permission::getInstance()->have('forum/thread');
        return template::getInstance()->twigRender('components/forum/board.tpl', $params);
    }

    private function viewMain() {
        $params = array();

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
                    $lastpost = null;
                    if($forum['forum_lastupdate'] > 0) {
                        $lastpost = system::getInstance()->toDate($forum['forum_lastupdate'], 'h');
                    }
                    $params['forum'][$order_id]['forums'][] = array(
                        'id' => $forum['forum_id'],
                        'title' => $forum['forum_name'],
                        'desc' => $forum['forum_desc'],
                        'threads' => $forum['forum_threads'],
                        'posts' => $forum['forum_posts'],
                        'lastupdate' => $lastpost,
                        'lasttopic_id' => $forum['forum_lasttopic_id'],
                        'lastposter_name' => $forum['forum_lastposter']
                    );
                }
            }
        }
        if(sizeof($params['forum']) > 0)
            ksort($params['forum']);
        return template::getInstance()->twigRender('components/forum/main.tpl', $params);
    }


    private function reCountForum($forum_id = 0) {
        if(!system::getInstance()->isInt($forum_id))
            return null;
        $stmt = null;
        if($forum_id == 0) {
            $stmt = database::getInstance()->con()->query("SELECT f.forum_id,COUNT(DISTINCT t.thread_id) as count_thread,COUNT(p.post_id) as count_post,t.thread_id,t.thread_updatetime,t.thread_updater
                FROM ".property::getInstance()->get('db_prefix')."_com_forum_item as f
                LEFT OUTER JOIN ".property::getInstance()->get('db_prefix')."_com_forum_thread as t ON f.forum_id = t.thread_forum_depend
                LEFT OUTER JOIN ".property::getInstance()->get('db_prefix')."_com_forum_post as p ON t.thread_id = p.post_target_thread
                GROUP BY f.forum_id
				ORDER BY t.thread_updatetime DESC,p.post_time DESC");
        } else {
            $stmt = database::getInstance()->con()->prepare("SELECT f.forum_id,COUNT(DISTINCT t.thread_id) as count_thread,COUNT(p.post_id) as count_post,t.thread_id,t.thread_updatetime,t.thread_updater
                FROM ".property::getInstance()->get('db_prefix')."_com_forum_item as f
                LEFT OUTER JOIN ".property::getInstance()->get('db_prefix')."_com_forum_thread as t ON f.forum_id = t.thread_forum_depend
                LEFT OUTER JOIN ".property::getInstance()->get('db_prefix')."_com_forum_post as p ON t.thread_id = p.post_target_thread
                WHERE f.forum_id = ?
                GROUP BY f.forum_id
				ORDER BY t.thread_updatetime DESC,p.post_time DESC");
            $stmt->bindParam(1, $forum_id, \PDO::PARAM_INT);
            $stmt->execute();
        }
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;
        foreach($result as $row) {
            $stmt = database::getInstance()->con()->prepare("UPDATE " . property::getInstance()->get('db_prefix') . "_com_forum_item
                    SET forum_threads = ?, forum_posts = ?, forum_lastposter = ?, forum_lastupdate = ?, forum_lasttopic_id = ? WHERE forum_id = ?");
            if ($row['thread_updater'] == null)
                $row['thread_updater'] = '';
            $stmt->bindParam(1, $row['count_thread'], \PDO::PARAM_INT);
            $stmt->bindParam(2, $row['count_post'], \PDO::PARAM_INT);
            $stmt->bindParam(3, $row['thread_updater'], \PDO::PARAM_STR);
            $stmt->bindParam(4, $row['thread_updatetime'], \PDO::PARAM_INT);
            $stmt->bindParam(5, $row['thread_id'], \PDO::PARAM_INT);
            $stmt->bindParam(6, $row['forum_id'], \PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
        }
    }

    private function updatePostCount($userid)
    {
        $time = time();
        $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user_custom SET forum_posts = forum_posts+1, forum_last_message = ? WHERE id = ?");
        $stmt->bindParam(1, $time, \PDO::PARAM_INT);
        $stmt->bindParam(2, $userid, \PDO::PARAM_INT);
        $stmt->execute();
        $stmt = null;
    }

    private function updateForumPostCount($id, $usernick, $thread_id)
    {
        $time = time();
        $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_forum_item SET forum_posts = forum_posts+1, forum_lastposter = ?, forum_lastupdate = ?, forum_lasttopic_id = ?  WHERE forum_id = ?");
        $stmt->bindParam(1, $usernick, \PDO::PARAM_STR);
        $stmt->bindParam(2, $time, \PDO::PARAM_INT);
        $stmt->bindParam(3, $thread_id, \PDO::PARAM_INT);
        $stmt->bindParam(4, $id, \PDO::PARAM_INT);
        $stmt->execute();
        $stmt = null;
        $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_forum_thread set thread_post_count = thread_post_count+1 WHERE thread_id = ?");
        $stmt->bindParam(1, $thread_id, \PDO::PARAM_INT);
        $stmt->execute();
        $stmt = null;
    }

    private function updateForumThreadCount($id, $usernick, $thread_id)
    {
        $time = time();
        $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_forum_item SET forum_threads = forum_threads+1, forum_lastposter = ?, forum_lastupdate = ?, forum_lasttopic_id = ? WHERE forum_id = ?");
        $stmt->bindParam(1, $usernick, PDO::PARAM_STR);
        $stmt->bindParam(2, $time, PDO::PARAM_INT);
        $stmt->bindParam(3, $thread_id, PDO::PARAM_INT);
        $stmt->bindParam(4, $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt = null;
    }

    private function updateViewCount($thread_id)
    {
        $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_forum_thread SET thread_view_count = thread_view_count+1 WHERE thread_id = ?");
        $stmt->bindParam(1, $thread_id, \PDO::PARAM_INT);
        $stmt->execute();
        $stmt = null;
    }

    private function totalForumThreadCount($forum_id) {
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_forum_thread WHERE thread_forum_depend = ?");
        $stmt->bindParam(1, $forum_id, \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();
        $stmt = null;
        return $res[0];
    }

    private function totalPostInThread($thread_id) {
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_forum_post WHERE post_target_thread = ?");
        $stmt->bindParam(1, $thread_id, \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();
        $stmt = null;
        return $res[0];
    }
}