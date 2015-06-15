<?php
/**
 * This file contains class::PluginConfigurationWindow
 * @package Runalyze\Plugin
 */
/**
 * Plugin configuration window
 * @author Hannes Christiansen
 * @package Runalyze\Plugin
 */
class PluginConfigurationWindow {
	/**
	 * Plugin
	 * @var \Plugin
	 */
	protected $Plugin;

	/**
	 * Constructor
	 * @param Plugin $Plugin
	 */
	public function __construct(Plugin &$Plugin) {
		$this->Plugin = $Plugin;

		$this->handlePostData();
	}

	/**
	 * Handle post data
	 */
	protected function handlePostData() {
		if (!empty($_POST)) {
			$this->updateConfiguration();
			$this->setReloadCommand();
		}
	}

	/**
	 * Update configuration
	 */
	protected function updateConfiguration() {
		if (isset($_POST['edit']) && $_POST['edit'] == 'true') {
			$this->Plugin->Configuration()->updateFromPost();
		}
	}

	/**
	 * Set reload command
	 */
	protected function setReloadCommand() {
		Ajax::setPluginIDtoReload( $this->Plugin->id() );
		Ajax::setReloadFlag( Ajax::$RELOAD_PLUGINS );
		echo Ajax::getReloadCommand();
	}

	/**
	 * Display
	 */
	public function display() {
		$this->displayHeader();
		$this->displayForm();

		if ($this->Plugin->type() == PluginType::Tool && $this->Plugin->isActive()) {
			$this->displayLinkToTool();
		}
	}

	/**
	 * Display header
	 */
	protected function displayHeader() {
		$name = ($this->Plugin instanceof PluginTool)
			? $this->Plugin->getWindowLink()
			: $this->Plugin->name();

		$Links = array();
		$Links[] = array('tag' => Ajax::window('<a href="'.ConfigTabPlugins::getExternalUrl().'">'.__('back to overview').'</a>'));

		echo '<div class="panel-heading">';
		echo '<div class="panel-menu">';
		echo Ajax::toolbarNavigation($Links);
		echo '</div>';
		echo '<h1>'.__('Plugin configuration').': '.$name.'</h1>';
		echo '</div>';
	}

	/**
	 * Display form
	 */
	protected function displayForm() {
		$Formular = new Formular(Plugin::$CONFIG_URL.'?id='.$this->Plugin->id(), 'post');
		$Formular->addCSSclass('ajax');
		$Formular->addCSSclass('no-automatic-reload');
		$Formular->addHiddenValue('edit', 'true');
		$Formular->addFieldset( $this->getFieldsetForDescription() );
		$Formular->addFieldset( $this->getFieldsetForValues() );
		$Formular->addFieldset( $this->getFieldsetForActivation() );
		$Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W100 );
		$Formular->display();
	}

	/**
	 * Get fieldset: description
	 * @return \FormularFieldset
	 */
	protected function getFieldsetForDescription() {
		$Fieldset = new FormularFieldset( __('Description') );
		$Fieldset->addText( $this->Plugin->description() );

		if ($this->Plugin->isInActive()) {
			$Fieldset->addWarning( __('The plugin is deactivated.') );
		}

		return $Fieldset;
	}

	/**
	 * Get fieldset: values
	 * @return \FormularFieldset
	 */
	protected function getFieldsetForValues() {
		$Fieldset = new FormularFieldset( __('Configuration') );

		if ($this->Plugin->Configuration()->isEmpty()) {
			$Fieldset->addInfo( __('There are no settings.') );
		} else {
			foreach ($this->Plugin->Configuration()->objects() as $Value) {
				$Fieldset->addField( $Value->getFormField() );
			}

			$Fieldset->addField( new FormularSubmit( __('Save'), '') );
		}

		return $Fieldset;
	}

	/**
	 * Get fieldset: activation
	 * @return \FormularFieldset
	 */
	protected function getFieldsetForActivation() {
		$activationLink = $this->Plugin->isInActive()
			? $this->Plugin->getConfigLink( __('Activate plugin'), '&active='.Plugin::ACTIVE)
			: $this->Plugin->getConfigLink( __('Deactivate plugin'), '&active='.Plugin::ACTIVE_NOT);

		$Fieldset = new FormularFieldset( __('Activation') );
		$Fieldset->addInfo( $activationLink );

		return $Fieldset;
	}

	/**
	 * Display link to tool
	 */
	protected function displayLinkToTool() {
		$Linklist = new BlocklinkList();
		$Linklist->addCompleteLink( $this->Plugin->getWindowLink(Icon::$CALCULATOR.' '.__('Open tool'), true) );
		$Linklist->display();
	}
}