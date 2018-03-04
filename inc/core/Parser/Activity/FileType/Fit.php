<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\InstallationSpecificException;
use Runalyze\Import\Exception\UnexpectedContentException;
use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\AbstractMultipleParserWithSubParser;
use Runalyze\Parser\Common\FileNameAwareParserInterface;
use Runalyze\Parser\Common\LineByLineParserTrait;

class Fit extends AbstractMultipleParserWithSubParser implements FileNameAwareParserInterface
{
    use LineByLineParserTrait;

    /** @var string */
    const PERL_FIT_ERROR_MESSAGE_START = 'main::Garmin::FIT';

    /** @var string */
    const PERL_GENERAL_MESSAGE_START = 'perl: warning:';

    /** @var bool */
    protected $ParsingStarted = false;

    /** @var FitActivity */
    protected $CurrentSubParser;

    public function __construct()
    {
        parent::__construct();

        $this->CurrentSubParser = new FitActivity();
    }

    public function parse()
    {
        $this->parseFileLineByLine();
        $this->finishCurrentSubParser();
    }

    protected function parseLine($line)
    {
        if (!$this->ParsingStarted) {
            $this->checkFirstLine($line);
        } else {
            if (substr($line, -20) == 'NAME=sport NUMBER=12' && !$this->currentContainerIsEmpty()) {
                $this->finishCurrentSubParser();
                $this->startAnotherSubParser();
            }

            $this->CurrentSubParser->parseLine($line);
        }
    }

    /**
     * @return bool
     */
    protected function currentContainerIsEmpty()
    {
        return null !== $this->CurrentSubParser && $this->CurrentSubParser->getActivityDataContainer()->ContinuousData->isEmpty();
    }

    protected function finishCurrentSubParser()
    {
        $this->CurrentSubParser->finishParsing();

        $this->Container[] = $this->CurrentSubParser->getActivityDataContainer();
    }

    protected function startAnotherSubParser()
    {
        $this->CurrentSubParser = new FitActivity();
        $this->CurrentSubParser->readMetadataForMultiSessionFrom(end($this->Container)->Metadata);
    }

    protected function checkFirstLine($line)
    {
        if (trim($line) != 'SUCCESS') {
            fclose($this->Handle);

            $this->throwErrorForFirstLine($line);
        }

        $this->ParsingStarted = true;
    }

    /**
     * @param string $line
     *
     * @throws InstallationSpecificException
     * @throws UnexpectedContentException
     * @throws UnsupportedFileException
     */
    protected function throwErrorForFirstLine($line) {
        $message = 'Reading *.fit-file failed. First line was "'.$line.'".';

        if (substr($line, 0, strlen(self::PERL_FIT_ERROR_MESSAGE_START)) == self::PERL_FIT_ERROR_MESSAGE_START) {
            throw new UnexpectedContentException($message);
        }

        if (substr($line, 0, strlen(self::PERL_GENERAL_MESSAGE_START)) == self::PERL_GENERAL_MESSAGE_START) {
            $message .= NL.NL.'See https://github.com/Runalyze/Runalyze/issues/1701';

            throw new InstallationSpecificException($message);
        }

        throw new UnsupportedFileException($message);
    }
}
