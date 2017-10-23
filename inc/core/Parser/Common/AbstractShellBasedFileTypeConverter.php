<?php

namespace Runalyze\Parser\Common;

use Runalyze\Import\Exception\UnsupportedFileException;

abstract class AbstractShellBasedFileTypeConverter implements FileTypeConverterInterface
{
    abstract public function getConvertibleFileExtension();

    abstract public function getConvertedFileName($inputFile);

    /**
     * @param string $inputFile
     * @param string $outputFile
     *
     * @return string
     */
    abstract protected function buildCommand($inputFile, $outputFile);

    public function convertFile($inputFile)
    {
        $outputFile = $this->getConvertedFileName($inputFile);

        shell_exec($this->buildCommand($inputFile, $outputFile).' 2>&1');

        $this->readFirstLineOfOutputAndForwardToSubclass($outputFile);

        return $outputFile;
    }

    private function readFirstLineOfOutputAndForwardToSubclass($outputFile)
    {
        $handle = @fopen($outputFile, 'r');

        if (!$handle) {
            throw new UnsupportedFileException(sprintf('Output file %s not found.', $outputFile));
        }

        try {
            $this->checkFirstLineOfOutput(
                stream_get_line($handle, 4096, PHP_EOL)
            );
        } catch (\Exception $e) {
            unlink($outputFile);

            throw $e;
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param string $firstLine
     */
    abstract protected function checkFirstLineOfOutput($firstLine);
}
