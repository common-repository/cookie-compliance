<?php

require dirname(__FILE__) . '/cookie-compliance-config.php';
require dirname(__FILE__) . '/cookie-compliance-language.php';

class Cookie_Compliance
{
    const VERSION = '1.0';
    const TABLE_NAME = 'cookie_compliance_log';
    const OPTION_PREFIX = 'cookie_compliance_';
    private $options = array();
    private static $language = 'en';
    private static $languages = array('en' => 'English');
    private static $languagechoice = false;
    
    public static function initialize()
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        
        // ######################################################
        // ### Check for translation plugin Q-Translate
        // ######################################################
        if (function_exists('qtrans_getLanguage') && function_exists('qtrans_getSortedLanguages') && function_exists('qtrans_getLanguageName')) {
            self::$language = qtrans_getLanguage();
            foreach (qtrans_getSortedLanguages() as $language) {
                self::$languages[$language] = qtrans_getLanguageName($language);
            }
            self::$languagechoice = true;
        }
        
        // ######################################################
        // ### Check for translation plugin Polylang
        // ######################################################
        if (is_plugin_active('polylang/polylang.php') && function_exists('pll_current_language') && function_exists('pll_the_languages')) {
            if (!is_admin()) {
                self::$language = pll_current_language('slug');
            } else {
                global $polylang;
                if (!(!$polylang->get_languages_list())) {
                    $pll_options    = get_option('polylang');
                    self::$language = $pll_options['default_language'];
                    if (isset($_GET['lang']) && isset($_GET['page']) && $_GET['page'] == 'cookie-compliance-settings') {
                        self::$language = $_GET['lang'];
                    }
                    foreach ($polylang->get_languages_list() as $language) {
                        $language                           = get_object_vars($language);
                        self::$languages[$language['slug']] = $language['name'];
                    }
                    self::$languagechoice = true;
                }
            }
        }
        
        // ######################################################
        // ### Check for translation plugin WPML
        // ######################################################
        if (defined('ICL_SITEPRESS_VERSION')) {
            global $sitepress;
            self::$language = $sitepress->get_current_language();
            if (self::$language == 'all')
                self::$language = $sitepress->get_default_language();
            foreach ($sitepress->get_active_languages() as $langkey => $langval) {
                self::$languages[$langkey] = $langval['native_name'];
            }
        }
        
        if (strlen(self::$language) == 0) {
            self::$language  = 'en';
            self::$languages = array(
                'en' => 'English'
            );
        }
        
        $cookie_compliance_config = get_option(Cookie_Compliance::OPTION_PREFIX . "options", new Cookie_Compliance_Config());
        
        $cookie_compliance_language = get_option(Cookie_Compliance::OPTION_PREFIX . "language_" . self::$language, new Cookie_Compliance_Language(self::$language));
        
        return new Cookie_Compliance($cookie_compliance_config, $cookie_compliance_language, $cookie_compliance_languages);
    }
    
    public static function activate()
    {
        global $wpdb;
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $sql = sprintf("CREATE TABLE %s (
         id int(11) NOT NULL AUTO_INCREMENT,
         time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
         ip_address VARCHAR(15) DEFAULT '' NOT NULL,
         user_agent TEXT NOT NULL,
         answer ENUM('accept', 'deny') DEFAULT 'deny' NOT NULL,
         PRIMARY KEY id (id));", $wpdb->prefix . Cookie_Compliance::TABLE_NAME);
        
        dbDelta($sql);
        
        $sql = sprintf("CREATE TABLE %s (
         id int(11) NOT NULL AUTO_INCREMENT,
         time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
         ip_address VARCHAR(15) DEFAULT '' NOT NULL,
         visitor TEXT NOT NULL,
         PRIMARY KEY id (id));", $wpdb->prefix . 'cookie_compliance_visitor');
        
        dbDelta($sql);
        
        add_option("cookie_compliance_db_version", Cookie_Compliance::VERSION);
    }
    
    public function Cookie_Compliance(Cookie_Compliance_Config $config, Cookie_Compliance_Language $language, $languages = array())
    {
        $this->config = $config;
        
        $this->languages = $this->languages + $languages;
        $this->language  = $language;
        
        $this->add_settings();
        $this->add_actions();
    }
    
    private function setLanguage($short, Cookie_Compliance_Language $language)
    {
        update_option(Cookie_Compliance::OPTION_PREFIX . "language_$short", $language);
        $this->language = $language;
    }
    
    private function setConfig(Cookie_Compliance_Config $config)
    {
        update_option(Cookie_Compliance::OPTION_PREFIX . "options", $config);
        $this->config = $config;
    }
    
    private function getConfig()
    {
        return $this->language->getConfig() + $this->config->getOptions();
    }
    
    private function add_settings()
    {
        add_action('admin_menu', array(
            &$this,
            'admin_menu'
        ));
    }
    
    public function admin_menu()
    {
        add_menu_page('Cookies', 'Cookies', 'manage_options', 'cookie-compliance', array(
            &$this,
            'menu_page'
        ), '', 81);
        add_submenu_page('cookie-compliance', 'Cookie Compliance', 'Cookie Compliance', 'manage_options', 'cookie-compliance', array(
            &$this,
            'menu_page'
        ));
        add_submenu_page('cookie-compliance', 'Pop-up Settings', 'Pop-up Settings', 'manage_options', 'cookie-compliance-settings', array(
            &$this,
            'cookie_compliance_settings'
        ));
        add_submenu_page('cookie-compliance', 'Google Analytics', 'Google Analytics', 'manage_options', 'zafrira_cookie_settings_ga', array(
            &$this,
            'zafrira_cookie_settings_ga'
        ));


    }
    
    public function menu_page()
    {
        if (0 === strcmp('POST', $_SERVER['REQUEST_METHOD'])) {
		$toPost = $this->config->getOptions();
		foreach ($_POST["cookie_compliance_options"] as $key => $value) {
			$toPost[$key] = $value;
		}
		$this->setConfig(new Cookie_Compliance_Config($toPost));
            if (function_exists('wp_cache_clean_cache')) {
                global $file_prefix;
                wp_cache_clean_cache($file_prefix, true);
            }
        }
?>
        <div class="wrap">
            <div style="padding-top:20px;">
                <h2>
                    <img width="46" height="32" style="vertical-align: middle; position: relative; top: -3px;" src="<?php
        echo plugins_url();
?>/cookie-compliance/cookie.jpg"> 
                    Cookie Compliance options - Script settings
                </h2>
            </div>

		This plugin is developed by <a href="https://zafrira.net" target="_blank">zafrira.net</a>, please visit the <a href="https://zafrira.net/en/tools/wordpress-plugins/cookie-compliance/" target="_blank">plugin page</a> to check for the latest details on this plugin or to contact us.<br />
		When you have suggestions, encounter problems or want a custom addition to this plugin, please visit our website for the contact details.
		<br /><br />
		We would like to ask you to leave us some feedback about this plugin. With the information we receive we can keep improving this plugin.<br />
		If you experience any technival issues or have suggestions, please leave a comment on the <a href="http://wordpress.org/support/plugin/cookie-compliance" target="_blank">support forum</a> on the wordpress.org website.<br />
		You can also rate the plugin and leave a <a href="http://wordpress.org/support/view/plugin-reviews/cookie-compliance" target="_blank">review on the wordpress plugin website</a>, feedback is really usefull to us!
		
            <form method="post" action="<?php
        echo admin_url();
?>admin.php?page=cookie-compliance">

		<h3>Display Options</h3>
		Below the display options of the plugin. Here you can choose to disable the big popup and to enable a constant bar on the bottom. You can also choose to display the bottom bar only when the cookies are disabled.
		<ul>
			<li><input type="checkbox" id="cookie_compliance_ccsettings_deny" name="cookie_compliance_options[ccsettings][deny]" <?php
        if ($this->config->getDefaultDeny()):
?>checked="checked"<?php
        endif;
?>> 
				Disable pop-up (makes the plugin to choose the deny option by default for all visitors)</li>
			<li><input type="checkbox" id="cookie_compliance_ccsettings_bottomdenied" name="cookie_compliance_options[ccsettings][bottomdenied]" <?php
        if ($this->config->getDefaultBottomdenied()):
?>checked="checked"<?php
        endif;
?>> 
				Display bar at the bottom when cookie usage is denied.</li>
			<li><input type="checkbox" id="cookie_compliance_ccsettings_bottompopup" name="cookie_compliance_options[ccsettings][bottompopup]" <?php
        if ($this->config->getDefaultBottompopup()):
?>checked="checked"<?php
        endif;
?>> 
				Display bar at the bottom in stead of the big popup.</li>
			<li><input type="checkbox" id="cookie_compliance_ccsettings_nocookie" name="cookie_compliance_options[ccsettings][nocookie]" <?php
        if ($this->config->getDefaultNocookie()):
?>checked="checked"<?php
        endif;
?>> 
				Do not store a cookie when the user chooses deny</li>
		</ul>
                
                <h3 style="margin-top:25px;">Cookie enabled</h3>
                Below fill in the javascript code to execute when your visitor allowed you to store cookies. You can add code to the header and footer.
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row">Header</th>
                            <td>
                                <textarea id="cookie_compliance_enabled_head"   rows="5" name="cookie_compliance_options[enabled][head]"
                                    ><?php
        echo $this->config->getEnabledHead();
?></textarea>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Footer</th>
                            <td>
                                <textarea id="cookie_compliance_enabled_footer" rows="5" name="cookie_compliance_options[enabled][footer]"
                                    ><?php
        echo $this->config->getEnabledFooter();
?></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>Cookie disabled</h3>
                Below fill in the javascript code to execute when your visitor denied you to store cookies, or when cookie storing is not available. You can add code to the header and footer.
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row">Header</th>
                            <td>
                                <textarea id="cookie_compliance_disabled_head"   rows="5" name="cookie_compliance_options[disabled][head]"
                                    ><?php
        echo $this->config->getDisabledHead();
?></textarea>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Footer</th>
                            <td>
                                <textarea id="cookie_compliance_disabled_footer" rows="5" name="cookie_compliance_options[disabled][footer]"
                                    ><?php
        echo $this->config->getDisabledFooter();
?></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>   
                
                <p class="submit">
                    <input type="submit" class="button-primary" value="Save Changes">
                </p>
            </form>
        </div>
        <style type="text/css">
            .form-table textarea,
            .form-table input,
            .form-table select {
                width: 500px;
            }
        </style>
        
        <?php
        $this->footer();
?>
        <?php
    }

    public function zafrira_cookie_settings_ga()
    {
        if (0 === strcmp('POST', $_SERVER['REQUEST_METHOD'])) {
		$toPost = $this->config->getOptions();
		foreach ($_POST["cookie_compliance_options"] as $key => $value) {
			$toPost[$key] = $value;
		}
		$this->setConfig(new Cookie_Compliance_Config($toPost));
		//$this->setConfig(new Cookie_Compliance_Config($_POST["cookie_compliance_options"]));

		if (function_exists('wp_cache_clean_cache')) {
			global $file_prefix;
			wp_cache_clean_cache($file_prefix, true);
		}
        }
?>     
        <div class="wrap">
            <div style="padding-top:20px;">
                <h2>
                    <img width="46" height="32" style="vertical-align: middle; position: relative; top: -3px;" src="<?php
        echo plugins_url();
?>/cookie-compliance/cookie.jpg"> 
                    Cookie Compliance options - Google Analytics settings
                </h2>
            </div>

		This plugin is developed by <a href="https://zafrira.net" target="_blank">zafrira.net</a>, please visit the <a href="https://zafrira.net/en/tools/wordpress-plugins/cookie-compliance/" target="_blank">plugin page</a> to check for the latest details on this plugin or to contact us.<br />
		When you have suggestions, encounter problems or want a custom addition to this plugin, please visit our website for the contact details.

		<br /><br />
		We would like to ask you to leave us some feedback about this plugin. With the information we receive we can keep improving this plugin.<br />
		If you experience any technival issues or have suggestions, please leave a comment on the <a href="http://wordpress.org/support/plugin/cookie-compliance" target="_blank">support forum</a> on the wordpress.org website.<br />
		You can also rate the plugin and leave a <a href="http://wordpress.org/support/view/plugin-reviews/cookie-compliance" target="_blank">review on the wordpress plugin website</a>, feedback is really usefull to us!
		

            <form method="post" action="<?php
        echo admin_url();
?>admin.php?page=zafrira_cookie_settings_ga">    

		<?php
        if (function_exists('fsockopen')) {
?>

                <h3><input type="checkbox" id="cookie_compliance_ga_enabled" name="cookie_compliance_options[ga][enabled]" 
                    <?php
            if ($this->config->getGAEnabled()):
?>checked="checked"<?php
            endif;
?>> Google Analytics</h3>
		  Below fill in the settings when you would like to use Google Analytics. The plugin will make sure no tracking cookies are used when your visitor chooses to deny cookies or when cookies are not available.
                <table id="cookie_compliance_ga" class="form-table" <?php
            if (!$this->config->getGAEnabled()):
?>style="display: none;"<?php
            endif;
?>>
                    <tbody>
                        <tr valign="top">
                            <th scope="row">UA Code</th>
                            <td>
                                <input type="text" id="cookie_compliance_ga_uacode" rows="5" name="cookie_compliance_options[ga][uacode]" 
                                    value="<?php
            echo $this->config->getGAUACode();
?>">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Type</th>
                            <td>
                                <select id="cookie_compliance_ga_type"  name="cookie_compliance_options[ga][type]">
                                    <?php
            foreach (array(
                'single' => 'Single',
                'multi_sub' => 'Domain with multiple subdomains',
                'multi_tld' => 'Domain with multiple TLD\'s'
            ) as $key => $desc):
?>
                                        <option value="<?php
                echo $key;
?>" <?php
                if ($this->config->getGAType() == $key):
?>selected="selected"<?php
                endif;
?>><?php
                echo $desc;
?></option>
                                    <?php
            endforeach;
?>
                                </select>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Domain</th>
                            <td>
                                <input type="text" id="cookie_compliance_ga_domain" rows="5" name="cookie_compliance_options[ga][domain]" 
                                    value="<?php
            echo $this->config->getGADomain();
?>">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Position *</th>
                            <td>
                                <select id="cookie_compliance_ga_position"  name="cookie_compliance_options[ga][position]">
                                    <?php
            foreach (array(
                'footer' => 'Footer',
                'header' => 'Header'
            ) as $key => $desc):
?>
                                        <option value="<?php
                echo $key;
?>" <?php
                if ($this->config->getGAPosition() == $key):
?>selected="selected"<?php
                endif;
?>><?php
                echo $desc;
?></option>
                                    <?php
            endforeach;
?>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>   
		<p>
		* By default the Cookie Compliance plugin will execute the Google Analytics code from the footer. If you wish to change this to the header, please change the position value.
		</p>
		<?php
        } else {
?>
		<h3>Google Analytics</h3>
		The Google Analytics function can only be enabled when the php function <i>fsockopen</i> is enabled and available.<br />
		If you would like to use this option, please request your hosting provider to enable the php function <i>fsockopen</i>.
          	<?php
        }
?>
                
                <p class="submit">
                    <input type="submit" class="button-primary" value="Save Changes">
                </p>

            </form>
        </div>
        <style type="text/css">
            .form-table textarea,
            .form-table input,
            .form-table select {
                width: 500px;
            }
        </style>
        <script type="text/javascript">
            jQuery('#cookie_compliance_ga_enabled').change(function() {
                jQuery('#cookie_compliance_ga').css('display', jQuery(this).is(':checked') ? 'block' : 'none').css('width', '75%');
            })
        </script>
        
        <?php
        $this->footer();
?>
        <?php
    }

    
    public function cookie_compliance_settings()
    {
        if (function_exists('qtrans_getLanguage')) {
            $activeLanguageShort = qtrans_getLanguage();
            $activeLanguageName  = self::$languages[$activeLanguageShort];
        } else {
            $language = array_slice(self::$languages, 0, 1, true);
            $language = array_keys($language);
            
            $activeLanguageShort = $this->language->getLanguageShortCode();
            $activeLanguageName  = self::$languages[$activeLanguageShort];
        }
        
        if (self::$languagechoice && isset($_GET['lang'])) {
            $activeLanguageShort = $_GET['lang'];
            $activeLanguageName  = self::$languages[$activeLanguageShort];
            self::$language      = $activeLanguageShort;
        }
        
        if (0 === strcmp('POST', $_SERVER['REQUEST_METHOD'])) {
            $_POST['cookie_compliance_language']['notify'] = stripslashes($_POST['cookie_compliance_language']['notify']);
            
            $_POST['cookie_compliance_language']['bottom']['message'] = stripslashes($_POST['cookie_compliance_language']['bottom']['message']);
            
            $_POST['cookie_compliance_language']['buttons']['submit'] = stripslashes($_POST['cookie_compliance_language']['buttons']['submit']);
            
            $_POST['cookie_compliance_language']['buttons']['cancel'] = stripslashes($_POST['cookie_compliance_language']['buttons']['cancel']);
            
            $language = new Cookie_Compliance_Language($activeLanguageShort, $_POST["cookie_compliance_language"]);
            
            $this->setLanguage($activeLanguageShort, $language);
            
            if (function_exists('wp_cache_clean_cache')) {
                global $file_prefix;
                wp_cache_clean_cache($file_prefix, true);
            }
        }
        
?>
        <div class="wrap" style="width: 75%;">
            <div style="padding-top:20px; height: 47px;">
                <h2 style="float: left;">
                    <img width="46" height="32" style="vertical-align: middle; position: relative; top: -3px;" src="<?php
        echo plugins_url();
?>/cookie-compliance/cookie.jpg"> 
                    Cookie Compliance options - Pop-up settings
                </h2>
                <?php
        if (self::$languagechoice && count(self::$languages) > 0):
?>
                    <select name="cookie_compliance_language" id="cookie_compliance_language" style="float: right; margin-top: 10px;">
                        <?php
            foreach (self::$languages as $languageShort => $languageName):
?>
                            <option value="<?php
                echo $languageShort;
?>" <?php
                if ($languageShort == $activeLanguageShort):
?>selected="selected"<?php
                endif;
?>>
                                <?php
                echo $languageName;
?>
                            </option>
                        <?php
            endforeach;
?>
                    </select>
                <?php
        endif;
?>
            </div>

		This plugin is developed by <a href="https://zafrira.net" target="_blank">zafrira.net</a>, please visit the <a href="https://zafrira.net/en/tools/wordpress-plugins/cookie-compliance/" target="_blank">plugin page</a> to check for the latest details on this plugin or to contact us.<br />
		When you have suggestions, encounter problems or want a custom addition to this plugin, please visit our website for the contact details.
		<br /><br />
		We would like to ask you to leave us some feedback about this plugin. With the information we receive we can keep improving this plugin.<br />
		If you experience any technival issues or have suggestions, please leave a comment on the <a href="http://wordpress.org/support/plugin/cookie-compliance" target="_blank">support forum</a> on the wordpress.org website.<br />
		You can also rate the plugin and leave a <a href="http://wordpress.org/support/view/plugin-reviews/cookie-compliance" target="_blank">review on the wordpress plugin website</a>, feedback is really usefull to us!
		

            <form method="post" action="<?php
        echo admin_url();
?>admin.php?page=cookie-compliance-settings&lang=<?php
        echo $activeLanguageShort;
?>">

                <input type="hidden" name="cookie_compliance_language" value="<?php
        echo $activeLanguageShort;
?>">

                <h3>Button texts (<?php
        echo $activeLanguageName;
?>)</h3>
                
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row">Approval</th>
                            <td>
                                <input type="text" id="cookie_compliance_buttons_submit" name="cookie_compliance_language[buttons][submit]" 
                                    value="<?php
        echo $this->language->getButtonSubmitText();
?>">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Rejection</th>
                            <td>
                                <input type="text" id="cookie_compliance_buttons_cancel" name="cookie_compliance_language[buttons][cancel]" 
                                    value="<?php
        echo $this->language->getButtonCancelText();
?>">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <h3>Pop up details (<?php
        echo $activeLanguageName;
?>)</h3>
                
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row">Message</th>
                            <td>
                                <?php
        wp_editor($this->language->getNotifyText(), 'cookie_compliance_language[notify]', array(
            'wpautop' => false
        ));
?>
                            </td>
                        </tr>
                    </tbody>
                </table>


 		<h3>Bottom bar options (<?php
        echo $activeLanguageName;
?>)</h3>
		Please take in mind that there is limited space to fill on the bottom. To still give you the possibillity to create your own way of display you can use the below box to decorate your bottom bar.<br />
		<?php
        if ($this->config->getDefaultBottompopup()) {
?>
			The below text will be displayed in stead of the pop-up. The options to show or not show the bottom bar can be set on the <a href="admin.php?page=cookie-compliance">settings page</a>.
		<?php
        } else if ($this->config->getDefaultBottomdenied()) {
?>
			The below text will be displayed when a user did not (yet) approve the usage of cookies. The options to show or not show the bottom bar can be set on the <a href="admin.php?page=cookie-compliance">settings page</a>.
		<?php
        } else {
?>
			The below text won't be displayed as you did not enable the bottom bar yet, see the <a href="admin.php?page=cookie-compliance">settings page</a> to enable the bottom bar.
		<?php
        }
?>		
                
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row">Message</th>
                            <td>
                                <?php
        wp_editor($this->language->getBottomText(), 'cookie_compliance_language[bottom][message]', array(
            'wpautop' => false
        ));
?>
                            </td>
                        </tr>
<?php
$currentbottomcss = $this->language->getBottomTextCss();
$currentbottomwidth = $this->language->getBottomTextWidth();
if ($currentbottomcss == '') {
	$currentbottomcss .= "div#cookie-compliance-bottom-overlay {" . chr(13) . chr(10);
	$currentbottomcss .= "	height: 	45px;" . chr(13) . chr(10);
	$currentbottomcss .= "}" . chr(13) . chr(10);
	$currentbottomcss .= " " . chr(13) . chr(10);
	$currentbottomcss .= "div#cookie-compliance-bottom-overlay div {" . chr(13) . chr(10);
	$currentbottomcss .= "	width: 		1000px;" . chr(13) . chr(10);
	$currentbottomcss .= "	margin: 	2px auto;" . chr(13) . chr(10);
	$currentbottomcss .= "	height:		auto;" . chr(13) . chr(10);
	$currentbottomcss .= "	padding: 	0;" . chr(13) . chr(10);
	$currentbottomcss .= "	color: 		#fff;" . chr(13) . chr(10);
	$currentbottomcss .= "}" . chr(13) . chr(10);
	$currentbottomcss .= "	" . chr(13) . chr(10);
	$currentbottomcss .= "div#cookie-compliance-bottom-overlay div p {" . chr(13) . chr(10);
	$currentbottomcss .= "	margin:		0;" . chr(13) . chr(10);
	$currentbottomcss .= "	padding:	0;" . chr(13) . chr(10);
	$currentbottomcss .= "	font-size:	12.5px;" . chr(13) . chr(10);
	$currentbottomcss .= "	letter-spacing:	0.9px;" . chr(13) . chr(10);
	if (!($currentbottomwidth == '')) $currentbottomcss .= "	width: 		" . $currentbottomwidth . "px;" . chr(13) . chr(10);
	else $currentbottomcss .= "	min-width:		850px;" . chr(13) . chr(10);
	$currentbottomcss .= "	float: 		left;" . chr(13) . chr(10);
	$currentbottomcss .= "	white-space:	nowrap;" . chr(13) . chr(10);
	$currentbottomcss .= "	line-height:	20px;" . chr(13) . chr(10);
	$currentbottomcss .= "}" . chr(13) . chr(10);
	$currentbottomcss .= "	" . chr(13) . chr(10);
	$currentbottomcss .= "div#cookie-compliance-bottom-overlay div p a {" . chr(13) . chr(10);
	$currentbottomcss .= "	color:		#fff;" . chr(13) . chr(10);
	$currentbottomcss .= "	text-decoration: underline;" . chr(13) . chr(10);
	$currentbottomcss .= "}" . chr(13) . chr(10);
}
?>
                        <tr valign="top">
                            <th scope="row">Bottom bar CSS rules</th>
                            <td>
                                <textarea style="width:500px;font-family:Courier New;" id="ookie_compliance_bottom_css" rows="10" name="cookie_compliance_language[bottom][css]"
                                    ><?php
        echo $currentbottomcss;
?></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>


                <p class="submit">
                    <input type="submit" class="button-primary" value="Save Changes">
                </p>
            </form>
        </div>
        <script type="text/javascript">
            jQuery('#cookie_compliance_language').change(function() {
                window.location.href = '<?php
        echo admin_url('admin.php?page=cookie-compliance-settings');
?>&lang=' + jQuery(this).val();
            })
        </script>

        <?php
        $this->footer();
?>

        <?php
    }
    
    private function footer()
    {

    }
    
    private function add_actions()
    {
        if (!is_admin()) {
            add_action('wp_enqueue_scripts', array(
                &$this,
                'wp_enqueue_scripts'
            ));
            add_action('wp_enqueue_scripts', array(
                &$this,
                'wp_enqueue_styles'
            ));
        }
        
        add_action('wp_head', array(
            &$this,
            'wp_head'
        ));
        add_action('wp_footer', array(
            &$this,
            'wp_footer'
        ));
    }
    
    public function wp_enqueue_scripts()
    {
        wp_enqueue_script('cookie-compliance', plugins_url('cookie-compliance') . '/cookie-compliance.min.js', array(
            'jquery'
        ));
        wp_localize_script('cookie-compliance', 'cookie_compliance_options', $this->getConfig() + array(
            'ajaxurl' => plugins_url('cookie-compliance') . '/zaf.call.php',
            'nonce' => wp_create_nonce('cookie-compliance-nonce'),
            'ga_nonce' => wp_create_nonce('cookie-compliance-ga-nonce')
        ));
    }
    
    public function wp_enqueue_styles()
    {
        if (!$this->config->getDefaultDeny()) {
            wp_enqueue_style('cookie-compliance', plugins_url('cookie-compliance') . '/cookie-compliance.css');
        } else {
            if ($this->config->getDefaultBottomdenied()) {
                wp_enqueue_style('cookie-compliance', plugins_url('cookie-compliance') . '/cookie-compliance.css');
            }
        }
    }
    
    public function wp_head()
    {
?>
        <script type="text/javascript">
<?php
echo $this->zaf_cookie_ga_init();
?>

        function cookie_compliance_head() {
            if (compliance.enabled() && compliance.check() && compliance.isAccepted()) {
<?php
        if ($this->config->getGAEnabled() && $this->config->getGAPosition() == 'header'):
		echo "cookie_compliance_ga();";
        endif;
?>
                <?php
        echo $this->config->getEnabledHead();

?>
            } else {
                <?php
        echo $this->config->getDisabledHead();
?>
            }
        }
        </script>
        <?php
    }

    private function zaf_cookie_ga_init()
    {
	$responsecode = "";

	if ($this->config->getGAEnabled()):
		$responsecode .= "var _gaq = _gaq || []; ";
	endif;

		$responsecode .= "function cookie_compliance_ga() { ";

	if ($this->config->getGAEnabled()):

		$responsecode .= "if (compliance.enabled() && compliance.check() && compliance.isAccepted()) { ";
		

		switch ($this->config->getGAType()) {
			case 'multi_tld': 
				$responsecode .= "_gaq.push(['_setAllowLinker', true]); ";
			case 'multi_sub': 
				$responsecode .= "_gaq.push(['_setDomainName', '" . $this->config->getGADomain() . "']); ";
			case 'single': 
				$responsecode .= "_gaq.push(['_setAccount', '" . $this->config->getGAUACode() . "']); ";
				$responsecode .= "_gaq.push(['_trackPageview']); ";
			}
		$responsecode .= "(function() { ";
		$responsecode .= "var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ";
		$responsecode .= "ga.src = '" . plugins_url('cookie-compliance') . "/ga.min.js'; ";
		$responsecode .= "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s); ";
		$responsecode .= "})(); ";

		$responsecode .= "} ";
	endif;

	$responsecode .= "} ";
	
	return $responsecode;
    }

    
    public function wp_footer()
    {
?><script type="text/javascript">



function cookie_compliance_footer() {
	if (compliance.enabled() && compliance.check() && compliance.isAccepted()) {
	<?php
        echo $this->config->getEnabledFooter();
?>
<?php
        if ($this->config->getGAEnabled() && !($this->config->getGAPosition() == 'header')):
		echo "cookie_compliance_ga();";
        endif;
?>
	} else {
	<?php
        echo $this->config->getDisabledFooter();
?>
<?php
        if ($this->config->getGAEnabled()):
?>	jQuery.post(compliance.options.ajaxurl, {
	    action: 'cookie_compliance_analytics',
	    path: window.location.pathname,
	    title: document.title,
	    nonce: compliance.options.ga_nonce,
	    ga_referrer: document.referrer,
	    __utma: compliance.getParameterByName('__utma'),
	    __utmb: compliance.getParameterByName('__utmb'),
	    __utmc: compliance.getParameterByName('__utmc'),
	    __utmz: compliance.getParameterByName('__utmz'),
	    zsid: compliance.getParameterByName('zsid')
	},
   	function(data) {
     		compliance.setSessionID(data);
   	});<?php
        endif;
?>
	}
}
</script>
<style type="text/css" media=screen>
<?php
	echo $this->language->getBottomTextCss();
?>
</style>

<?php
    }
    
}