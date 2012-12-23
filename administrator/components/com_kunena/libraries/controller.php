<?php
/**
 * Kunena Component
 * @package Kunena.Framework
 *
 * @copyright (C) 2008 - 2012 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

jimport ( 'joomla.application.component.helper' );

/**
 * Base controller class for Kunena.
 *
 * @since		2.0
 */
class KunenaController extends JControllerLegacy {
	public $app = null;
	public $me = null;
	public $config = null;

	var $_escape = 'htmlspecialchars';
	var $_redirect = null;
	var $_message= null;
	var $_messageType = null;

	public function __construct() {
		parent::__construct ();
		$this->profiler = KunenaProfiler::instance('Kunena');
		$this->app = JFactory::getApplication();
		$this->config = KunenaFactory::getConfig();
		$this->me = KunenaUserHelper::getMyself();

		// Save user profile if it didn't exist.
		if (!$this->me->userid && !$this->me->exists()) {
			$this->me->save();
		}
	}

	/**
	 * Method to get the appropriate controller.
	 *
	 * @return	object	Kunena Controller
	 * @since	1.6
	 */
	public static function getInstance($prefix = 'Kunena', $config = array()) {
		static $instance = null;

		if (!$prefix) $prefix = 'Kunena';
		if (! empty ( $instance ) && !isset($instance->home)) {
			return $instance;
		}

		$app = JFactory::getApplication();
		$view = strtolower ( JRequest::getWord ( 'view', $app->isAdmin() ? 'cpanel' : 'home' ) );
		$path = JPATH_COMPONENT . "/controllers/{$view}.php";

		// If the controller file path exists, include it ... else die with a 500 error.
		if (file_exists ( $path )) {
			require_once $path;
		} else {
			JError::raiseError ( 500, JText::sprintf ( 'COM_KUNENA_INVALID_CONTROLLER', ucfirst ( $view ) ) );
		}

		// Set the name for the controller and instantiate it.
		if ($app->isAdmin()) {
			$class = $prefix . 'AdminController' . ucfirst ( $view );
		} else {
			$class = $prefix . 'Controller' . ucfirst ( $view );
		}
		if (class_exists ( $class )) {
			$instance = new $class ();
		} else {
			JError::raiseError ( 500, JText::sprintf ( 'COM_KUNENA_INVALID_CONTROLLER_CLASS', $class ) );
		}

		return $instance;
	}

	/**
	 * Method to display a view.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function display($cachable = false, $urlparams = false) {
		KUNENA_PROFILER ? $this->profiler->mark('beforeDisplay') : null;
		KUNENA_PROFILER ? KunenaProfiler::instance()->start('function '.__CLASS__.'::'.__FUNCTION__.'()') : null;

		// Get the document object.
		$document = JFactory::getDocument ();

		// Set the default view name and format from the Request.
		$vName = JRequest::getWord ( 'view', $this->app->isAdmin() ? 'cpanel' : 'home' );
		$lName = JRequest::getWord ( 'layout', 'default' );
		$vFormat = $document->getType ();

		if ($this->app->isAdmin()) {
			// Load admin language files
			KunenaFactory::loadLanguage('com_kunena.sys', 'admin');
			KunenaFactory::loadLanguage('com_kunena.install', 'admin');
			KunenaFactory::loadLanguage('com_kunena.controllers', 'admin');
			KunenaFactory::loadLanguage('com_kunena.models', 'admin');
			KunenaFactory::loadLanguage('com_kunena.views', 'admin');
			// Load last to get deprecated language files to work
			KunenaFactory::loadLanguage('com_kunena', 'site');
			KunenaFactory::loadLanguage('com_kunena', 'admin');

			// Version warning
			require_once KPATH_ADMIN . '/install/version.php';
			$version = new KunenaVersion();
			$version_warning = $version->getVersionWarning();
			if (! empty ( $version_warning )) {
				$this->app->enqueueMessage ( $version_warning, 'notice' );
			}
		} else {
			// Load site language files
			KunenaFactory::loadLanguage('com_kunena.controllers');
			KunenaFactory::loadLanguage('com_kunena.models');
			KunenaFactory::loadLanguage('com_kunena.views');
			KunenaFactory::loadLanguage('com_kunena.templates');
			KunenaFactory::loadLanguage('com_kunena.sys', 'admin');
			// Load last to get deprecated language files to work
			KunenaFactory::loadLanguage('com_kunena');

			$menu = $this->app->getMenu ();
			$active = $menu->getActive ();
			if (!$active) {
				JError::raiseError ( 404, JText::_ ( 'COM_KUNENA_NO_ACCESS' ) );
			}

			// Check if menu item was correctly routed
			$routed = $menu->getItem ( KunenaRoute::getItemID() );

			if ($vFormat=='html' && !empty($routed->id) && (empty($active->id) || $active->id != $routed->id)) {
				// Routing has been changed, redirect
				// FIXME: check possible redirect loops!
				// FIXME: Joomla 3.0: redirect disabled for now, no idea why it's not working
				//$this->app->redirect (KunenaRoute::_(null, false));
			}

			// Joomla 2.5+ multi-language support
			/* // FIXME:
			if (isset($active->language) && $active->language != '*') {
				$language = JFactory::getDocument()->getLanguage();
				if (strtolower($active->language) != strtolower($language)) {
					$this->redirect (KunenaRoute::_(null, false));
				}
			}
			*/
		}

		$view = $this->getView ( $vName, $vFormat );
		if ($view) {
			if ($this->app->isSite() && $vFormat=='html') {
				$common = $this->getView ( 'common', $vFormat );
				$model = $this->getModel ( 'common' );
				$common->setModel ( $model, true );
				$view->ktemplate = $common->ktemplate = KunenaFactory::getTemplate();
				$view->common = $common;
			}

			// Set the view layout.
			$view->setLayout ( $lName );

			// Get the appropriate model for the view.
			$model = $this->getModel ( $vName );

			// Push the model into the view (as default).
			$view->setModel ( $model, true );

			// Push document object into the view.
			$view->document = $document;

			// Render the view.
			if ($vFormat=='html') {
				JPluginHelper::importPlugin('kunena');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('onKunenaDisplay', array('start', $view));
				$view->displayAll ();
				$dispatcher->trigger('onKunenaDisplay', array('end', $view));
			} else {
				$view->displayLayout ();
			}
		}

		KUNENA_PROFILER ? KunenaProfiler::instance()->stop('function '.__CLASS__.'::'.__FUNCTION__.'()') : null;
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * If escaping mechanism is one of htmlspecialchars or htmlentities.
	 *
	 * @param  mixed $var The output to escape.
	 * @return mixed The escaped value.
	 */
	public function escape($var) {
		if (in_array ( $this->_escape, array ('htmlspecialchars', 'htmlentities' ) )) {
			return call_user_func ( $this->_escape, $var, ENT_COMPAT, 'UTF-8' );
		}
		return call_user_func ( $this->_escape, $var );
	}

	/**
	 * Sets the _escape() callback.
	 *
	 * @param mixed $spec The callback for _escape() to use.
	 */
	public function setEscape($spec) {
		$this->_escape = $spec;
	}

	public function getRedirect() {
		return $this->_redirect;
	}
	public function getMessage() {
		return $this->_message;
	}
	public function getMessageType() {
		return $this->_messageType;
	}

	protected function redirectBack($fragment = '') {
		$httpReferer = JRequest::getVar ( 'HTTP_REFERER', JUri::base ( true ), 'server' );
		JFactory::getApplication ()->redirect ( $httpReferer.($fragment ? '#'.$fragment : '') );
	}

}
