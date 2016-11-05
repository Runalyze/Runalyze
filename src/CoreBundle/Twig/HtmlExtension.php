<?php
namespace Runalyze\Bundle\CoreBundle\Twig;

class HtmlExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'runalyze.html_extension';
    }

    /**
     * @return \Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('info', array($this, 'info'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('error', array($this, 'error'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('warning', array($this, 'warning'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('okay', array($this, 'okay'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('nbsp', array($this, 'nbsp'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('nonbsp', array($this, 'nonbsp'), array('is_safe' => array('html'))),
        );
    }

    /**
     * @param string $string
     * @return string
     */
    public function info($string)
    {
        return '<p class="info">'.$string.'</p>';
    }

    /**
     * @param string $string
     * @return string
     */
    public function error($string)
    {
        return '<p class="error">'.$string.'</p>';
    }

    /**
     * @param string $string
     * @return string
     */
    public function warning($string)
    {
        return '<p class="warning">'.$string.'</p>';
    }

    /**
     * @param string $string
     * @return string
     */
    public function okay($string)
    {
        return '<p class="okay">'.$string.'</p>';
    }

    /**
     * @param string $string
     * @return string
     */
    public function nbsp($string)
    {
        return str_replace(' ', '&nbsp;', $string);
    }

    /**
     * @param string $string
     * @return string
     */
    public function nonbsp($string)
    {
        return str_replace('&nbsp;', ' ', $string);
    }
}
