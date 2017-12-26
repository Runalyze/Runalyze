<?php

namespace Runalyze\Parser\Common;

use Runalyze\Import\Exception\ParserException;

trait LineByLineParserTrait
{
    /** @var string */
    protected $FileName;

    /** @var resource|null */
    protected $Handle = null;

    /**
     * @param string $file
     */
    public function setFileName($file)
    {
        $this->FileName = $file;
    }

    public function parseFileLineByLine()
    {
        if (!method_exists($this, 'parseLine')) {
            throw new \RuntimeException('Line-by-line parser must implement parseLin($line)');
        }

        $this->Handle = @fopen($this->FileName, "r");

        if (false === $this->Handle) {
            throw new ParserException(sprintf('File "%s" can\'t be opened for reading.', $this->FileName));
        }

        while (($line = stream_get_line($this->Handle, 4096, PHP_EOL)) !== false && !feof($this->Handle)) {
            $this->parseLine($line);
        }

        fclose($this->Handle);
    }
}
