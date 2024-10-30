<?php
if (ini_set('session.use_cookies', '0') === false) {
	//could not disable session cookie
} else ini_set('session.use_cookies', '0');

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

require dirname(__FILE__) . '/../../../wp-config.php';

$cookie_compliance = Cookie_Compliance::initialize();

switch ($_POST['action']) {
    
    case 'cookie_compliance_log':
        check_ajax_referer('cookie-compliance-nonce', 'nonce');
        
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'cookie_compliance_log', array(
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'answer' => $_POST['answer']
        ));
        
        die();
        break;
    case 'cookie_compliance_analytics':
        if ($cookie_compliance->config->getGAType == 'multi_sub' || $cookie_compliance->config->getGAType == 'multi_tld') {
            $server = $_SERVER['SERVER_NAME'];
        } else {
            $server = $cookie_compliance->config->getGADomain();
        }
        require dirname(__FILE__) . '/ga.php';
        echo trackPageView($cookie_compliance->config->getGAUACode(), $server);
        break;
}