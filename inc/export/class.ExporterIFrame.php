<?php
/**
 * Exporter for: HTML-IFrame-snippet
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ExporterIFrame extends ExporterEmbedded {
	/**
	 * Is this exporter without a file?
	 * @return boolean 
	 */
	public static function isWithoutFile() {
		return true;
	}

	/**
	 * Set file content
	 */
	protected function setFileContent() {
		$Code = $this->getHTMLCode();
		$Code = str_replace(array("\r", "\n", "\t"), array("", "", ""), $Code);

		$CodeField = new FormularTextarea('code', 'Code', $Code);
		$CodeField->addCSSclass('fullWidth');
		$CodeField->addAttribute('rows', 3);

		$FieldsetCode = new FormularFieldset('HTML-Code');

		if (System::isAtLocalhost()) {
			$FieldsetCode->addError('Runalyze l&auml;uft auf einem lokalen Server - so kannst du das IFrame nirgends einbinden.');
		} else {
			$FieldsetCode->addField( $CodeField );
			$FieldsetCode->addInfo('F&uuml;ge diesen HTML-Code in deinem Blog ein.');
		}

		$FieldsetPreview = new FormularFieldset('Vorschau');
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
		$Url      = System::getFullDomain().SharedLinker::getUrlFor($this->Training->id());

		return '<iframe style="padding:0;margin:0 auto;display:block;" src="'.$Url.'&amp;mode=iframe" width="500" height="500"></iframe>';
	}
}