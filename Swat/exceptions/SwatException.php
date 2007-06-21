<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Swat/SwatExceptionDisplayer.php';
require_once 'Swat/SwatExceptionLogger.php';

/**
 * An exception in Swat
 *
 * Exceptions in Swat have handy methods for outputting nicely formed error
 * messages. Call SwatException::setupHandler() to register SwatException as
 * the PHP exception handler. The SwatException handler is able to handle all
 * sub-classes of Exception by internally wrapping non-SwatExceptions in a new
 * instance of a SwatException. This allows all exceptions to be nicely
 * formatted and processed consistently.
 *
 * Custom displaying and logging of SwatExceptions can be achieved through
 * {@link SwatException::setLogger()} and {@link SwatException::setDisplayer()}.
 *
 * @package   Swat
 * @copyright 2004-2006 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class SwatException extends Exception
{
	// {{{ private properties
	
	/**
	 * A variable to represent if the excception was manually handled
	 *
	 * @var boolean
	 */
	private $handled = null;

	// }}}
	// {{{ protected properties

	protected $backtrace = null;
	protected $class = null;

	/**
	 * @var SwatExceptionDisplayer
	 */
	protected static $displayer = null;

	/**
	 * @var SwatExceptionLogger
	 */
	protected static $logger = null;

	/**
	 * Array of sensitive parameter names this exception should filter out of
	 * its stack trace
	 *
	 * @var array
	 *
	 * @see SwatException::addSensitiveParameterName()
	 */
	protected static $sensitive_param_names = array();

	// }}}
	// {{{ public static function setLogger()

	/**
	 * Sets the object that logs SwatException objects when they are processed
	 *
	 * For example:
	 * <code>
	 * SwatException::setLogger(new CustomLogger());
	 * </code>
	 *
	 * @param SwatExceptionLogger $logger the object to use to log exceptions.
	 */
	public static function setLogger(SwatExceptionLogger $logger)
	{
		self::$logger = $logger;
	}

	// }}}
	// {{{ public static function setDisplayer()

	/**
	 * Sets the object that displays SwatException objects when they are
	 * processed
	 *
	 * For example:
	 * <code>
	 * SwatException::setDisplayer(new SilverorangeDisplayer());
	 * </code>
	 *
	 * @param SwatExceptionDisplayer $displayer the object to use to display
	 *                                           exceptions.
	 */
	public static function setDisplayer(SwatExceptionDisplayer $displayer)
	{
		self::$displayer = $displayer;
	}

	// }}}
	// {{{ public static function addSensitiveParameter()

	/**
	 * Adds a parameter name to the list of sensitive parameters that are
	 * filtered upon display
	 *
	 * @param string $name the sensitive parameter name.
	 * @param string $function optional. The function of method name the
	 *                          parameter belongs to.
	 * @param string $class optional. The class name the method belongs to.
	 */
	public static function addSensitiveParameter($name, $function = '',
		$class = '')
	{
		if (strlen($class) > 0 && strlen($function) > 0)
			$name = $class.'::'.$function.'('.$name.')';
		elseif (strlen($function) > 0)
			$name = $fucntion.'('.$name.')';

		if (!in_array($name, self::$sensitive_param_names))
			self::$sensitive_param_names[] = $name;
	}

	// }}}
	// {{{ public static function setupHandler()

	/**
	 * Set the PHP exception handler to use SwatException
	 *
	 * @param string $class the exception class containing a static handle()
	 *                       method.
	 */
	public static function setupHandler($class = 'SwatException')
	{
		set_exception_handler(array($class, 'handle'));
	}

	// }}}
	// {{{ public function __construct()

	public function __construct($message = null, $code = 0)
	{
		if (is_object($message) && ($message instanceof Exception)) {
			$e = $message;
			$message = $e->getMessage();
			$code = $e->getCode();
			parent::__construct($message, $code);
			$this->file = $e->getFile();
			$this->line = $e->getLine();
			$this->backtrace = $e->getTrace();
			$this->class = get_class($e);
		} else {
			parent::__construct($message, $code);
			$this->backtrace = $this->getTrace();
			$this->class = get_class($this);
		}
	}

	// }}}
	// {{{ public function process()

	/**
	 * Processes this exception
	 *
	 * Processing involves displaying errors, logging errors and sending
	 * error message emails
	 */
	public function process($exit = true, $handled = true)
	{
		$this->handled = $handled;

		if (ini_get('display_errors'))
			$this->display();

		if (ini_get('log_errors'))
			$this->log();

		if ($exit)
			exit(1);
	}

	// }}}
	// {{{ public function log()

	/**
	 * Logs this exception
	 *
	 * The exception is logged to the webserver error log.
	 */
	public function log()
	{
		if (self::$logger === null) {
			error_log($this->getSummary(), 0);
		} else {
			$logger = self::$logger;
			$logger->log($this);
		}
	}

	// }}}
	// {{{ public function display()

	/**
	 * Display this exception
	 *
	 * The exception is display as either txt or XHMTL.
	 */
	public function display()
	{
		if (self::$displayer === null) {
			if (isset($_SERVER['REQUEST_URI'])) {
				header('Content-Type: text/html; charset=UTF-8');
				header('Content-Disposition: inline');
				echo $this->toXHTML();
			} else {
				echo $this->toString();
			}
		} else {
			$displayer = self::$displayer;
			$displayer->display($this);
		}
	}

	// }}}
	// {{{ public function getSummary()

	/**
	 * Gets a one-line short text summary of this exception
	 *
	 * This summary is useful for log entries and error email titles.
	 *
	 * @return string a one-line summary of this exception
	 */
	public function getSummary()
	{
		ob_start();

		printf("%s in file '%s' line %s",
			$this->class,
			$this->getFile(),
			$this->getLine());

		if ($this->wasHandled())
			echo ' Exception was handled';

		return ob_get_clean();
	}

	// }}}
	// {{{ public function toString()

	/**
	 * Gets this exception as a nicely formatted text block
	 *
	 * This is useful for text-based logs and emails.
	 *
	 * @return string this exception formatted as text.
	 */
	public function toString()
	{
		ob_start();

		printf("Uncaught Exception: %s\n\nMessage:\n\t%s\n\n".
			"Created in file '%s' on line %s.\n\n",
			$this->class,
			$this->getMessage(),
			$this->getFile(),
			$this->getLine());

		if ($this->wasHandled())
			echo "Exception was handled\n\n";

		echo "Stack Trace:\n";
		$count = count($this->backtrace);

		foreach ($this->backtrace as $entry) {
			$class = array_key_exists('class', $entry) ?
				$entry['class'] : null;

			$function = array_key_exists('function', $entry) ?
				$entry['function'] : null;

			if (array_key_exists('args', $entry))
				$arguments = $this->getArguments(
					$entry['args'], $function, $class);
			else
				$arguments = '';

			printf("%s. In file '%s' on line %s.\n%sMethod: %s%s%s(%s)\n",
				str_pad(--$count, 6, ' ', STR_PAD_LEFT),
				array_key_exists('file', $entry) ? $entry['file'] : 'unknown',
				array_key_exists('line', $entry) ? $entry['line'] : 'unknown',
				str_repeat(' ', 8),
				($class === null) ? '' : $class,
				array_key_exists('type', $entry) ? $entry['type'] : '',
				($function === null) ? '' : $function,
				$arguments);
		}

		echo "\n";

		return ob_get_clean();
	}

	// }}}
	// {{{ public function toXHTML()

	/**
	 * Gets this exception as a nicely formatted XHTML fragment
	 *
	 * This is nice for debugging errors on a staging server.
	 *
	 * @return string this exception formatted as XHTML.
	 */
	public function toXHTML()
	{
		ob_start();

		$this->displayStyleSheet();

		echo '<div class="swat-exception">';

		printf('<h3>Uncaught Exception: %s</h3>'.
				'<div class="swat-exception-body">'.
				'Message:<div class="swat-exception-message">%s</div>'.
				'Created in file <strong>%s</strong> '.
				'on line <strong>%s</strong>.<br /><br />',
				$this->class,
				nl2br($this->getMessage()),
				$this->getFile(),
				$this->getLine());
		if ($this->wasHandled())
			echo '<strong>Exception was handled</strong><br /><br />';

		echo 'Stack Trace:<br /><dl>';
		$count = count($this->backtrace);

		foreach ($this->backtrace as $entry) {
			$class = array_key_exists('class', $entry) ?
				$entry['class'] : null;

			$function = array_key_exists('function', $entry) ?
				$entry['function'] : null;

			if (array_key_exists('args', $entry))
				$arguments = htmlentities($this->getArguments(
					$entry['args'], $function, $class),
					null, 'UTF-8');
			else
				$arguments = '';

			printf('<dt>%s.</dt><dd>In file <strong>%s</strong> '.
				'line&nbsp;<strong>%s</strong>.<br />Method: '.
				'<strong>%s%s%s(</strong>%s<strong>)</strong></dd>',
				--$count,
				array_key_exists('file', $entry) ? $entry['file'] : 'unknown',
				array_key_exists('line', $entry) ? $entry['line'] : 'unknown',
				($class === null) ? '' : $class,
				array_key_exists('type', $entry) ? $entry['type'] : '',
				($function === null) ? '' : $function,
				$arguments);
		}

		echo '</dl></div></div>';

		return ob_get_clean();
	}

	// }}}
	// {{{ public function getClass()

	/**
	 * Gets the name of the class this exception represents
	 *
	 * This is usually, but not always, equivalent to get_class($this).
	 *
	 * @return string the name of the class this exception represents.
	 */
	public function getClass()
	{
		return $this->class;
	}

	// }}}
	// {{{ public function wasHandled()
	
	/**
	 * Tells you if the exception was handled 
	 *
	 * @reutrn boolean
	 */
	public function wasHandled()
	{
		return $this->handled;
	}
	
	// }}}
	// {{{ public static function handle()
	
	/**
	 * Handles an exception
	 *
	 * Runs the process() method on SwatException exceptions and displays all
	 * other exceptions. Also used to pass $handled = false to the process()
	 * method to represent the fact that the exception was not handled manually.
	 *
	 * @param Exception $e the exception to handle.
	 */
	public static function handle($e)
	{
		if ($e instanceof SwatException) {
			$e->process(true, false);
		} else {
			// wrap other exceptions in SwatExceptions
			$e = new SwatException($e);
			$e->process(true, false);
		}
	}

	// }}}
	// {{{ protected function getArguments()

	/**
	 * Formats a method call's arguments
	 *
	 * This method is also responsible for filtering sensitive parameters
	 * out of the final stack trace.
	 *
	 * @param array|string $args an array of arguments or a single argument.
	 * @param string $method optional. The current method or function.
	 * @param string $class optional. The current class name.
	 *
	 * @return string the arguments formatted into a comma delimited string.
	 */
	protected function getArguments($args, $function = null, $class = null)
	{
		if (is_array($args)) {
			$params = array();

			// try to get function or method parameter list using reflection
			if ($class !== null && $function !== null && class_exists($class)) {
				$class_reflector = new ReflectionClass($class);
				if ($class_reflector->hasMethod($function)) {
					$reflector = $class_reflector->getMethod($function);
					$params = $reflector->getParameters();
				}
			} elseif ($function !== null && function_exists($function)) {
				$reflector = new ReflectionFunction($function);
				$params = $reflector->getParameters();
			}

			$formatted_values = array();
			for ($i = 0; $i < count($args); $i++) {
				$value = $args[$i];

				$name = (array_key_exists($i, $params)) ?
					$params[$i]->getName() : null;
	
				if ($name !== null &&
					self::isSensitiveParamater($name, $function, $class)) {
					$formatted_value =
						$this->formatSensitiveParam($name, $value);
				} elseif (is_object($value)) {
					$formatted_value = '<'.get_class($value).' object>';
				} elseif ($value === null) {
					$formatted_value = '<null>';
				} elseif (is_string($value)) {
					$formatted_value = "'".$value."'";
				} elseif (is_array($value)) {
					$formatted_value =
						'array('.$this->getArguments($value).')';
				}

				$formatted_values[] = $formatted_value;
			}

			$output = implode(', ', $formatted_values);
		} else {
			$output = $this->getArguments(array($args), $function, $class);
		}

		return $output;
	}

	// }}}
	// {{{ protected function formatSensitiveParam()

	/**
	 * Removes sensitive information from a parameter value and formats
	 * the parameter as a string
	 *
	 * This is used, for example, to filter credit/debit card numbers from
	 * stack traces. By default, a string of the form
	 * "[$<i>$name</i> FILTERED]" is returned.
	 *
	 * @param string $name the name of the parameter.
	 * @param mixed $value the sensitive value of the parameter.
	 *
	 * @return string the filtered formatted version of the parameter.
	 *
	 * @see SwatException::$sensitive_param_names
	 */
	protected function formatSensitiveParam($name, $value)
	{
		return '[$'.$name.' FILTERED]';
	}

	// }}}
	// {{{ protected function displayStyleSheet()

	/**
	 * Displays style sheet required for XHMTL exception formatting
	 *
	 * This is purposly not in a separate file so that even if this exception
	 * causes problems including other files the exception styles will be
	 * displayed.
	 */
	protected function displayStyleSheet()
	{
		static $displayed = false;
		if (!$displayed) {
			echo "<style>\n";
			echo ".swat-exception { border: 1px solid #d43; margin: 1em; ".
				"font-family: sans-serif; background: #fff !important; ".
				"z-index: 9999 !important; color: #000; text-align: left; ".
				"min-width: 400px; }\n";

			echo ".swat-exception h3 { background: #e65; margin: 0; padding: ".
				"5px; border-bottom: 2px solid #d43; color: #fff; }\n";

			echo ".swat-exception-body { padding: 0.8em; }\n";
			echo ".swat-exception-message { margin-left: 2em; ".
				"padding: 1em; }\n";

			echo ".swat-exception dt { float: left; margin-left: 1em; }\n";
			echo ".swat-exception dd { margin-bottom: 1em; }\n";
			echo "</style>";
			$displayed = true;
		}
	}

	// }}}
	// {{{ private static function isSensitiveParamater()

	/**
	 * Gets whether or not a given paramater is sensitive
	 *
	 * @param string $name the parameter name.
	 * @param string $function optional. The function of method name the
	 *                          parameter belongs to.
	 * @param string $class optional. The class name the method belongs to.
	 *
	 * @return boolean true if this paramater is sensitive and false if it is
	 *                  not.
	 */
	private static function isSensitiveParamater($name, $function = '',
		$class = '')
	{
		if (strlen($class) > 0 && strlen($function) > 0)
			$name = $class.'::'.$function.'('.$name.')';
		elseif (strlen($function) > 0)
			$name = $fucntion.'('.$name.')';

		return (in_array($name, self::$sensitive_param_names));
	}

	// }}}
}

?>
