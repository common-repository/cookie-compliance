<?php

class Cookie_Compliance_Config {

    static $defaults = array(
        'cookie' => 'cookie_compliance',
        'enabled' => array(
            'head'      => "alert('Cookies are enabled!');", 
            'footer'    => "",
        ),
        'disabled' => array(
            'head'      => "alert('Cookies are disabled!');", 
            'footer'    => "",
        ),
    );
    
    private $options = array();
    private $setting = "cookie_compliance_options";
    
    public function Cookie_Compliance_Config($options = array()) {
        $this->options = (array) $options + (array) Cookie_Compliance_Config::$defaults;
    }
    
    public function getOptions() {
        return $this->options;
    }
    
    public function getGAEnabled() {
        return stripslashes($this->options['ga']['enabled']) === 'on';
    }
    
    public function getGAUACode() {
        return stripslashes($this->options['ga']['uacode']);
    }
    
    public function getGAType() { 
        return stripslashes($this->options['ga']['type']);
    }
    
    public function getGADomain() {
        return stripslashes($this->options['ga']['domain']);
    }

    public function getGAPosition() {
        return stripslashes($this->options['ga']['position']);
    }
    
    public function getEnabledHead() {
        return stripslashes($this->options['enabled']['head']);
    }

    public function getEnabledFooter() {
        return stripslashes($this->options['enabled']['footer']);
    }

    public function getDisabledHead() {
        return stripslashes($this->options['disabled']['head']);
    }

    public function getDisabledFooter() {
        return stripslashes($this->options['disabled']['footer']);
    }

    public function getDefaultDeny() {
        return stripslashes($this->options['ccsettings']['deny']);
    }

    public function getDefaultBottomdenied() {
        return stripslashes($this->options['ccsettings']['bottomdenied']);
    }

    public function getDefaultBottompopup() {
        return stripslashes($this->options['ccsettings']['bottompopup']);
    }

    public function getDefaultNocookie() {
        return stripslashes($this->options['ccsettings']['nocookie']);
    }


    public function getOldDefaultDeny() {
        return stripslashes($this->options['default']['deny']);
    }

    public function getOldDefaultBottomdenied() {
        return stripslashes($this->options['default']['bottomdenied']);
    }

    public function getOldDefaultBottompopup() {
        return stripslashes($this->options['default']['bottompopup']);
    }

    public function getOldDefaultNocookie() {
        return stripslashes($this->options['default']['nocookie']);
    }
    
}