<?php

namespace Runalyze\Parameter;

class ParameterSelectFileTest extends \PHPUnit_Framework_TestCase
{
	/** @var \Runalyze\Parameter\SelectFile */
	protected $object;

	protected function setUp()
    {
		$this->object = new SelectFile('path/to/file.jpg', array(
			'folder' => 'dir/',
			'extensions' => array('jpg', 'png', 'gif'),
            'filename_only' => false
		));
	}

	public function testSet()
    {
		$this->assertEquals('path/to/file.jpg', $this->object->value());

		$this->object->set('another/path/to/file.png');
		$this->assertEquals('another/path/to/file.png', $this->object->value());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testWrongExtension()
    {
		$this->object->set('path/to/file.php');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testWrongPath()
    {
		$this->object->set('../private/file.jpg');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testRootPath()
    {
		$this->object->set('/usr/file.jpg');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testNoExtension()
    {
		$this->object->set('/bin/shell');
	}

	public function testUppercaseVariantsAllowed()
    {
		$this->object->set('another/path/to/file.PNG');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testUppercaseVariantsDisallowed()
    {
		$this->object->allowUppercaseVariants(false);
		$this->object->set('another/path/to/file.PNG');
	}

    public function testFilenameOnly()
    {
        $select = new SelectFile('file.jpg', array(
            'folder' => 'dir/',
            'extensions' => array('jpg', 'png', 'gif'),
            'filename_only' => true
        ));

        $this->assertEquals('file.jpg', $select->value());
    }
}
