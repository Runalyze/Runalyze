<?php

namespace Runalyze\Bundle\CoreBundle\Services\Import;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Runalyze\Import\Exception\ParserException;
use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Data\Merge\ActivityDataContainerMerger;
use Runalyze\Parser\Activity\Common\ParserInterface;
use Runalyze\Parser\Activity\Converter\FitConverter;
use Runalyze\Parser\Activity\Converter\KmzConverter;
use Runalyze\Parser\Activity\Converter\TTbinConverter;
use Runalyze\Parser\Activity\Converter\ZipConverter;
use Runalyze\Parser\Activity\FileExtensionToParserMapping;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\FileNameAwareParserInterface;
use Runalyze\Parser\Common\FileTypeConverterInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class FileImporter implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var \Runalyze\Parser\Common\FileTypeConverterInterface[] */
    protected $Converter = [];

    /** @var ZipConverter */
    protected $ZipConverter;

    /** @var FileExtensionToParserMapping */
    protected $ParserMapping;

    /** @var Filesystem */
    protected $Filesystem;

    /** @var string|null */
    protected $DirectoryForFailedImports;

    /** @var ParserException[] */
    protected $ConverterExceptions = [];

    /** @var bool */
    protected $RemoveFiles = true;

    /**
     * @param FitConverter $fitConverter
     * @param TTbinConverter $ttbinConverter
     * @param string|null $directoryForFailedImports
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        FitConverter $fitConverter,
        TTbinConverter $ttbinConverter,
        $directoryForFailedImports = null,
        LoggerInterface $logger = null
    )
    {
        $this->ParserMapping = new FileExtensionToParserMapping();
        $this->Converter = [$fitConverter, $ttbinConverter, new KmzConverter()];
        $this->ZipConverter = new ZipConverter($this->getSupportedFileExtensions());
        $this->Filesystem = new Filesystem();
        $this->DirectoryForFailedImports = $directoryForFailedImports;
        $this->logger = $logger ?: new NullLogger();
    }

    public function disableFileDeletion($flag = true)
    {
        $this->RemoveFiles = !$flag;
    }

    /**
     * @param string $fileName
     */
    protected function remove($fileName)
    {
        if ($this->RemoveFiles) {
            $this->Filesystem->remove($fileName);
        }
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
        $this->ConverterExceptions = [];
        $results = new FileImportResultCollection();

        foreach ($fileNames as $fileName) {
            $results->merge($this->importSingleFile($fileName));
        }

        return $this->handleResults($results);
    }

    /**
     * @param string $fileName
     * @return FileImportResultCollection
     */
    public function importSingleFile($fileName)
    {
        $this->ConverterExceptions = [];
        $results = new FileImportResultCollection();
        $convertedFileNames = $this->tryToConvertFileNameIfRequired($fileName);

        foreach ($convertedFileNames as $convertedFileName) {
            $results->add($this->getFileImportResultFor($convertedFileName, $fileName));
        }

        return $this->handleResults($results);
    }

    /**
     * @param FileImportResultCollection $results
     * @return FileImportResultCollection
     */
    protected function handleResults(FileImportResultCollection $results)
    {
        $this->findAndMergeRelatedHrmAndGpx($results);

        $this->logFileImports($results);

        return $results;
    }

    protected function findAndMergeRelatedHrmAndGpx(FileImportResultCollection $results)
    {
        $matchingIndices = [];
        $lookupTable = array_flip($results->getAllFileNames());

        foreach ($lookupTable as $fileName => $index) {
            if (substr($fileName, -4) == '.hrm' && isset($lookupTable[substr($fileName, 0, -4).'.gpx'])) {
                $matchingIndices[$index] = $lookupTable[substr($fileName, 0, -4).'.gpx'];
            }
        }

        if (!empty($matchingIndices)) {
            $this->mergeResultsOfRelatedHrmAndGpx($results, $matchingIndices);
        }
    }

    protected function mergeResultsOfRelatedHrmAndGpx(FileImportResultCollection $results, $matchingIndices)
    {
        foreach ($matchingIndices as $firstIndex => $secondIndex) {
            if (0 == $results[$firstIndex]->getNumberOfActivities() || 0 == $results[$secondIndex]->getNumberOfActivities()) {
                continue;
            }

            $merger = new ActivityDataContainerMerger(
                $results[$firstIndex]->getContainer(0),
                $results[$secondIndex]->getContainer(0)
            );

            $results[] = new FileImportResult(
                [$merger->getResultingContainer()],
                $results[$firstIndex]->getFileName(),
                $results[$firstIndex]->getOriginalFileName()
            );

            $this->remove($results[$secondIndex]->getFileName());

            unset($results[$firstIndex]);
            unset($results[$secondIndex]);
        }

        $results->reindex();
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
    protected function tryToConvertFileNameIfRequired($fileName)
    {
        try {
            return $this->convertFileNameIfRequired($fileName);
        } catch (ParserException $e) {
            $this->ConverterExceptions[$fileName] = $e;
        }

        return [$fileName];
    }

    /**
     * @param string $fileName
     * @return string[] converted file names
     */
    protected function convertFileNameIfRequired($fileName)
    {
        $extension = mb_strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ('zip' == $extension) {
            $convertedFileNames = [];
            $zipFiles = $this->ZipConverter->convertFile($fileName);

            foreach ($zipFiles as $zipFile) {
                $convertedFileNames = array_merge($convertedFileNames, $this->tryToConvertFileNameIfRequired($zipFile));
                $this->remove($zipFile);
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
        if (isset($this->ConverterExceptions[$fileName])) {
            throw $this->ConverterExceptions[$fileName];
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $parserClass = $this->ParserMapping->getParserClassFor($extension);

        if (null === $parserClass) {
            throw new UnsupportedFileException('Unsupported file extension.');
        }

        /** @var ParserInterface $parser */
        $parser = new $parserClass;
        $this->letParserParseFile($parser, $fileName);

        $container = [];
        $numContainer = $parser->getNumberOfActivities();

        for ($i = 0; $i < $numContainer; ++$i) {
            $container[] = $parser->getActivityDataContainer($i);
        }

        return $container;
    }

    /**
     * @param ParserInterface $parser
     * @param string $fileName
     *
     * @throws ParserException
     */
    protected function letParserParseFile(ParserInterface $parser, $fileName)
    {
        if ($parser instanceof LoggerAwareInterface) {
            $parser->setLogger($this->logger);
        }

        if ($parser instanceof FileNameAwareParserInterface) {
            $parser->setFileName($fileName);
        } elseif ($parser instanceof FileContentAwareParserInterface) {
            if (file_exists($fileName) && $content = file_get_contents($fileName)) {
                $parser->setFileContent($content);
            } else {
                throw new ParserException('Cannot open file.');
            }
        } else {
            throw new ParserException('Chosen parser has no method to set file name or content.');
        }

        $parser->parse();
    }

    protected function logFileImports(FileImportResultCollection $results)
    {
        foreach ($results as $result) {
            $this->logSingleFileImport($result);
        }
    }

    protected function logSingleFileImport(FileImportResult $result)
    {
        if ($result->isFailed()) {
            $this->logger->error(sprintf('File upload of %s failed.', $this->getFileNameForLog($result)), [
                'exception' => $result->getException()
            ]);

            if (null !== $this->DirectoryForFailedImports && '' != $this->DirectoryForFailedImports) {
                try {
                    $this->Filesystem->rename($result->getOriginalFileName(), $this->DirectoryForFailedImports.'/'.pathinfo($result->getOriginalFileName(), PATHINFO_BASENAME), true);
                } catch (IOException $e) {
                    $this->logger->warning('File cannot be renamed.', [
                        'file' => $result->getOriginalFileName(),
                        'exception' => $e
                    ]);
                }
            } else {
                $this->remove($result->getOriginalFileName());
            }
        } else {
            $this->logger->info(sprintf('Successfull file upload of %s.', $this->getFileNameForLog($result)));
            $this->remove($result->getOriginalFileName());
        }

        if ($result->getOriginalFileName() != $result->getFileName()) {
            $this->remove($result->getFileName());
        }
    }

    /**
     * @param FileImportResult $result
     * @return string
     */
    protected function getFileNameForLog(FileImportResult $result)
    {
        if ($result->getOriginalFileName() != $result->getFileName()) {
            return sprintf('%s (original %s)', pathinfo($result->getFileName(), PATHINFO_BASENAME), pathinfo($result->getOriginalFileName(), PATHINFO_BASENAME));
        }

        return pathinfo($result->getFileName(), PATHINFO_BASENAME);
    }
}
