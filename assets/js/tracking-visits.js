jQuery(document).ready(
    function ($) {
    	
	    function getCookie(cname) {
		    var name = cname + "=";
		    var decodedCookie = decodeURIComponent(document.cookie);
		    var ca = decodedCookie.split(';');
		    for(var i = 0; i <ca.length; i++) {
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
    	
        var visited_cookie = getCookie("affwp_visit_id");
        var affiliate_id = getCookie("affwp_affiliate_id")
        var campaign_cookie = getCookie("affwp_campaign")

        if ( affiliate_id ) {
            var url = awp_track_visit_var.url;
	            if(url.substr(-1) == '/') {
		            url = url.substr(0, url.length - 1);
	        }
	        target_urls = $("a[href^='" + url + "']");
            var referral_variable = awp_track_visit_var.referral_variable;
            $(target_urls).each(
                function () {
                    current_url = $(this).attr("href");
	                current_url = updateQueryStringParameter(current_url, referral_variable, affiliate_id);
	                if ( visited_cookie ) {
		                current_url = current_url + "&visit=" + visited_cookie;
	                }
	                if ( campaign_cookie ) {
		                current_url = current_url + "&campaign=" + campaign_cookie;
	                }
                    $(this).attr("href", current_url );
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