<?php
class Language {
    
    static private $localedir = './inc/locale';
    
    public function __construct() {
        putenv("LANG=$language"); 
        setlocale(LC_ALL, $language);
        $domain = 'runalyze';
        bindtextdomain('runalyze', $this->localdir); 
        textdomain('runalyze');
    }
    /**
     * 
     * @return string
     */
    public function availableLanguages() {
        $languages=array();
        $languages['de']='German';
        return $languages;
    }
    
    /**
     * 
     * @param type $domainname
     * @param type $dir
     */
    public function addTextDomain($domainname, $dir) {
        bindtextdomain($domainname, $dir);
    }
    
    /**
     * Set Language for user
     * @return boolean
     */
    public function setLanguage() {
        return true;        
    }
    
    /**
     * Get all available languages
     * @return array
     */
    public function getLanguages() {
        
    }
    /*
    * Returns the translation for a textstring
    * @param string $text
    */
   public function __($text) {
       return gettext($text);
   }

   /*
    * Echo the translation for a textstring
    * @param string $text
    */
   public function _e($text) {
       return gettext($text);
   }

   /*
    * Return singular/plural translation for a textstring
    * @param string $text
    */
   public function _n($msg1, $msg2, $n) {
       return ngettext($msg1, $msg2, $n);
   }

   /*
    * Echo singular/plural translation for a textstring
    * @param string $text
    */
   public function _ne($msg1, $msg2, $n) {
       return ngettext($msg1, $msg2, $n);
   }
    /**
     * get browser language
     * @return type 
     */
    private function getBrowserLanguage() {
        return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    }
}