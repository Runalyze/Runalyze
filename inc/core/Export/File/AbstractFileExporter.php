<?php
/**
 * This file contains class::AbstractExporter
 * @package Runalyze\Export\File
 */

namespace Runalyze\Export\File;

use Runalyze\Export\AbstractExporter;

/**
 * Create exporter for given type
 *
 * @author Hannes Christiansen
 * @package Runalyze\Export\File
 */
abstract class AbstractFileExporter extends AbstractExporter
{
    /** @var string */
    const URL = 'call/call.Exporter.export.php';

    /**
     * File content to write
     * @var string
     */
    protected $FileContent = '';

    /**
     * Get extension
     * @return string
     */
    abstract public function extension();

    /**
     * Export
     */
    abstract protected function createFile();

    /**
     * @return int
     */
    abstract public function enum();

    /**
     * @return string
     */
    public function iconClass()
    {
        return 'fa-file-text-o';
    }

    /**
     * @return string
     */
    public function url()
    {
        return self::URL.'?file=true&id='.$this->Context->activity()->id().'&typeid='.$this->enum();
    }

    /**
     * Add indents to file content
     */
    final protected function formatFileContentAsXML()
    {
        $XML = new \DOMDocument('1.0');
        $XML->preserveWhiteSpace = false;
        $XML->loadXML( $this->FileContent );
        $XML->formatOutput = true;

        $this->FileContent = $XML->saveXML();
    }

    /**
     * Get file content
     * @return string
     */
    final public function fileContent()
    {
        return $this->FileContent;
    }

	/**
	 * Create file but don't start download
	 */
	final public function createFileWithoutDirectDownload()
	{
		$this->createFile();
	}

    /**
     * Download content
     */
    final public function downloadFile()
    {
        header("Content-Type: text/plain");
        header("Content-Disposition: attachment; filename=".$this->filename()."");

        $this->createFile();

        print $this->FileContent;
    }

    /**
     * Get filename
     * @return string
     */
    final public function filename()
    {
        if (is_null($this->Context)) {
            return 'undefined.'.$this->extension();
        }

        return self::fileNameStart().date('Y-m-d_H-i', $this->Context->activity()->timestamp()).'_'.$this->Context->activity()->id().'.'.$this->extension();
    }

    /**
     * Get file name start
     * @return string
     */
    public static function fileNameStart()
    {
        return \SessionAccountHandler::getId().'-Activity_';
    }
}