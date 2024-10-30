var compliance;

function Cookie_Compliance(options) {
    this.options = options;
};

Cookie_Compliance.prototype.enabled = function () {
    var cookieEnabled = (navigator.cookieEnabled) ? true : false;
    if (typeof navigator.cookieEnabled == 'undefined' && !cookieEnabled) {
        document.cookie = 'testcookie';
        cookieEnabled = (document.cookie.indexOf('testcookie') != -1) ? true : false;
    }
    return cookieEnabled;
};

Cookie_Compliance.prototype.isAccepted = function () {
    var cookie = this.getCookie(this.options.cookie);
    return cookie == '1';
};

function getInternetExplorerVersion() {
    var rv = -1; // Return value assumes failure.
    if (navigator.appName == 'Microsoft Internet Explorer') {
        var ua = navigator.userAgent;
        var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
        if (re.exec(ua) != null) rv = parseFloat(RegExp.$1);
    }
    return rv;
}

Cookie_Compliance.prototype.check = function () {
    var cookie = this.getCookie(this.options.cookie);
    return (cookie == '0') ? true : ((cookie != null && cookie != '') ? this.setCookie(this.options.cookie, '1', 365) : false);
};

Cookie_Compliance.prototype.getParameterByName = function (name) {
    name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
    var regexS = "[\\?&]" + name + "=([^&#]*)";
    var regex = new RegExp(regexS);
    var results = regex.exec(window.location.search);
    if (results == null) return "";
    else return decodeURIComponent(results[1].replace(/\+/g, " "));
};

function addParameter(url, param, value) {
    // Using a positive lookahead (?=\=) to find the
    // given parameter, preceded by a ? or &, and followed
    // by a = with a value after than (using a non-greedy selector)
    // and then followed by a & or the end of the string
    

    // Check if the parameter exists
   
}

Cookie_Compliance.prototype.setSessionID = function (zsid) {
	param = 'zsid';
	
	if (!(zsid =='')) {
	jQuery('a').each(function()
	{
		var url = this.href;
		var newurl = this.href;
		if (!this.href.match(/^mailto:/) && (this.hostname.replace(/^www\./i, '') == document.location.hostname.replace(/^www\./i, ''))) {
			var val = new RegExp('(\\?|\\&)' + param + '=.*?(?=(&|$))'),
        			qstring = /\?.+$/;
			if (val.test(url))
    			{
				newurl = url.replace(val, '$1' + param + '=' + zsid);
    			}
    			else if (qstring.test(url))
    			{
				newurl = url + '&' + param + '=' + zsid;
    			}
    			else
    			{
				newurl = url + '?' + param + '=' + zsid;
    			}
			jQuery(this).attr('href', newurl);
		}
	});
	}
};

Cookie_Compliance.prototype.notify = function () {
    var message = '<div id="cookie-compliance">' +
        '   <div id="cookie-compliance-overlay">' +
        '       <div>' +
        '           ' + this.options.notify + '' +
        '           <p id="cookie-compliance-buttons">' +
        '               <a href="" id="cookie-compliance-submit" onClick="JavaScript:compliance.setCookie(\'' + this.options.cookie + '\', \'1\', 365); compliance.log(\'accept\');">' + this.options.buttons.submit + '</a>' +
        '               <a href="" id="cookie-compliance-cancel" onClick="JavaScript:compliance.setCookie(\'' + this.options.cookie + '\', \'0\', 365); compliance.log( \'deny\' );">' + this.options.buttons.cancel + '</a>' +
        '           </p>' +
        '       </div>' +
        '   </div>' +
        '</div>';
    jQuery('body').prepend(message);
};

Cookie_Compliance.prototype.notifybottom = function () {
    var message = '<div id="cookie-compliance-bottom">' +
        '   <div id="cookie-compliance-bottom-overlay">' +
        '       <div>' +
        '           ' + this.options.bottom.message + '' +
        '               <a href="#" id="cookie-compliance-submit" onClick="JavaScript:compliance.setCookie(\'' + this.options.cookie + '\', \'1\', 365); compliance.log(\'accept\');">' + this.options.buttons.submit + '</a>' +
        '       </div>' +
        '   </div>' +
        '</div>';
    jQuery('body').append(message);
    if (getInternetExplorerVersion() == -1) jQuery('body').append('<div style="height:45px;width:100%;"></div>');
    var divwidth = ((this.options.bottom.textwidth * 1) + (150 * 1));
    jQuery('div#cookie-compliance-bottom-overlay div').width(divwidth);
    jQuery('div#cookie-compliance-bottom-overlay p').width(this.options.bottom.textwidth);
};

Cookie_Compliance.prototype.notifybottompopup = function () {
    var message = '<div id="cookie-compliance-bottom">' +
        '   <div id="cookie-compliance-bottom-overlay">' +
        '       <div>' +
        '           ' + this.options.bottom.message + '' +
        '               <a href="#" id="cookie-compliance-submit" onClick="JavaScript:compliance.setCookie(\'' + this.options.cookie + '\', \'1\', 365); compliance.log(\'accept\');">' + this.options.buttons.submit + '</a>' +
        '               <a href="#" id="cookie-compliance-cancel" onClick="JavaScript:compliance.setCookie(\'' + this.options.cookie + '\', \'0\', 365); compliance.log( \'deny\' );">' + this.options.buttons.cancel + '</a>' +
        '       </div>' +
        '   </div>' +
        '</div>';
    jQuery('body').append(message);
    if (getInternetExplorerVersion() == -1) jQuery('body').append('<div style="height:45px;width:100%;"></div>');
    var divwidth = ((this.options.bottom.textwidth * 1) + (150 * 1));
    jQuery('div#cookie-compliance-bottom-overlay div').width(divwidth);
    jQuery('div#cookie-compliance-bottom-overlay p').width(this.options.bottom.textwidth);
};

Cookie_Compliance.prototype.getCookie = function (name) {
    var i, x, y, ARRcookies = document.cookie.split(";");
    for (i = 0; i < ARRcookies.length; i++) {
        x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
        y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
        x = x.replace(/^\s+|\s+$/g, '');
        if (x == name) {
            return unescape(y);
        }
    }
};

Cookie_Compliance.prototype.setCookie = function (name, value, expiry) {
    if (value == '0' && compliance.options.ccsettings != null && compliance.options.ccsettings.nocookie != null && compliance.options.ccsettings.nocookie == 'on') {
        jQuery('#cookie-compliance').hide();
        jQuery('#cookie-compliance-bottom').hide();
    } else {
        var date = new Date();
        date.setDate(date.getDate() + expiry);
        document.cookie = name + '=' + value + ';' + 'expires=' + date.toUTCString() + ';path=/';
        jQuery('#cookie-compliance').hide();
        jQuery('#cookie-compliance-bottom').hide();
    }
    return true;
};

Cookie_Compliance.prototype.gapush = function (gavals) {
    if (compliance.enabled() && compliance.check() && compliance.isAccepted()) {
        _gaq.push(gavals);
    } else {
        if (gavals[0] == '_trackEvent') {
            var utmeval = '';
            for (var i = 0; i < gavals.length; i++) {
                if (i > 1) utmeval = utmeval + '*';
                if (i > 0) utmeval = utmeval + gavals[i];
            }
            jQuery.post(
            compliance.options.ajaxurl, {
                action: 'cookie_compliance_analytics',
                path: window.location.pathname,
                title: document.title,
                nonce: compliance.options.ga_nonce,
                ga_referrer: document.referrer,
                utmt: 'event',
                utmtn: '5',
                utme: utmeval,
                __utma: compliance.getParameterByName('__utma'),
                __utmb: compliance.getParameterByName('__utmb'),
                __utmc: compliance.getParameterByName('__utmc'),
                __utmz: compliance.getParameterByName('__utmz')
            });
        }
    }


    var cookieEnabled = (navigator.cookieEnabled) ? true : false;
    if (typeof navigator.cookieEnabled == 'undefined' && !cookieEnabled) {
        document.cookie = 'testcookie';
        cookieEnabled = (document.cookie.indexOf('testcookie') != -1) ? true : false;
    }
    return cookieEnabled;
};

Cookie_Compliance.prototype.log = function (answer) {
    jQuery.post(this.options.ajaxurl, {
        action: 'cookie_compliance_log',
        answer: answer,
        nonce: this.options.nonce
    },

    function (response) {
        compliance = new Cookie_Compliance(cookie_compliance_options);
        if (compliance.enabled() && compliance.check()) {
            if (!(compliance.isAccepted()) && compliance.options.ccsettings != null && compliance.options.ccsettings.bottomdenied != null && compliance.options.ccsettings.bottomdenied == 'on') {
                compliance.notifybottom();
            }
            cookie_compliance_head();
            cookie_compliance_footer();
        } else {
            if (compliance.options.ccsettings != null && compliance.options.ccsettings.deny != null && compliance.options.ccsettings.deny == 'on') {
                if (compliance.options.ccsettings.nocookie == null) compliance.setCookie(compliance.options.cookie, '0', 365);
                cookie_compliance_head();
                cookie_compliance_footer();
                if (compliance.options.ccsettings.bottomdenied != null && compliance.options.ccsettings.bottomdenied == 'on') compliance.notifybottom();
            } else {
                if (compliance.options.ccsettings != null && compliance.options.ccsettings.bottompopup != null && compliance.options.ccsettings.bottompopup == 'on') compliance.notifybottompopup();
                else compliance.notify();
            }
        }
    });
};

(function ($) {
    $(document).ready(function () {
        compliance = new Cookie_Compliance(cookie_compliance_options);
        if (compliance.enabled() && compliance.check()) {
            if (!(compliance.isAccepted()) && compliance.options.ccsettings != null && compliance.options.ccsettings.bottomdenied != null && compliance.options.ccsettings.bottomdenied == 'on') {
                compliance.notifybottom();
            }
            cookie_compliance_head();
            cookie_compliance_footer();
        } else {
            if (compliance.options.ccsettings != null && compliance.options.ccsettings.deny != null && compliance.options.ccsettings.deny == 'on') {
                if (compliance.options.ccsettings.nocookie == null) compliance.setCookie(compliance.options.cookie, '0', 365);
                cookie_compliance_head();
                cookie_compliance_footer();
                if (compliance.options.ccsettings.bottomdenied != null && compliance.options.ccsettings.bottomdenied == 'on') compliance.notifybottom();
            } else {
                if (compliance.options.ccsettings != null && compliance.options.ccsettings.bottompopup != null && compliance.options.ccsettings.bottompopup == 'on') compliance.notifybottompopup();
                else compliance.notify();
            }
        }
    })
})(jQuery);