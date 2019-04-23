jQuery(document).ready(
    function ($) {
    	
        $('#cdtawp_plugin_type').change(
        	
            function () {
	            $("#connection_msg").text('');
                if ("Child" == $('#cdtawp_plugin_type').val() ) {
                    $('#cdtawp_store_url, #cdtawp_referral_credit_last, #cdtawp_public_key, #cdtawp_token').closest('tr').fadeIn();
                    $(("h2:contains('Authenticate with AffiliateWP')")).fadeIn();
                    $(("h2:contains('Authenticate with AffiliateWP')")).next('p').fadeIn();
                    $("#check_store_connection").fadeIn();
                } else {
                    $('#cdtawp_store_url, #cdtawp_referral_credit_last, #cdtawp_public_key, #cdtawp_token').closest('tr').fadeOut();
	                $(("h2:contains('Authenticate with AffiliateWP')")).fadeOut();
	                $(("h2:contains('Authenticate with AffiliateWP')")).next('p').fadeOut();
	                $("#check_store_connection").fadeOut();
                }
            }
        );
        
        if ("Parent" == $('#cdtawp_plugin_type').val() ) {
            $('#cdtawp_store_url, #cdtawp_referral_credit_last, #cdtawp_public_key, #cdtawp_token').closest('tr').hide();
            $(("h2:contains('Authenticate with AffiliateWP')")).hide();
            $(("h2:contains('Authenticate with AffiliateWP')")).next('p').hide();
        } else {
	
	        $(".submit").before('<input type="button" name="check_store_connection" id="check_store_connection" class="button button-secondary button-small cdtawp-updating-message" value="Authenticate with AffiliateWP"><span style="margin-left: 5px" id="connection_msg"></span>');
        }
	    
	    $("#check_store_connection").click(function () {
		    $("#connection_msg").text('Authenticating...').css('color','green');
		    $.ajax({
			    url: cdtawp_vars.ajaxurl,
			    type: 'POST',
			    data : {
				    action : 'cdtawp_check_connection',
				    store_url: $("#cdtawp_store_url").val(),
				    public_key: $("#cdtawp_public_key").val(),
		            token: $("#cdtawp_token").val(),
				    plugin_type: $("#cdtawp_plugin_type").val()
			    },
			    success: function(data) {
			    	if( data.success ) {
					    $("#connection_msg").text('Authentication Successful!').css('color','green').css('color', 'green');
				    } else {
					    $("#connection_msg").text('Authentication failed! Please verify settings.').css('color','green').css('color', 'red');
				    }
			    }
		    });
	    });
     
    }
);