<?php

namespace Runalyze\Parser\Activity\Converter;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Common\FileTypeConverterInterface;

class ZipConverter implements FileTypeConverterInterface
{
    /** @var array|null */
    protected $AcceptedFileExtensions = null;

    public function __construct(array $acceptedFileExtensions = null)
    {
        $this->AcceptedFileExtensions = $acceptedFileExtensions;
    }

    public function getConvertibleFileExtension()
    {
        return 'zip';
    }

    public function getConvertedFileName($inputFile)
    {
        return $this->readArchive($inputFile, false);
    }

    public function convertFile($inputFile)
    {
        return $this->readArchive($inputFile, true);
    }

    /**
     * @param string $inputFile
     * @param bool $extractFiles
     *
     * @return string[]
     */
    protected function readArchive($inputFile, $extractFiles)
    {
        $archive = new \ZipArchive();

        $this->tryToOpenArchive($archive, $inputFile);

        $inputFileNames = $this->getFileNamesFrom($archive);
        $outputFileNames = array_map(function($file) use ($inputFile) {
            return $inputFile.'.'.$file;
        }, $inputFileNames);

        if ($extractFiles) {
            foreach ($inputFileNames as $i => $currentFile) {
                copy('zip://'.$inputFile.'#'.$currentFile, $outputFileNames[$i]);
            }
        }

        $archive->close();

        return $outputFileNames;
    }

    /**
     * @param \ZipArchive $archive
     * @param string $inputFile
     *
     * @throws UnsupportedFileException
     */
    protected function tryToOpenArchive(\ZipArchive $archive, $inputFile)
    {
        if (true !== $archive->open($inputFile)) {
            throw new UnsupportedFileException('Can\'t open zip file.');
        }
    }

    protected function getFileNamesFrom(\ZipArchive $archive)
    {
        $files = [];

        for ($i = 0; $i < $archive->numFiles; ++$i) {
            $currentFile = $archive->getNameIndex($i);
            $pathinfo = pathinfo($currentFile);

            if (
                is_array($pathinfo) && isset($pathinfo['dirname']) && isset($pathinfo['extension']) &&
                '.' == $pathinfo['dirname'] && '.' != substr($currentFile, 0, 1) &&
                $this->isFileExtensionAllowed($pathinfo['extension'])
            ) {
                $files[] = $currentFile;
            }
        }

        return $files;
    }

    /**
     * @param string $extension
     *
     * @return bool
     */
    protected function isFileExtensionAllowed($extension)
    {
        if (is_array($this->AcceptedFileExtensions)) {
            return in_array(mb_strtolower($extension), $this->AcceptedFileExtensions);
        }

        return true;
    }
}
