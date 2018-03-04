<?php

namespace Runalyze\Parser\Activity\FileType;

use Psr\Log\LoggerAwareInterface;
use Runalyze\Parser\Activity\Common\AbstractMultipleParser;
use Runalyze\Parser\Activity\Common\ParserInterface;
use Runalyze\Parser\Activity\Common\PauseDetectionCapableParserInterface;
use Runalyze\Parser\Activity\Common\PauseDetectionCapableTrait;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\XmlParserTrait;
use SimpleXMLElement;

class Tcx extends AbstractMultipleParser implements FileContentAwareParserInterface, PauseDetectionCapableParserInterface
{
    use XmlParserTrait;
    use PauseDetectionCapableTrait;

    /** @var bool */
    protected $IsFromRuntastic = false;

    public function parse()
    {
        $this->checkOrigin();
        $this->parseMultiSportSessions();
        $this->parseStandardActivities();
        $this->parseCourses();
    }

    protected function checkOrigin()
    {
        $this->IsFromRuntastic = false !== strpos($this->Xml['creator'], 'runtastic');
    }

    protected function parseMultiSportSessions()
    {
        if (!isset($this->Xml->Activities->MultiSportSession)) {
            return;
        }

        if (isset($this->Xml->Activities->MultiSportSession->FirstSport)) {
            foreach ($this->Xml->Activities->MultiSportSession->FirstSport as $sport) {
                foreach ($sport->Activity as $activity) {
                    $this->parseSingleActivity($activity);
                }
            }
        }

        if (isset($this->Xml->Activities->MultiSportSession->NextSport)) {
            foreach ($this->Xml->Activities->MultiSportSession->NextSport as $sport) {
                foreach ($sport->Activity as $activity) {
                    $this->parseSingleActivity($activity);
                }
            }
        }
    }

    protected function parseStandardActivities()
    {
        if (isset($this->Xml->Activities->Activity)) {
            foreach ($this->Xml->Activities->Activity as $activity) {
                $this->parseSingleActivity($activity);
            }
        }
    }

    protected function parseCourses()
    {
        if (isset($this->Xml->Courses->Course)) {
            foreach ($this->Xml->Courses->Course as $course) {
                $this->parseSingleActivityWith(new TcxCourse($course));
            }
        }
    }

    protected function parseSingleActivity(SimpleXMLElement $activity)
    {
        $parser = $this->IsFromRuntastic ? new TcxActivityRuntastic($activity) : new TcxActivity($activity);

        $this->parseSingleActivityWith($parser);
    }

    protected function parseSingleActivityWith(ParserInterface $parser)
    {
        if ($parser instanceof LoggerAwareInterface) {
            $parser->setLogger($this->logger);
        }

        if ($parser instanceof PauseDetectionCapableParserInterface) {
            $parser->activatePauseDetection($this->DetectPauses);
        }

        $parser->parse();

        $this->Container[] = $parser->getActivityDataContainer();
    }
}
