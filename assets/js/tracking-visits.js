jQuery(document).ready(
    function ($) {


        var visited_cookie = Cookies.get("affwp_visit_id");
        var affiliate_id = Cookies.get("affwp_erl_id")



        if (visited_cookie ) {
            var url = awp_track_visit_var.url, target_urls = $("a[href^='" + url + "']");
            var referral_variable = awp_track_visit_var.referral_variable;
            $(target_urls).each(
                function () {
                    current_url = $(this).attr("href"), $(this).attr("href", updateQueryStringParameter(current_url, referral_variable, affiliate_id) + "&visited_id=" + visited_cookie);
                }
            );
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