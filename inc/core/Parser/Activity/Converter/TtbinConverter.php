<?php

namespace Runalyze\Parser\Activity\Converter;

use Runalyze\Import\Exception\InstallationSpecificException;
use Runalyze\Import\Exception\UnexpectedContentException;
use Runalyze\Parser\Common\AbstractShellBasedFileTypeConverter;

class TtbinConverter extends AbstractShellBasedFileTypeConverter
{
    /** @var string */
    protected $PathToConverter;

    /**
     * @param string $pathToTtbincnv
     */
    public function __construct($pathToTtbincnv)
    {
        $this->PathToConverter = $pathToTtbincnv;
    }

    public function getConvertibleFileExtension()
    {
        return 'ttbin';
    }

    public function getConvertedFileName($inputFile)
    {
        return $inputFile.'.tcx';
    }

    protected function buildCommand($inputFile, $outputFile)
    {
        return sprintf('%s -t -E < "%s" > "%s"', $this->PathToConverter, $inputFile, $outputFile);
    }

    protected function checkFirstLineOfOutput($firstLine)
    {
        if (strpos($firstLine, 'ttbincnv') !== false) {
            $message = 'Executing ttbincnv did not work: '.$firstLine;
            $message .= NL.NL.'You may need to compile ttbincnv for your environment.';

            throw new InstallationSpecificException($message);
        } elseif (substr($firstLine, 0, 1) != '<') {
            throw new UnexpectedContentException('Parsing your *.ttbin-file failed: '.$firstLine);
        }
    }
}
