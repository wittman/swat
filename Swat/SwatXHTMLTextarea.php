<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Swat/SwatTextarea.php';
require_once 'Swat/SwatFormField.php';
require_once 'Swat/SwatCheckbox.php';

/**
 * A text area that validates its content as an XHTML fragment against the
 * XHTML Strict DTD
 *
 * @package   Swat
 * @copyright 2006 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class SwatXHTMLTextarea extends SwatTextarea
{
	// {{{ public properties

	/**
	 * Whether or not to allow the user to ignore validation errors
	 *
	 * Setting htis property to true will present a checkbox to the user
	 * allowing him or her to ignore validation errors generated by the XML
	 * parser.
	 *
	 * @var boolean
	 */
	public $allow_ignore_validation_errors = false;

	// }}}
	// {{{ protected properties

	/**
	 * An array of document validation errors used by the custom error handler
	 * for loading XML documents
	 *
	 * @var array
	 */
	protected static $validation_errors = array();

	/**
	 * Whether or not this XHTML entry has validation errors or not
	 *
	 * @var boolean
	 */
	protected $has_validation_errors = false;

	/**
	 * Embedded form field to contain checkbox control used to ignore XHTML
	 * validation errors
	 *
	 * @var SwatFormField
	 */
	protected $ignore_errors_field = null;

	/**
	 * Embedded checkbox control used to ignore XHTML validation errors
	 *
	 * @var SwatCheckbox
	 */
	protected $ignore_errors_checkbox = null;

	/**
	 * An internal flag that is set to true when embedded widgets have been
	 * created
	 *
	 * @var boolean
	 *
	 * @see SwatXHTMLTextarea::createEmbeddedWidgets()
	 */
	protected $widgets_created = false;

	// }}}
	// {{{ public function process

	public function process()
	{
		// {{{ defines the xhtml template

		static $xhtml_template = '';

		if (strlen($xhtml_template) == 0) {
			$xhtml_template = <<<XHTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<title>SwatXHTMLTextarea Content</title>
	</head>
	<body>
		<div>
		%s
		</div>
	</body>
</html>

XHTML;
		}

		// }}}

		parent::process();

		$this->createEmbeddedWidgets();
		$this->ignore_errors_field->process();

		$ignore_validation_errors = ($this->allow_ignore_validation_errors &&
			$this->ignore_errors_checkbox->value);

		$xhtml_content = sprintf($xhtml_template, $this->value);

		$html_errors_value = self::initializeErrorHandler();

		$document = new DOMDocument();
		$document->resolveExternals = true;
		$document->validateOnParse = true;
		$document->loadXML($xhtml_content);

		if (count(self::$validation_errors) > 0 && !$ignore_validation_errors) {
			$this->addMessage($this->getValidationErrorMessage());
			$this->has_validation_errors = true;
		}

		self::restoreErrorHandler($html_errors_value);
	}

	// }}}
	// {{{ public function display()

	public function display()
	{
		$this->createEmbeddedWidgets();

		if (!$this->visible)
			return;

		parent::display();

		if ($this->allow_ignore_validation_errors &&
			($this->has_validation_errors ||
			$this->ignore_errors_checkbox->value))
			$this->ignore_errors_field->display();
	}

	// }}}
	// {{{ public static function handleValidationErrors()

	/**
	 * Handles errors generated by loading and validation of malformed or
	 * non-conformant XML documents
	 *
	 * @param integer $errno the level of the error raised.
	 * @param string $errstring the error message.
	 */
	public static function handleValidationErrors($errno, $errstr)
	{
		$error = $errstr;
		self::$validation_errors[] = $error;
	}

	// }}}
	// {{{ protected function getValidationErrorMessage()

	/**
	 * Gets a human readable error message for XHTML validation errors on
	 * this textarea's value
	 *
	 * @return SwatMessage a human readable error message for XHTML validation
	 *                      errors on this textarea's value.
	 */
	protected function getValidationErrorMessage()
	{
		$ignored_errors = array(
			'extra content at the end of the document',
			'premature end of data in tag html',
			'opening and ending tag mismatch between html and body',
			'opening and ending tag mismatch between body and html',
		);

		$errors = array();
		$load_xml_error = '/^DOMDocument::loadXML\(\): (.*)?$/u';
		$validate_error = '/^DOMDocument::validate\(\): (.*)?$/u';
		foreach (self::$validation_errors as $error) {
			$matches = array();

			// parse errors into human form
			if (preg_match($validate_error, $error, $matches) === 1) {
				$error = $matches[1];
			} elseif (preg_match($load_xml_error, $error, $matches) === 1) {
				$error = $matches[1];
			}

			// further humanize
			$error = str_replace('tag mismatch:', 'tag mismatch between',
				$error);

			// remove some stuff that only makes sense in document context
			$error = preg_replace('/\s?line:? [0-9]+\s?/ui', ' ', $error);
			$error = preg_replace('/in entity[:,.]?/ui', '', $error);
			$error = strtolower($error);
			$error = trim($error);

			if (!in_array($error, $ignored_errors))
				$errors[] = $error;
		}

		$content = '%s must be valid XHTML markup: ';
		$content.= '<ul><li>'.implode(',</li><li>', $errors).'.</li></ul>';
		$message = new SwatMessage($content, SwatMessage::ERROR);
		$message->content_type = 'text/xml';

		return $message;
	}

	// }}}
	// {{{ protected function createEmbeddedWidgets()

	protected function createEmbeddedWidgets()
	{
		if (!$this->widgets_created) {
			$this->ignore_errors_field =
				new SwatFormField($this->id.'_ignore_field');

			$this->ignore_errors_field->title =
				Swat::_('Ignore XHTML validation errors');

			$this->ignore_errors_field->parent = $this;
			
			$this->ignore_errors_checkbox =
				new SwatCheckbox($this->id.'_ignore_checkbox');

			$this->ignore_errors_field->add(
				$this->ignore_errors_checkbox);

			$this->widgets_created = true;
		}
	}

	// }}}
	// {{{ private static function initializeErrorHandler()

	/**
	 * Initializes the custom error handler for loading and validating XML
	 * files
	 *
	 * Make sure to call {@link SwatXHTMLTextarea::restoreErrorHandler()} some
	 * time after calling this function.
	 *
	 * @return boolean the old value of the ini value for html_errors.
	 */
	private static function initializeErrorHandler()
	{
		self::$validation_errors = array();
		$html_errors = ini_set('html_errors', false);
		set_error_handler(array(__CLASS__, 'handleValidationErrors'),
			E_NOTICE | E_WARNING);

		return $html_errors;
	}

	// }}}
	// {{{ private static function restoreErrorHandler()

	/**
	 * Restores the custom error handler used for loading and validating XML
	 * files
	 *
	 * @param boolean $html_errors whether to turn on or off html_errors when
	 *                              restoring regular error handling.
	 *
	 * @see SwatXHTMLTextarea::initializeErrorHandler()
	 */
	private static function restoreErrorHandler($html_errors)
	{
		ini_set('html_errors', $html_errors);
		restore_error_handler();
	}

	// }}}
}

?>
