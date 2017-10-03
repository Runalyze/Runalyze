<?php

namespace Runalyze\Parser\Activity\Converter;

use Runalyze\Import\Exception\InstallationSpecificException;
use Runalyze\Import\Exception\ParserException;
use Runalyze\Import\Exception\UnexpectedContentException;
use Runalyze\Parser\Common\AbstractShellBasedFileTypeConverter;

class FitConverter extends AbstractShellBasedFileTypeConverter
{
    /** @var string */
    const PERL_FIT_ERROR_MESSAGE_START = 'main::Garmin::FIT';

    /** @var string */
    const PERL_GENERAL_MESSAGE_START = 'perl: warning:';

    /** @var string */
    protected $PathToPerl;

    /** @var string */
    protected $PathToFitPerlScript;

    /**
     * @param string $pathToPerl
     * @param string $pathToFitPerlScript
     */
    public function __construct($pathToPerl, $pathToFitPerlScript)
    {
        $this->PathToPerl = $pathToPerl;
        $this->PathToFitPerlScript = $pathToFitPerlScript;
    }

    /**
     * @return string
     */
    public function getConvertibleFileExtension()
    {
        return 'fit';
    }

    public function getConvertedFileName($inputFile)
    {
        return $inputFile.'temp';
    }

    protected function buildCommand($inputFile, $outputFile)
    {
        return sprintf('%s %s "%s" 1>"%s"', $this->PathToPerl, $this->PathToFitPerlScript, $inputFile, $outputFile);
    }

    protected function checkFirstLineOfOutput($firstLine)
    {
        if (trim($firstLine) != 'SUCCESS') {
            $message = 'Reading *.fit-file failed. First line was "'.$firstLine.'".';

            if (substr($firstLine, 0, strlen(self::PERL_FIT_ERROR_MESSAGE_START)) == self::PERL_FIT_ERROR_MESSAGE_START) {
                throw new UnexpectedContentException($message);
            }

            if (substr($firstLine, 0, strlen(self::PERL_GENERAL_MESSAGE_START)) == self::PERL_GENERAL_MESSAGE_START) {
                $message .= NL.NL.'See https://github.com/Runalyze/Runalyze/issues/1701';

                throw new InstallationSpecificException($message);
            }

            throw new ParserException($message);
        }
    }
}
