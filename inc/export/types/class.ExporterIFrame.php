<?php
/**
 * This file contains class::ExporterIFrame
 * @package Runalyze\Export\Types
 */
/**
 * Exporter for: IFrame
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Export\Types
 */
class ExporterIFrame extends ExporterAbstract {
	/**
	 * Display
	 */
	public function display() {
		$Code = str_replace(array("\r", "\n", "\t"), array("", "", ""), $this->getHTMLCode());

		$CodeField = new FormularTextarea('code', 'Code', $Code);
		$CodeField->addCSSclass('fullwidth');
		$CodeField->addAttribute('rows', 3);

		$FieldsetCode = new FormularFieldset('HTML-Code');

		if (System::isAtLocalhost()) {
			$FieldsetCode->addError( __('Runalyze runs on a local server. Only people in your local network will be able to see the training.') );
		}

		$FieldsetCode->addField( $CodeField );
		$FieldsetCode->addInfo( __('Add this code to your blog/website.') );

		$FieldsetPreview = new FormularFieldset( __('Preview') );
		$FieldsetPreview->addBlock($Code);

		$Formular = new Formular();
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
		$this->Training->set('is_public', 1);
		$Url = $this->Training->Linker()->publicUrl();

		return '<iframe style="padding:0;margin:0 auto;display:block;max-width:100%;" src="'.$Url.'&amp;mode=iframe" width="500" height="500"></iframe>';
	}
}