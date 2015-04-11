<?php
/**
 * This file contains class::ExporterHTML
 * @package Runalyze\Export\Types
 */

use Runalyze\View\Activity\Linker;

/**
 * Exporter for: HTML
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Export\Types
 */
class ExporterHTML extends ExporterAbstract {
	/**
	 * Display
	 */
	public function display() {
		$Code = str_replace(array("\r", "\n", "\t"), array("", "", ""), $this->getHTMLCode());

		$CodeField = new FormularTextarea('code', __('Code'), $Code);
		$CodeField->addCSSclass('fullwidth');
		$CodeField->addAttribute('rows', 8);

		$FieldsetCode = new FormularFieldset( __('HTML-Code') );
		$FieldsetCode->addField( $CodeField );
		$FieldsetCode->addInfo( __('Add this code to your blog/website.') );

		$FieldsetPreview = new FormularFieldset( __('Preview') );
		$FieldsetPreview->addBlock($Code);

		if (!$this->Context->activity()->isPublic())
			$FieldsetPreview->addWarning( __('Your training is private: There is no link included.') );

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
		$Linker = new Linker($this->Context->activity());
		$Url      = $Linker->publicUrl();
		$Date     = $this->Context->dataview()->date();
		$Time     = $this->Context->dataview()->duration()->string();
		$Title    = $this->Context->activity()->distance() > 0 ? $this->Context->dataview()->distance().' ' : '';
		$Title   .= $this->Context->dataview()->titleByTypeOrSport();
		$Pace     = $this->Context->activity()->distance() > 0 ? $this->Context->dataview()->pace()->valueWithAppendix() : '';
		$Elev     = $this->Context->dataview()->elevation();
		$Heart    = $this->Context->activity()->hrAvg() > 0 ? $this->Context->dataview()->hrAvg()->string() : '';
		$Spans    = '';

		if ($Time != '')
			$Spans .= '<span class="runalyze-emb-time">'.$Time.'</span>';
		if ($Pace != '')
			$Spans .= '<span class="runalyze-emb-pace">'.$Pace.'</span>';
		if ($Heart != '')
			$Spans .= '<span class="runalyze-emb-heart">'.$Heart.'</span>';
		if ($Elev != '')
			$Spans .= '<span class="runalyze-emb-elev">'.$Elev.'</span>';

		$UrlLink = (System::isAtLocalhost()) ? '' : '<a href="'.$Url.'" class="runalyze-emb-share">'.$Url.'</a>';

		return '<div class="runalyze-emb">
	<a href="https://runalyze.com/" class="runalyze-emb-runalyze">runalyze.com</a>
	<strong>'.$Title.'</strong> <small>'.$Date.'</small><br>
	<div class="runalyze-emb-infos">
		'.$Spans.'
	</div>
	'.$UrlLink.'
	<div class="runalyze-clear"></div>
</div>
<script src="https://runalyze.com/lib/embedded.js"></script>';
	}
}