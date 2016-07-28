<?php

namespace Runalyze\Bundle\CoreBundle\Console\Formatter;

use Symfony\Component\Console\Formatter\OutputFormatter;

class HtmlOutputFormatter extends OutputFormatter
{
    /** @var string */
    const CLI_COLORS_PATTERN = '/\033\[(([\d+];?)*)m(.*?)\033\[(([\d+];?)*)m/i';

    /**
     * @var string[]
     */
    protected $ReplaceStyle = [
        '30' => 'color:rgba(0,0,0,1)',
        '31' => 'color:rgba(230,50,50,1)',
        '32' => 'color:rgba(50,230,50,1)',
        '33' => 'color:rgba(230,230,50,1)',
        '34' => 'color:rgba(50,50,230,1)',
        '35' => 'color:rgba(230,50,150,1)',
        '36' => 'color:rgba(50,230,230,1)',
        '37' => 'color:rgba(250,250,250,1)',
        '40' => 'color:rgba(0,0,0,1)',
        '41' => 'background-color:rgba(230,50,50,1)',
        '42' => 'background-color:rgba(50,230,50,1)',
        '43' => 'background-color:rgba(230,230,50,1)',
        '44' => 'background-color:rgba(50,50,230,1)',
        '45' => 'background-color:rgba(230,50,150,1)',
        '46' => 'background-color:rgba(50,230,230,1)',
        '47' => 'background-color:rgba(250,250,250,1)',
        '1' => 'font-weight:bold',
        '4' => 'text-decoration:underline',
        '8' => 'visibility:hidden',
    ];

    /**
     * {@inheritdoc}
     */
    public function format($message)
    {
        $formatted = parent::format($message);
        $escaped = htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8');
        $converted = preg_replace_callback(self::CLI_COLORS_PATTERN, function ($matches) {
            return $this->replaceFormat($matches);
        }, $escaped);

        return $converted;
    }

    /**
     * @param array $matches
     *
     * @return string
     */
    private function replaceFormat(array $matches)
    {
        $text = $matches[3];
        $styles = explode(';', $matches[1]);
        $css = array_intersect_key($this->ReplaceStyle, array_flip($styles));

        return sprintf('<span style="%s">%s</span>', implode(';', $css), $text);
    }
}
