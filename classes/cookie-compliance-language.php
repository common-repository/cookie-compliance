<?php

class Cookie_Compliance_Language {
    
    static $defaults = array(
       'notify' => 'In order for this site to work properly, and in order to evaluate and improve the site we need to store small files (called cookies) on your computer. <br/> Over 90% of all websites do this, however, since the 25th of May 2011 we are required by EU regulations to obtain your consent first. What do you say?',
	'bottom' => array(
            'message' => 'Change this text!',
            'textwidth' => '850'
        ),
       'buttons' => array(
            'submit' => 'That\'s fine!',
            'cancel' => 'Deny'
        )
    );
    
    var $language = '';
    
    public function Cookie_Compliance_Language($language, $options = array()) {
        $this->language = $language;
        $this->config = (array) $options + (array) Cookie_Compliance_Language::$defaults;
    }
    
    public function getLanguageShortCode() {
        return $this->language;
    }
    
    public function getConfig() {
        return $this->config;
    }
    
    public function getNotifyText() {
        return stripslashes($this->config['notify']);
    }
    
    public function getButtonSubmitText() {
        return stripslashes($this->config['buttons']['submit']);
    }

    public function getButtonCancelText() {
        return stripslashes($this->config['buttons']['cancel']);
    }

    public function getBottomTextWidth() {
        return stripslashes($this->config['bottom']['textwidth']);
    }

    public function getBottomText() {
        return stripslashes($this->config['bottom']['message']);
    }

    public function getBottomTextCss() {
        return stripslashes($this->config['bottom']['css']);
    }

}