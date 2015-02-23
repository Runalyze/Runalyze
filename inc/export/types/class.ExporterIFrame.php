<?php
/**
 * This file contains class::ExporterIFrame
 * @package Runalyze\Export\Types
 */

use Runalyze\View\Activity\Linker;
use Runalyze\Model\Activity;

/**
 * Exporter for: IFrame
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Export\Types
 */
class ExporterIFrame extends ExporterAbstract {
	/**
	 * Default width
	 * @var int
	 */
	const Width = 500;

	/**
	 * Default height
	 * @var int
	 */
	const Height = 500;

	/**
	 * Display
	 */
	public function display() {
		$Code = str_replace(array("\r", "\n", "\t"), array("", "", ""), $this->getHTMLCode());

		$CodeField = new FormularTextarea('code', __('Code'), $Code);
		$CodeField->addCSSclass('fullwidth');
		$CodeField->addAttribute('rows', 3);

		$FieldsetCode = new FormularFieldset( __('HTML-Code') );

		if (System::isAtLocalhost()) {
			$FieldsetCode->addError( __('Runalyze runs on a local server. Only people in your local network will be able to see the training.') );
		}

		$FieldsetCode->addField( $CodeField );
		$FieldsetCode->addInfo( __('Add this code to your blog/website.') );

		$WidthField = new FormularInputNumber('width', __('Width'), $this->width());
		$WidthField->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
		$WidthField->setMin(200);
		$WidthField->setMax(600);

		$HeightField = new FormularInputNumber('height', __('Height'), $this->height());
		$HeightField->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
		$HeightField->setMin(200);
		$HeightField->setMax(600);

		$SubmitField = new FormularSubmit( __('Change size'), '' );

		$FieldsetCode->addField( $WidthField );
		$FieldsetCode->addField( $HeightField );
		$FieldsetCode->addField( $SubmitField );

		$FieldsetPreview = new FormularFieldset( __('Preview') );
		$FieldsetPreview->addBlock($Code);

		$Formular = new Formular( $_SERVER['SCRIPT_NAME'].'?type=IFrame&id='.$this->Context->activity()->id() );
		$Formular->addCSSclass('ajax');
		$Formular->addCSSclass('no-automatic-reload');
		$Formular->addFieldset($FieldsetCode);
		$Formular->addFieldset($FieldsetPreview);
		$Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W100 );
		$Formular->display();
	}

	/**
	 * Get HTML code for snippet
	 * @return string 
	 */
	protected function getHTMLCode() {
		$this->Context->activity()->set(Activity\Object::IS_PUBLIC, 1);
		$Linker = new Linker($this->Context->activity());

		return '<iframe style="padding:0;margin:0 auto;display:block;max-width:100%;" src="'.$Linker->publicUrl().'&amp;mode=iframe" width="'.$this->width().'" height="'.$this->height().'"></iframe>';
	}

	/**
	 * Current width
	 * @return int
	 */
	protected function width() {
		return (isset($_POST['width']) && is_numeric($_POST['width']) ? (int)$_POST['width'] : self::Width);
	}

	/**
	 * Current height
	 * @return int
	 */
	protected function height() {
		return (isset($_POST['height']) && is_numeric($_POST['height']) ? (int)$_POST['height'] : self::Height);
	}
}