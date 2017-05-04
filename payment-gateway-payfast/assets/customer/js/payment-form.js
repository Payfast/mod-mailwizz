/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @subpackage Payment Gateway Payfast
 * @author PayFast <support@payfast.co.za>
 * @link http://www.payfast.co.za/
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 * @since 1.0
 */
jQuery(document).ready(function($){

    var ajaxData = {};
    if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
        var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
        var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
        ajaxData[csrfTokenName] = csrfTokenValue;
    }

    $('#payfast-hidden-form').on('submit', function(){
        var $this = $(this);
        if ($this.data('submit')) {
            return true;
        }
        if ($this.data('ajaxRunning')) {
            return false;
        }
        $this.data('ajaxRunning', true);
        $.post($this.data('order'), $this.serialize(), function(json){
            $this.data('ajaxRunning', false);
            if (json.status == 'error') {
                notify.remove().addError(json.message).show();
            } else {
                $this.data('submit', true).submit();
            }
        }, 'json');
        return false;
    });
});