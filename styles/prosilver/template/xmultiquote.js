function xmultiquote_quote(postId, isQuote) {
    var toJSON = function(arr) {
        return '[' + arr.join(',') + ']';
        
    };

    var getCookie= function(key, isRaw) {
        if (typeof(isRaw) == 'undefined') isRaw = false;
        var result, decode = isRaw ? function (s) {
            return s;
        } : decodeURIComponent;
        return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? decode(result[1]) : null;
    };

    var setCookie= function(key, value, isRaw) {
        if (typeof(isRaw) == 'undefined') isRaw = false;
        document.cookie = [
        encodeURIComponent(key), '=',
        isRaw ? String(value) : encodeURIComponent(String(value)),
        '; path=/'
        ].join('');
    };

    var xmessages = jQuery.parseJSON(getCookie('xmultiquote_xmessages'));
    if (xmessages == null) xmessages = [];
    
    var found = false;

    if (isQuote) {
        for(i=0;i<xmessages.length;i++) {
            if (xmessages[i] == postId) {
                found = true;
                break;
            }
        }
        if (!found) {
            xmessages.push(postId);
            setCookie('xmultiquote_xmessages', toJSON(xmessages));
            return true;
        }
    } else {
        var newXmessages = [];
        
        for(i=0;i<xmessages.length;i++) {
            if (xmessages[i] != postId) {
                newXmessages.push(xmessages[i]);
            } else {
                found = true;
            }
        }
        if (found) {
            setCookie('xmultiquote_xmessages', toJSON(newXmessages));
            return true;
        }
    }
    return false;
}

$(function() {
    $('.xquote-icon, .xquoted-icon').click(function(evt) {
        evt.preventDefault();
        var tobeQuote = $(this).hasClass('xquote-icon');

        if (xmultiquote_quote($(this).attr('postId'), tobeQuote)) {
            if (tobeQuote) {
                $(this).html('MULTIQUOTED').removeClass('xquote-icon').addClass('xquoted-icon');
            } else {
                $(this).html('MULTIQUOTE').removeClass('xquoted-icon').addClass('xquote-icon');
            }
        }
        return false;
    });
});