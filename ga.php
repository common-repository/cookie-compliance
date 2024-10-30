<?php
define("VERSION", "5.3.8");
define("SESSION_NAME", "cookie_compliance_gasession");

$current_session = '';
if (isset($_POST['zsid']) && !($_POST['zsid'] == '')) $current_session = $_POST['zsid'];

global $current_session_details;
$current_session_details = array();

$session_key_params = array();
$session_key_params['HTTP_USER_AGENT'] 		= $_SERVER['HTTP_USER_AGENT'];
$session_key_params['HTTP_ACCEPT'] 		= $_SERVER['HTTP_ACCEPT'];
$session_key_params['HTTP_ACCEPT_LANGUAGE'] 	= $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$session_key_params['HTTP_ACCEPT_ENCODING'] 	= $_SERVER['HTTP_ACCEPT_ENCODING'];
$session_key_params['SERVER_NAME'] 		= $_SERVER['SERVER_NAME'];
$session_key_params['REMOTE_ADDR'] 		= $_SERVER['REMOTE_ADDR'];
$session_key = sha1(serialize($session_key_params));

$session_key_params['visitor_key'] 		= $session_key;
$new_session_params = serialize($session_key_params);

$useripparts = explode('.',$_SERVER['REMOTE_ADDR']);
$visitorid = $useripparts[2] . $useripparts[1] . sha1($session_key . time());
$visitorid = substr($visitorid,0,15);

$current_session_details['visitor_key']		= $session_key;
$current_session_details['visitor_sid']		= $visitorid;
$current_session_details['visitor_id']		= 0;

global $wpdb;
if (!($current_session == '')) {
	$session_matches = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "cookie_compliance_visitor WHERE UNIX_TIMESTAMP(`time`) > '" . (time() - 900) . "' AND `ip_address` = '" . $current_session . "'" );
	if (count($session_matches) == 0) {
		$wpdb->insert($wpdb->prefix . 'cookie_compliance_visitor', array(
            		'ip_address' => $visitorid,
			'visitor' => $new_session_params
        	));
		$current_session = $visitorid;
		$session_matches = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "cookie_compliance_visitor WHERE UNIX_TIMESTAMP(`time`) > '" . (time() - 900) . "' AND `ip_address` = '" . $current_session . "'" );
	}
} else {
	$wpdb->insert($wpdb->prefix . 'cookie_compliance_visitor', array(
            	'ip_address' => $visitorid,
		'visitor' => $new_session_params
        ));
	$current_session = $visitorid;
	$session_matches = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "cookie_compliance_visitor WHERE UNIX_TIMESTAMP(`time`) > '" . (time() - 900) . "' AND `ip_address` = '" . $visitorid . "' LIMIT 0,1" );
}

foreach ($session_matches as $session_match) {
	$visitor_details = unserialize($session_match->visitor);
	if ($session_key_params['visitor_key'] == $visitor_details['visitor_key']) {
		$current_session_details['visitor_sid']		= $session_match->ip_address;
		$current_session_details['visitor_id']		= $session_match->id;
		$current_session_details['visitor_info']		= $visitor_details;
	}
}

if ($current_session_details['visitor_id'] == 0) {
	$wpdb->insert($wpdb->prefix . 'cookie_compliance_visitor', array(
            	'ip_address' => $visitorid,
		'visitor' => $new_session_params
        ));
	$current_session = $visitorid;
	$session_matches = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "cookie_compliance_visitor WHERE UNIX_TIMESTAMP(`time`) > '" . (time() - 900) . "' AND `ip_address` = '" . $visitorid . "' LIMIT 0,1" );

	foreach ($session_matches as $session_match) {
		$visitor_details = unserialize($session_match->visitor);
		if ($session_key_params['visitor_key'] == $visitor_details['visitor_key']) {
			$current_session_details['visitor_sid']		= $session_match->ip_address;
			$current_session_details['visitor_id']		= $session_match->id;
			$current_session_details['visitor_info']		= $visitor_details;
		}
	}
}

foreach ($current_session_details['visitor_info'] as $key => $value) {
	$current_session_details[$key] = $value;
}



function storeSessionInfo() {
	global $wpdb, $current_session_details;
	$wpdb->query("UPDATE " . $wpdb->prefix . "cookie_compliance_visitor SET `time`=NOW(), `visitor` = '" . serialize($current_session_details) . "' WHERE id = '" . $current_session_details['visitor_id'] . "'");
}


        




function getIP($remoteAddress)
{
    if (empty($remoteAddress)) {
        return "";
    }
    
    $regex = "/^([^.]+\.[^.]+\.[^.]+\.).*/";
    if (preg_match($regex, $remoteAddress, $matches)) {
        return $matches[1] . "0";
    } else {
        return "";
    }
}

function getVisitorId($guid, $account, $userAgent, $gasession)
{
    if (!empty($gasession)) {
        return $gasession;
    }
    $message = "";
    if (!empty($guid)) {
        $message = $guid . $account;
    } else {
        $message = $userAgent . uniqid(getRandomNumber(), true);
    }
    $md5String = md5($message);
    //return "0x" . substr($md5String, 0, 16);
    return getRandomNumber();
}

function getRandomNumber()
{
    return rand(0, 0x7fffffff);
}

function sendRequestToGoogleAnalytics($utmUrl,$referer = '')
{
	$curpath = 'http://';
	if ($_SERVER["HTTPS"] == 'on') $curpath = 'https://';
	$curpath .= $_SERVER["HTTP_HOST"] . $referer;

    $options = array(
        "http" => array(
            	"method" => "GET",
            	"user_agent" => $_SERVER["HTTP_USER_AGENT"],
            	"header" => (	"Accept:"	. "image/png,image/*;q=0.8,*/*;q=0.5\r\n"
				."Accept-Language:"		. $_SERVER["HTTP_ACCEPT_LANGUAGE"] . "\r\n"
				."Host:"		. "www.google-analytics.com\r\n"
				."Referer:"		. $curpath . "\r\n"
				."Accept-Encoding:"	. $_SERVER["HTTP_ACCEPT_ENCODING"] . "\r\n"
				."X-Forwarded-For:"	. $_SERVER["REMOTE_ADDR"] . "\r\n"
				."User-Agent:"	. $_SERVER["HTTP_USER_AGENT"] )
        )
    );
    $data    = @file_get_contents($utmUrl, false, stream_context_create($options));
	//print_r($utmUrl);
	//print_r($http_response_header);
}

function trackPageView($account, $server)
{
    global $current_session_details;

    $timeStamp  = time();
    $domainName = $_SERVER["SERVER_NAME"];
    if (empty($domainName)) {
        $domainName = $server;
    }
    
    $documentReferer = $_POST["ga_referrer"];
    if (empty($documentReferer) && $documentReferer !== "0") {
        $documentReferer = "-";
    } else {
        $documentReferer = urldecode($documentReferer);
    }
    
    
    $documentPath = $_POST["path"];
    if (empty($documentPath)) {
        $documentPath = "";
    } else {
        $documentPath = urldecode($documentPath);
    }
    
    $userAgent = $_SERVER["HTTP_USER_AGENT"];
    if (empty($userAgent)) {
        $userAgent = "";
    }
    
    if (isset($current_session_details[SESSION_NAME]))
        $gasession = unserialize($current_session_details[SESSION_NAME]);
    else {
        $gasession          = array();
        $gasession['id']    = '';
        $gasession['start'] = time();
        $gasession['utms']  = 0;
    }
    
    $guidHeader = $_SERVER["HTTP_X_DCMGUID"];
    if (empty($guidHeader)) {
        $guidHeader = $_SERVER["HTTP_X_UP_SUBNO"];
    }
    if (empty($guidHeader)) {
        $guidHeader = $_SERVER["HTTP_X_JPHONE_UID"];
    }
    if (empty($guidHeader)) {
        $guidHeader = $_SERVER["HTTP_X_EM_UID"];
    }

	if (substr($account,0,2) == 'UA') {
		$account = 'MO' . substr($account,2,strlen($account)-2);
	}

    $visitorId = getVisitorId($guidHeader, $account, $userAgent, $gasession['id']);
    
    $gasession['id'] = $visitorId;
    $gasession['utms']++;
    $gasession['last']      = time();
    $current_session_details[SESSION_NAME] = serialize($gasession);
    
    $utmGifLocation = "http://www.google-analytics.com/__utm.gif";
    
    $utmUrl = $utmGifLocation . "?" . "utmwv=" . VERSION . "&utmje=" . '1' . "&utmcs=" . 'UTF-8' . "&utmsr=" . '1600x900' . "&utmvp=" . '1600x900' . "&utmsc=" . '24-bit' . "&utmn=" . getRandomNumber() . "&utmhn=" . urlencode($domainName) . "&utmr=" . urlencode($documentReferer) . "&utmp=" . urlencode($documentPath) . "&utmdt=" . urlencode($_POST["title"]) . "&utmac=" . $account . "&utmcc=" . utmcc($documentReferer, $domainName, $gasession) . "&utmvid=" . urlencode($visitorId) . "&utmip=" . getIP($_SERVER["REMOTE_ADDR"]);
    if (isset($_POST['utmt']) && isset($_POST['utmtn']) && isset($_POST['utme']) && $_POST['utmt'] == 'event') {
        $utmUrl .= "&utmt=event&utme=" . $_POST['utmtn'] . "(" . str_replace('+','%20',str_replace('%2A', '*', urlencode($_POST['utme']))) . ")";
    }
    
    storeSessionInfo();
    sendRequestToGoogleAnalytics($utmUrl,$documentPath);

    return $current_session_details['visitor_sid'];
}

function generateHash($string)
{
    $string = (string) $string;
    $hash   = 1;
    if ($string !== null && $string !== '') {
        $hash = 0;
        
        $length = strlen($string);
        for ($pos = $length - 1; $pos >= 0; $pos--) {
            $current   = ord($string[$pos]);
            $hash      = (($hash << 6) & 0xfffffff) + $current + ($current << 14);
            $leftMost7 = $hash & 0xfe00000;
            if ($leftMost7 != 0) {
                $hash ^= $leftMost7 >> 21;
            }
        }
    }
    return $hash;
}

function searchenginesarray()
{
    return explode(" ", "daum:q eniro:search_word naver:query pchome:q images.google:q google:q yahoo:p yahoo:q msn:q bing:q aol:query aol:q lycos:q lycos:query ask:q netscape:query cnn:query about:terms mamma:q voila:rdata virgilio:qs live:q baidu:wd alice:qs yandex:text najdi:q seznam:q rakuten:qt biglobe:q goo.ne:MT wp:szukaj onet:qt yam:k kvasir:q ozu:q terra:query rambler:query conduit:q babylon:q search-results:q avg:q comcast:q incredimail:q startsiden:q go.mail.ru:q search.centrum.cz:q");
}

function checkforSE($refdom)
{
    $SEresult           = array();
    $SEresult['engine'] = 'none';
    $SEquery            = array();
    $searchengines      = searchenginesarray();
    foreach ($searchengines as $searchengine) {
        $SEdet = explode(":", $searchengine);
        if (strpos($refdom, $SEdet[0] . ".") > 0) {
            array_push($SEquery, $SEdet[1]);
            $SEresult['engine'] = $SEdet[0];
        }
    }
    $SEresult['query'] = $SEquery;
    return $SEresult;
}

function utmcc($referrer, $domainName, $gasession)
{
    global $current_session_details;

    if (!isset($current_session_details['__utma'])) {
        $current_session_details['__utma'] = generateHash($domainName) . '.' . $gasession['id'] . '.' . $gasession['start'] . '.' . $gasession['last'] . '.' . time() . '.1';
    }
    
    if (!isset($current_session_details['__utmz'])) {
        $umtz = array();
        array_push($umtz, generateHash($domainName));
        array_push($umtz, time());
        array_push($umtz, '0');
        array_push($umtz, '0');
        
        if (substr($referrer, 0, 4) == 'http') {
            $urlarr = explode('/', $referrer);
            if (count($urlarr) > 1) {
                $refdom  = $urlarr[2];
                $refpath = '';
                if (count($urlarr) > 2) {
                    foreach ($urlarr as $num => $val) {
                        if ($num > 2)
                            $refpath .= '/' . $val;
                    }
                } else {
                    $refpath = '/';
                }
                if ($refdom != $_SERVER['SERVER_NAME']) {
                    $reftype = "referral";
                    
                    // Check if the referral was a search engine...
                    $SEcheck = checkforSE($refdom);
                    if (!($SEcheck['engine'] == 'none')) {
                        $reftype  = "organic";
                        $keywords = "";
                        
                        $SEreferrer = str_replace(chr(35), chr(63), $referrer);
                        $parsed     = parse_url($SEreferrer, PHP_URL_QUERY);
                        parse_str($parsed, $SEquery);
                        foreach ($SEcheck['query'] as $query) {
                            if (isset($SEquery[$query])) {
                                if (strlen($keywords) > 0)
                                    $keywords .= "+";
                                $keywords .= $SEquery[$query];
                            }
                        }
                        if (strlen($keywords) == 0)
                            $keywords = "(not%20provided)";
                        $utmcsr = "utmcsr=" . $SEcheck['engine'] . "|utmccn=(organic)|utmcmd=organic|utmctr=" . $keywords;
                        array_push($umtz, $utmcsr);
                    } else {
                        $utmcsr = "utmcsr=" . $refdom . "|utmccn=(referral)|utmcmd=referral|utmctr=" . $refpath;
                        array_push($umtz, $utmcsr);
                    }
                    
                } else {
                    $utmcsr = "utmcsr=(direct)|utmccn=(direct)|utmcmd=(none)";
                    array_push($umtz, $utmcsr);
                }
            } else {
                $utmcsr = "utmcsr=(direct)|utmccn=(direct)|utmcmd=(none)";
                array_push($umtz, $utmcsr);
            }
        } else {
            $utmcsr = "utmcsr=(direct)|utmccn=(direct)|utmcmd=(none)";
            array_push($umtz, $utmcsr);
        }
        if (isset($umtz)) {
            $utmzt = '';
            foreach ($umtz as $val) {
                if (strlen($utmzt) > 0)
                    $utmzt .= '.';
                $utmzt .= $val;
            }
            $current_session_details['__utmz'] = $utmzt;
        }
    }
    
    if (isset($_COOKIE["__utma"]))
        $current_session_details['__utma'] = $_COOKIE["__utma"];
    if (isset($_COOKIE["__utmb"]))
        $current_session_details['__utmb'] = $_COOKIE["__utmb"];
    if (isset($_COOKIE["__utmc"]))
        $current_session_details['__utmc'] = $_COOKIE["__utmc"];
    if (isset($_COOKIE["__utmz"]))
        $current_session_details['__utmz'] = $_COOKIE["__utmz"];
    
    if (isset($_POST["__utma"]) && !($_POST["__utma"] == ''))
        $current_session_details['__utma'] = $_POST["__utma"];
    if (isset($_POST["__utmb"]) && !($_POST["__utmb"] == ''))
        $current_session_details['__utmb'] = $_POST["__utmb"];
    if (isset($_POST["__utmc"]) && !($_POST["__utmc"] == ''))
        $current_session_details['__utmc'] = $_POST["__utmc"];
    if (isset($_POST["__utmz"]) && !($_POST["__utmz"] == ''))
        $current_session_details['__utmz'] = $_POST["__utmz"];
    
    $resp = '';
    
    if (isset($current_session_details['__utma'])) {
        $resp .= '__utma=' . $current_session_details['__utma'] . ';';
    }
    if (isset($current_session_details['__utmb'])) {
        if (strlen($resp) > 0)
            $resp .= '+';
        $resp .= '__utmb=' . $current_session_details['__utmb'] . ';';
    }
    if (isset($current_session_details['__utmc'])) {
        if (strlen($resp) > 0)
            $resp .= '+';
        $resp .= '__utmc=' . $current_session_details['__utmc'] . ';';
    }
    if (isset($current_session_details['__utma'])) {
        if (strlen($resp) > 0)
            $resp .= '+';
        $resp .= '__utmz=' . $current_session_details['__utmz'] . ';';
    }
    
    return urlencode($resp);
}


