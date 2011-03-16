<?php
// TODO xmultiquote: MOD compliance file header

/**
 * Description of functions
 *
 * @author jafar
 */
function xmultiquote_get_ids() {
    $xmessages = array();
    if (isset($_COOKIE['xmultiquote_xmessages'])) {
        $xmessages = json_decode($_COOKIE['xmultiquote_xmessages']);
        if (is_object($xmessages)) {
            xmultiquote_clear();
            $xmessages = array();
        }
    }
    return $xmessages;
}

function xmultiquote_get_post($post_id) {
    global $db, $auth;
    $sql = 'SELECT forum_id
			FROM ' . POSTS_TABLE . '
			WHERE post_id = ' . $post_id;
    $result = $db->sql_query($sql);
//    echo '<pre>';
//    print_r($db->sql_fetchrow());
//    echo '</pre>';
    $forum_id = (int) $db->sql_fetchfield('forum_id');
    $db->sql_freeresult($result);
//    $forum_id = (!$f_id) ? $forum_id : $f_id;

    $sql = 'SELECT f.*, t.*, p.*, u.username, u.username_clean, u.user_sig, u.user_sig_bbcode_uid, u.user_sig_bbcode_bitfield
			FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f, ' . USERS_TABLE . " u
			WHERE p.post_id = $post_id
				AND t.topic_id = p.topic_id
				AND u.user_id = p.poster_id
				AND (f.forum_id = t.forum_id
					OR f.forum_id = $forum_id)" .
            (($auth->acl_get('m_approve', $forum_id)) ? '' : 'AND p.post_approved = 1');

    $result = $db->sql_query($sql);
    $post_data = $db->sql_fetchrow($result);


    if (isset($post_data['poster_id']) && $post_data['poster_id'] == ANONYMOUS) {
        $post_data['quote_username'] = (!empty($post_data['post_username'])) ? $post_data['post_username'] : $user->lang['GUEST'];
    } else {
        $post_data['quote_username'] = isset($post_data['username']) ? $post_data['username'] : '';
    }

    return $post_data;
}

function xmultiquote_get_xmessage_string($message_parser) {
    global $config;
    $ids = xmultiquote_get_ids();

    $xmessages = array();
    foreach ($ids as $id) {
        $post_data = xmultiquote_get_post($id);

        if ($config['allow_bbcode']) {
            $message = '[quote=&quot;' . $post_data['quote_username'] . '&quot;]' . censor_text(trim($post_data['post_text'])) . "[/quote]\n";
        } else {
            $offset = 0;
            $quote_string = "&gt; ";
            $message = censor_text(trim($post_data['post_text']));
            // see if we are nesting. It's easily tricked but should work for one level of nesting
            if (strpos($message, "&gt;") !== false) {
                $offset = 10;
            }
            $message = utf8_wordwrap($message, 75 + $offset, "\n");

            $message = $quote_string . $message;
            $message = str_replace("\n", "\n" . $quote_string, $message);
            $message =  $post_data['quote_username'] . " " . $user->lang['WROTE'] . ":\n" . $message . "\n";
        }

        $message_parser->message = $message;
        $message_parser->decode_message($post_data['bbcode_uid']);

        $message = $message_parser->message;

        $xmessages[] = $message;
    }

    return implode("\n\n", $xmessages);
}

function xmultiquote_is_quoted($post_data) {
    $key = $post_data['post_id'];
    if (empty($key)) {
        $key = $post_data['POST_ID'];
    }
    if (empty($key)) {
        echo "Terjadi error";
        return false;
    }
    $key = intval($key);
    
    if ($key) {
        $xmessages = xmultiquote_get_ids();

        if (array_search($key, $xmessages) !== false) {
            return true;
        } else {
            return false;
        }
    }
    return false;
}

function xmultiquote_clear() {
    setcookie('xmultiquote_xmessages', NULL, 0, '/');
}