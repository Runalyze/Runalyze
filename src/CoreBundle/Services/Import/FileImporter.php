<?php

namespace Runalyze\Bundle\CoreBundle\Services\Import;

use Runalyze\Import\Exception\ParserException;
use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\ParserInterface;
use Runalyze\Parser\Activity\Converter\FitConverter;
use Runalyze\Parser\Activity\Converter\KmzConverter;
use Runalyze\Parser\Activity\Converter\TTbinConverter;
use Runalyze\Parser\Activity\Converter\ZipConverter;
use Runalyze\Parser\Activity\FileExtensionToParserMapping;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\FileNameAwareParserInterface;
use Runalyze\Parser\Common\FileTypeConverterInterface;

class FileImporter
{
    /** @var \Runalyze\Parser\Common\FileTypeConverterInterface[] */
    protected $Converter = [];

    /** @var ZipConverter */
    protected $ZipConverter;

    /** @var FileExtensionToParserMapping */
    protected $ParserMapping;

    public function __construct(
        FitConverter $fitConverter,
        TTbinConverter $ttbinConverter
    )
    {
        $this->ParserMapping = new FileExtensionToParserMapping();
        $this->Converter = [$fitConverter, $ttbinConverter, new KmzConverter()];
        $this->ZipConverter = new ZipConverter($this->getSupportedFileExtensions());
    }

    /**
     * @return array
     */
    public function getSupportedFileExtensions()
    {
        return array_merge(
            array_keys(FileExtensionToParserMapping::MAPPING),
            array_map(function (FileTypeConverterInterface $converter) {
                return $converter->getConvertibleFileExtension();
            }, $this->Converter),
            ['zip']
        );
    }

    /**
     * @param array $fileNames
     * @return FileImportResultCollection
     */
    public function importFiles(array $fileNames)
    {
        $results = new FileImportResultCollection();

        foreach ($fileNames as $fileName) {
            $results->merge($this->importSingleFile($fileName));
        }

        return $results;
    }

    /**
     * @param string $fileName
     * @return FileImportResultCollection
     */
    public function importSingleFile($fileName)
    {
        $results = new FileImportResultCollection();
        $convertedFileNames = $this->convertFileNameIfRequired($fileName);

        foreach ($convertedFileNames as $convertedFileName) {
            $results->add($this->getFileImportResultFor($convertedFileName, $fileName));
        }

        return $results;
    }

    /**
     * @param string $fileName
     * @param string|null $originalFileName
     * @return FileImportResult
     */
    protected function getFileImportResultFor($fileName, $originalFileName = null)
    {
        if (null === $originalFileName) {
            $originalFileName = $fileName;
        }

        try {
            return new FileImportResult(
                $this->parseSingleFile($fileName),
                $fileName,
                $originalFileName
            );
        } catch (ParserException $e) {
            return new FileImportResult([], $fileName, $originalFileName, $e);
        }
    }

    /**
     * @param string $fileName
     * @return string[] converted file names
     */
    protected function convertFileNameIfRequired($fileName)
    {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        if ('zip' == $extension) {
            $convertedFileNames = [];
            $zipFiles = $this->ZipConverter->convertFile($fileName);

            foreach ($zipFiles as $zipFile) {
                $convertedFileNames = array_merge($convertedFileNames, $this->convertFileNameIfRequired($zipFile));
            }

            return $convertedFileNames;
        }

        foreach ($this->Converter as $converter) {
            if ($converter->getConvertibleFileExtension() == $extension) {
                $result = $converter->convertFile($fileName);

                return is_array($result) ? $result : [$result];
            }
        }

        return [$fileName];
    }

    /**
     * @param string $fileName
     * @return ActivityDataContainer[]
     *
     * @throws UnsupportedFileException
     * @throws ParserException
     */
    protected function parseSingleFile($fileName)
    {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $parser = $this->ParserMapping->getParserClassFor($extension);

        if (null === $parser) {
            throw new UnsupportedFileException();
        }

        /** @var ParserInterface $parser */
        $parser = new $parser;

        if ($parser instanceof FileNameAwareParserInterface) {
            $parser->setFileName($fileName);
        } elseif ($parser instanceof FileContentAwareParserInterface) {
            $parser->setFileContent(file_get_contents($fileName));
        } else {
            throw new ParserException('Chosen parser has no method to set file name or content.');
        }

        $parser->parse();

        $container = [];
        $numContainer = $parser->getNumberOfActivities();

        for ($i = 0; $i < $numContainer; ++$i) {
            $container[] = $parser->getActivityDataContainer($i);
        }

        return $container;
    }
}
