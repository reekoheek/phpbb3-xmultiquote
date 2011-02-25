<?php
// TODO xmultiquote: MOD compliance file header

/**
 * Description of functions
 *
 * @author jafar
 */
function xmultiquote_get_xmessages() {
    $xmessages = new stdClass();
    if (isset($_COOKIE['xmultiquote_xmessages'])) {
        $xmessages = json_decode($_COOKIE['xmultiquote_xmessages']);
    } else {
        setcookie('xmultiquote_xmessages', json_encode($xmessages, JSON_FORCE_OBJECT), 0, '/');
    }
    return $xmessages;
}

function xmultiquote_get_xmessage_string() {
    $xmessages = get_object_vars(xmultiquote_get_xmessages());
    return implode("\n\n", $xmessages);
}

function xmultiquote_set_xmessages($post_data, $xmessage) {
    $xmessages = xmultiquote_get_xmessages();
    if (!xmultiquote_is_quoted($post_data)) {
        $key = xmultiquote_get_key($post_data);
        $xmessages->$key = $xmessage;
        setcookie('xmultiquote_xmessages', json_encode($xmessages, JSON_FORCE_OBJECT), 0, '/');
    }
}

function xmultiquote_get_key($post_data) {
    $key = $post_data['post_id'];
    if (empty($key)) {
        $key = $post_data['POST_ID'];
    }

    if (empty($key)) {
        echo "Terjadi error";
        return false;
    }
    return 'XQUOTED_'.$key;
}

function xmultiquote_is_quoted($post_data) {
    $key = xmultiquote_get_key($post_data);
    if ($key) {
        $xmessages = xmultiquote_get_xmessages();
//        print_r($xmessages);
        return (!empty($xmessages->$key));
    }
    return false;
}
