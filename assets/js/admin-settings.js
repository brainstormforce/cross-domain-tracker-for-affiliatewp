jQuery(document).ready(
    function ($) {
        
        $('#cdtawp_plugin_type').change(
            function () {
                if ("Child" == $('#cdtawp_plugin_type').val() ) {
                    $('#cdtawp_store_url, #cdtawp_referral_credit_last, #cdtawp_public_key, #cdtawp_token').closest('tr').fadeIn();
                    $(("h2:contains('Store Connection Settings')")).fadeIn();
                } else {
                    $('#cdtawp_store_url, #cdtawp_referral_credit_last, #cdtawp_public_key, #cdtawp_token').closest('tr').fadeOut();
                    $(("h2:contains('Store Connection Settings')")).fadeOut();
                }
            }
        );
        
        if ("Parent" == $('#cdtawp_plugin_type').val() ) {
            $('#cdtawp_store_url, #cdtawp_referral_credit_last, #cdtawp_public_key, #cdtawp_token').closest('tr').hide();
            $(("h2:contains('Store Connection Settings')")).hide();
        }
    }
);