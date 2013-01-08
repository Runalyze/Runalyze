<?php
/**
 * Exporter for: HTML-snippet
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ExporterHTML extends ExporterEmbedded {
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
		$CodeField->addAttribute('rows', 8);

		$FieldsetCode = new FormularFieldset('HTML-Code');
		$FieldsetCode->addField( $CodeField );
		$FieldsetCode->addInfo('F&uuml;ge diesen HTML-Code in deinem Blog ein.');

		$FieldsetPreview = new FormularFieldset('Vorschau');
		$FieldsetPreview->addBlock($Code);

		if (!$this->Training->isPublic())
			$FieldsetPreview->addWarning('Da das Training privat ist, enth&auml;lt die HTML-Ansicht keinen Link zum Training.');

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
		$Date     = $this->Training->getDate(false);
		$Time     = $this->Training->getTimeString();
		$Title    = $this->Training->hasDistance() ? $this->Training->getDistanceString().' ' : '';
		$Title   .= $this->Training->getTitle();
		$Pace     = $this->Training->hasDistance() ? $this->Training->get('pace').'/km' : '';
		$Elev     = $this->Training->get('elevation') > 0 ? $this->Training->get('elevation').' hm' : '';
		$Heart    = $this->Training->hasPulse() ? $this->Training->get('pulse_avg').'bpm' : '';
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
	<a href="http://www.runalyze.de/" class="runalyze-emb-runalyze">runalyze.de</a>
	<strong>'.$Title.'</strong> <small>am '.$Date.'</small><br />
	<div class="runalyze-emb-infos">
		'.$Spans.'
	</div>
	'.$UrlLink.'
	<div class="runalyze-clear"></div>
</div>
<script type="text/javascript" src="'.(Request::isHttps() ? System::getFullDomain().'/lib/embedded.local.js' : 'http://user.runalyze.de/lib/embedded.js').'"></script>';
	}
}