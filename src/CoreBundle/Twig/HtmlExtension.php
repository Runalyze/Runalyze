<?php
namespace Runalyze\Bundle\CoreBundle\Twig;

class HtmlExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('info', array($this, 'infoFilter'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('error', array($this, 'errorFilter'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('warning', array($this, 'warningFilter'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('okay', array($this, 'okayFilter'), array('is_safe' => array('html'))),
        );
    }

    public function infoFilter($string)
    {
        $text =  '<p class="info">'.$string.'</p>';
        return $text;
    }
    
    public function errorFilter($string)
    {
        $text =  '<p class="error">'.$string.'</p>';
        return $text;
    }
    
    public function warningFilter($string)
    {
        $text =  '<p class="warning">'.$string.'</p>';
        return $text;
    }

    public function okayFilter($string)
    {
        $text =  '<p class="okay">'.$string.'</p>';
        return $text;
    }

    public function getName()
    {
        return 'app_extension';
    }
}