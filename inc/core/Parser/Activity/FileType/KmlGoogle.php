<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\AbstractSingleParser;
use Runalyze\Parser\Activity\Common\Data\GpsDistanceCalculator;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\XmlParserTrait;

class KmlGoogle extends AbstractSingleParser implements FileContentAwareParserInterface
{
    use XmlParserTrait;

    /** @var string */
    protected $CoordinatesXPath = '//coordinates';

    /** @var float [km] */
    protected $CurrentDistance = 0.0;

    /**
     * @param string|null $namespace
     */
    public function __construct($namespace = null)
    {
        parent::__construct();

        $this->setNamespace($namespace);
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace = 'kml')
    {
        $this->CoordinatesXPath = '//'.(null !== $namespace ? $namespace.':.' : '').'coordinates';
    }

    public function parse()
    {
        $this->checkThatThereAreCoordinates();
        $this->parseCoordinates();
    }

    protected function checkThatThereAreCoordinates()
    {
        $coordinates = $this->Xml->xpath($this->CoordinatesXPath);

        if (empty($coordinates)) {
            throw new UnsupportedFileException('Kml file must contain coordinates.');
        }
    }

    protected function parseCoordinates()
    {
        foreach ($this->Xml->xpath($this->CoordinatesXPath) as $coordinates) {
            $lines = preg_split('/\r\n|\r|\n/', (string)$coordinates);
            $thisLineHasValidPoints = false;

            foreach ($lines as $lineIndex => $line) {
                $parts = explode(',', $line);
                $num = count($parts);

                if (($num == 3 || $num == 2) && ($parts[0] != 0.0 || $parts[1] != 0.0)) {
                    if ($lineIndex > 0 && $thisLineHasValidPoints) {
                        $this->CurrentDistance += GpsDistanceCalculator::gpsDistance(
                            $parts[1],
                            $parts[0],
                            end($this->Container->ContinuousData->Latitude),
                            end($this->Container->ContinuousData->Longitude)
                        );
                    }

                    $this->Container->ContinuousData->Latitude[] = $parts[1];
                    $this->Container->ContinuousData->Longitude[] = $parts[0];
                    $this->Container->ContinuousData->Altitude[] = ($num > 2) ? $parts[2] : null;
                    $this->Container->ContinuousData->Distance[] = $this->CurrentDistance;

                    $thisLineHasValidPoints = true;
                }
            }
        }
    }
}
