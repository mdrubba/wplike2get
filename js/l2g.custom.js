jQuery(document).ready(function ($) {
    if (l2g_options.keep_after_reload == true && contentWasLiked() == true) {
        getDownloadLink();
    }

    if (l2g_options.fb_activated) {
        if (l2g_options.fb_hide == true && contentWasLiked() == true) {
            // do nothing
        } else {
            showFacebook()
        }
    }

    if (l2g_options.tw_activated) {
        if (l2g_options.tw_hide == true && contentWasLiked() == true) {
            // do nothing
        } else {
            showTwitter()
        }
    }

    if (l2g_options.gp_activated) {
        if (l2g_options.gp_hide == true && contentWasLiked() == true) {
            // do nothing
        } else {
            showGooglePlus()
        }
    }

    function contentWasLiked() {
        return $.cookie('_liked' + l2g_options.cookie_suffix) == 'liked';
    }

    function showFacebook() {
        $('#l2g .facebook').fbjlike({
            appID:l2g_options.fb_appID,
            userID:l2g_options.fb_userID,
            siteName:l2g_options.fb_siteName,
            buttonWidth:l2g_options.fb_buttonWidth,
            buttonHeight:l2g_options.fb_buttonHeight,
            showfaces:Boolean(l2g_options.fb_showfaces),
            send:Boolean(l2g_options.fb_send),
            comments:Boolean(l2g_options.fb_comments),
            font:l2g_options.fb_font,
            layout:l2g_options.fb_layout,
            action:l2g_options.fb_action,
            colorscheme:l2g_options.fb_colorscheme,
            lang:l2g_options.fb_lang,
            hideafterlike:Boolean(l2g_options.fb_hide),
            googleanalytics:Boolean(l2g_options.fb_ga),
            googleanalytics_obj:l2g_options.fb_ga_version,
            onlike:function (response) {
                getDownloadLink();
                $.cookie('_liked' + l2g_options.cookie_suffix, 'liked');
            },
            onunlike:function (response) {
                removeDownloadLink();
                $.cookie('_liked' + l2g_options.cookie_suffix, 'unliked');
            }
        });
    }

    function showTwitter() {
        $('#l2g .twitter').twitterbutton({
            user:l2g_options.tw_user,
            user_description:l2g_options.tw_user_description,
            url:l2g_options.tw_url,
            count_url:l2g_options.tw_count_url,
            title:l2g_options.tw_title,
            layout:l2g_options.tw_layout,
            action:l2g_options.tw_action,
            lang:l2g_options.tw_lang,
            hideafterlike:Boolean(l2g_options.tw_hide),
            googleanalytics:Boolean(l2g_options.tw_ga),
            googleanalytics_obj:l2g_options.tw_ga_version,
            ontweet:function (response) {
                getDownloadLink();
                $.cookie('_liked' + l2g_options.cookie_suffix, 'liked');
            },
            onfollow:function (response) {
                getDownloadLink();
                $.cookie('_liked' + l2g_options.cookie_suffix, 'liked');
            }
        });
    }

    function showGooglePlus() {
        if (!l2g_options.gp_url)
            var gpUrl = document.location;
        else
            var gpUrl = l2g_options.gp_url;

        $('#l2g .gplusone').gplusone({
            size:l2g_options.gp_size,
            count:l2g_options.gp_count,
            href:gpUrl,
            lang:l2g_options.gp_lang,
            hideafterlike:Boolean(l2g_options.gp_hide),
            googleanalytics:Boolean(l2g_options.gp_ga),
            googleanalytics_obj:l2g_options.gp_ga_version,
            onlike:"jQuery.cookie('_liked' + l2g_options.cookie_suffix, 'liked');jQuery('.l2g-hidden-content').show();jQuery.post(l2g_options.ajaxurl,{action : 'l2g-get-download-link',id: jQuery(\"#l2g\").attr(\"class\")},function( response ) {jQuery('#l2g-download-link a').first().attr('href', response.link);jQuery('#l2g-download-link').show();});",
            onunlike:"jQuery.cookie('_liked' + l2g_options.cookie_suffix, 'unliked');jQuery('.l2g-hidden-content').hide();jQuery('#l2g-download-link').hide();jQuery('#l2g-download-link a').first().attr('href', '');"
        });
    }

    function getDownloadLink() {
        $('.l2g-hidden-content').show();
        $.post(
            l2g_options.ajaxurl,
            {
                action:'l2g-get-download-link',
                id:$("#l2g").attr("class")
            },
            function (response) {
                $('#l2g-download-link a').first().attr('href', response.link);
                $('#l2g-download-link').show();
            }
        );
    }

    function removeDownloadLink() {
        $('.l2g-hidden-content').hide();
        $('#l2g-download-link').hide();
        $('#l2g-download-link a').first().attr('href', '');
    }
});