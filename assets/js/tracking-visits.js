jQuery(document).ready(
    function ($) {

        function getCookie(cname) {
            var name = cname + "=";
            var decodedCookie = decodeURIComponent(document.cookie);
            var ca = decodedCookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }

        // Read a query-string parameter from the current URL.
        function getQueryParam(name) {
            var re = new RegExp("[?&]" + name + "=([^&#]*)", "i");
            var match = window.location.search.match(re);
            return match ? decodeURIComponent(match[1].replace(/\+/g, " ")) : "";
        }

        // Append the referral variables to every link that points to the
        // parent (store) site so the referral is carried across domains.
        function rewriteLinks(affiliate_id, visited_cookie, campaign_cookie) {
            if (!affiliate_id) {
                return;
            }
            var url = awp_track_visit_var.url;
            if (url.substr(-1) == '/') {
                url = url.substr(0, url.length - 1);
            }
            var target_urls = $("a[href^='" + url + "']");
            var referral_variable = awp_track_visit_var.referral_variable;
            $(target_urls).each(
                function () {
                    var current_url = $(this).attr("href");
                    current_url = updateQueryStringParameter(current_url, referral_variable, affiliate_id);
                    if (visited_cookie) {
                        current_url = current_url + "&visit=" + visited_cookie;
                    }
                    if (campaign_cookie) {
                        current_url = current_url + "&campaign=" + campaign_cookie;
                    }
                    $(this).attr("href", current_url);
                }
            );
        }

        var referral_variable = awp_track_visit_var.referral_variable;
        var url_affiliate_id = getQueryParam(referral_variable);
        var cookie_affiliate_id = getCookie("affwp_affiliate_id");

        // If the referral variable is present in the URL but the cookie is
        // missing (e.g. the server-side Set-Cookie header was stripped by a
        // full-page cache / CDN) or points to a different affiliate, record the
        // visit over a non-cacheable AJAX request so the cookies are set
        // reliably. The API keys stay server-side.
        if (url_affiliate_id && url_affiliate_id !== cookie_affiliate_id && awp_track_visit_var.ajaxurl) {

            var payload = {
                action: 'cdtawp_track_visit',
                campaign: getQueryParam('campaign'),
                url: window.location.href,
                referrer: document.referrer
            };
            payload[referral_variable] = url_affiliate_id;

            $.post(awp_track_visit_var.ajaxurl, payload)
                .done(
                    function (response) {
                        var affiliate_id = url_affiliate_id;
                        var visit_id = getCookie("affwp_visit_id");
                        var campaign = getCookie("affwp_campaign");
                        if (response && response.success && response.data) {
                            affiliate_id = response.data.affiliate_id || affiliate_id;
                            visit_id = response.data.visit_id || visit_id;
                            campaign = response.data.campaign || campaign;
                        }
                        rewriteLinks(affiliate_id, visit_id, campaign);
                    }
                )
                .fail(
                    function () {
                        rewriteLinks(getCookie("affwp_affiliate_id"), getCookie("affwp_visit_id"), getCookie("affwp_campaign"));
                    }
                );
        } else {
            rewriteLinks(getCookie("affwp_affiliate_id"), getCookie("affwp_visit_id"), getCookie("affwp_campaign"));
        }

        function updateQueryStringParameter(uri, ref_var, aff_id)
        {
            var re = new RegExp("([?|&])" + ref_var + "=.*?(&|#|$)", "i");
            if (uri.match(re)) { return uri.replace(re, "$1" + ref_var + "=" + aff_id + "$2");
            }
            var hash = "", separator = -1 !== uri.indexOf("?") ? "&" : "?";
            return -1 !== uri.indexOf("#") && (hash = uri.replace(/.*#/, "#"), uri = uri.replace(/#.*/, "")), uri + separator + ref_var + "=" + aff_id + hash
        }


    }
);
