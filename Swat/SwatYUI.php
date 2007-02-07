<?php

require_once 'Swat/SwatHtmlHeadEntrySet.php';
require_once 'Swat/SwatYUIComponent.php';

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

/**
 * Object for building Swat HTML head entry dependencies for Yahoo UI Library
 * components
 *
 * Example usage:
 * <code>
 * $yui = new SwatYUI('dom');
 * $html_head_entries = $yui->getHtmlHeadEntrySet();
 * </code>
 *
 * @package   Swat
 * @copyright 2006-2007 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class SwatYUI
{
	// {{{ class constants

	/**
	 * @todo document me or get rid of me
	 */
	const PACKAGE_ID = 'YUI';

	/**
	 * Component mode for minimized version of JavaScript files.
	 *
	 * This is the default mode.
	 */
	const MODE_MIN = 'min';

	/**
	 * Component mode for debug version of JavaScript files.
	 */
	const MODE_DEBUG = 'debug';

	/**
	 * Component mode for normal version of JavaScript files.
	 */
	const MODE_NORMAL = '';

	// }}}
	// {{{ private static properties

	/**
	 * Static component definitions
	 *
	 * This array is used for each instance of SwatYUI and contains component
	 * definitions and dependency information.
	 *
	 * @var array
	 * @see SwatYUI::buildComponents()
	 */
	private static $components = array();

	// }}}
	// {{{ private properties

	/**
	 * The {@link SwatHtmlHeadEntrySet} required for this SwaYUI object
	 *
	 * @var SwatHtmlHeadEntrySet
	 */
	private $html_head_entry_set;

	// }}}
	// {{{ public function __construct()

	/**
	 * Creates a new SwatYUI HTML head entry set building object
	 *
	 * @param array $component_ids an array of YUI component ids to build a
	 *                              HTML head entry set for.
	 * @param string $mode the YUI component mode to use. Should be one of the
	 *                      SwatYUI::MODE_* constants. The default mode is
	 *                      {@link SwatYUI::MODE_MIN}.
	 */
	public function __construct(array $component_ids, $mode = SwatYUI::MODE_MIN)
	{
		self::buildComponents();

		if (!is_array($component_ids))
			$component_ids = array($component_ids);

		$this->html_head_entry_set =
			$this->buildHtmlHeadEntrySet($component_ids, $mode);
	}

	// }}}
	// {{{ public function getHtmlHeadEntrySet()

	/**
	 * Gets the HTML head entry set required for the YUI components of this
	 * object
	 *
	 * @return SwatHtmlHeadEntrySet
	 */
	public function getHtmlHeadEntrySet()
	{
		return $this->html_head_entry_set;
	}

	// }}}
	// {{{ private function buildHtmlHeadEntrySet()

	/**
	 * Builds the HTML head entry set required for the YUI components of this
	 * object
	 *
	 * @param array $component_ids an array of YUI component ids to build
	 *                              HTML head entries for.
	 * @param string $mode the YUI component mode to use. Should be one of the
	 *                      SwatYUI::MODE_* constants.
	 *
	 * @return SwatHtmlHeadEntrySet the full constructed set of HTML head
	 *                               entries.
	 */
	private function buildHtmlHeadEntrySet(array $component_ids, $mode)
	{
		$set = new SwatHtmlHeadEntrySet();
		foreach ($component_ids as $component_id) {
			$set->addEntrySet(
				self::$components[$component_id]->getHtmlHeadEntrySet($mode));
		}
		return $set;
	}

	// }}}
	// {{{ private static function buildComponents()

	/**
	 * Builds the YUI component definitions and dependency information
	 *
	 * Since this is a large data structure, the actual building is only done
	 * once and the result is stored in a static class variable.
	 */
	private static function buildComponents()
	{
		static $components_built = false;
		static $components = array();

		if ($components_built)
			return;

		$components['animation'] = new YUIComponent('animation');
		$components['animation']->addJavaScript(YUI::MODE_DEBUG);
		$components['animation']->addJavaScript(YUI::MODE_MIN);
		$components['animation']->addJavaScript(YUI::MODE_NORMAL);

		$components['autocomplete'] = new YUIComponent('autocomplete');
		$components['autocomplete']->addJavaScript(YUI::MODE_DEBUG);
		$components['autocomplete']->addJavaScript(YUI::MODE_MIN);
		$components['autocomplete']->addJavaScript(YUI::MODE_NORMAL);

		$components['calendar'] = new YUIComponent('calendar');
		$components['calendar']->addJavaScript(YUI::MODE_DEBUG);
		$components['calendar']->addJavaScript(YUI::MODE_MIN);
		$components['calendar']->addJavaScript(YUI::MODE_NORMAL);

		$components['connection'] = new YUIComponent('connection');
		$components['connection']->addJavaScript(YUI::MODE_DEBUG);
		$components['connection']->addJavaScript(YUI::MODE_MIN);
		$components['connection']->addJavaScript(YUI::MODE_NORMAL);

		$components['container'] = new YUIComponent('container');
		$components['container']->addJavaScript(YUI::MODE_DEBUG);
		$components['container']->addJavaScript(YUI::MODE_MIN);
		$components['container']->addJavaScript(YUI::MODE_NORMAL);

		$components['container_core'] = new YUIComponent('container');
		$components['container_core']->addJavaScript(YUI::MODE_DEBUG,
			'packages/yui/container/container_core-debug.js');

		$components['container_core']->addJavaScript(YUI::MODE_MIN,
			'packages/yui/container/container_core-min.js');

		$components['container_core']->addJavaScript(YUI::MODE_NORMAL,
			'packages/yui/container/container_core.js');

		$components['dom'] = new YUIComponent('dom');
		$components['dom']->addJavaScript(YUI::MODE_DEBUG);
		$components['dom']->addJavaScript(YUI::MODE_MIN);
		$components['dom']->addJavaScript(YUI::MODE_NORMAL);

		$components['dragdrop'] = new YUIComponent('dragdrop');
		$components['dragdrop']->addJavaScript(YUI::MODE_DEBUG);
		$components['dragdrop']->addJavaScript(YUI::MODE_MIN);
		$components['dragdrop']->addJavaScript(YUI::MODE_NORMAL);

		$components['event'] = new YUIComponent('event');
		$components['event']->addJavaScript(YUI::MODE_DEBUG);
		$components['event']->addJavaScript(YUI::MODE_MIN);
		$components['event']->addJavaScript(YUI::MODE_NORMAL);

		$components['fonts'] = new YUIComponent('fonts');
		$components['fonts']->addStyleSheet(YUI::MODE_MIN);
		$components['fonts']->addStyleSheet(YUI::MODE_NORMAL);

		$components['grids'] = new YUIComponent('grids');
		$components['grids']->addStyleSheet(YUI::MODE_MIN);
		$components['grids']->addStyleSheet(YUI::MODE_NORMAL);

		$components['logger'] = new YUIComponent('logger');
		$components['logger']->addJavaScript(YUI::MODE_DEBUG);
		$components['logger']->addJavaScript(YUI::MODE_MIN);
		$components['logger']->addJavaScript(YUI::MODE_NORMAL);

		$components['menu'] = new YUIComponent('menu');
		$components['menu']->addJavaScript(YUI::MODE_DEBUG);
		$components['menu']->addJavaScript(YUI::MODE_MIN);
		$components['menu']->addJavaScript(YUI::MODE_NORMAL);
		$components['menu']->addStyleSheet(YUI::MODE_NORMAL,
			'packages/yui/menu/assets/menu.css');

		$components['menu']->addStyleSheet(YUI::MODE_MIN,
			'packages/yui/menu/assets/menu.css');

		$components['reset'] = new YUIComponent('reset');
		$components['reset']->addStyleSheet(YUI::MODE_MIN);
		$components['reset']->addStyleSheet(YUI::MODE_NORMAL);

		$components['slider'] = new YUIComponent('slider');
		$components['slider']->addJavaScript(YUI::MODE_DEBUG);
		$components['slider']->addJavaScript(YUI::MODE_MIN);
		$components['slider']->addJavaScript(YUI::MODE_NORMAL);

		$components['treeview'] = new YUIComponent('treeview');
		$components['treeview']->addJavaScript(YUI::MODE_DEBUG);
		$components['treeview']->addJavaScript(YUI::MODE_MIN);
		$components['treeview']->addJavaScript(YUI::MODE_NORMAL);

		$components['yahoo'] = new YUIComponent('yahoo');
		$components['yahoo']->addJavaScript(YUI::MODE_DEBUG);
		$components['yahoo']->addJavaScript(YUI::MODE_MIN);
		$components['yahoo']->addJavaScript(YUI::MODE_NORMAL);

		// dependencies
		$components['animation']->addDependency($components['yahoo']);
		$components['animation']->addDependency($components['dom']);
		$components['animation']->addDependency($components['event']);

		$components['autocomplete']->addDependency($components['yahoo']);
		$components['autocomplete']->addDependency($components['dom']);
		$components['autocomplete']->addDependency($components['event']);
		$components['autocomplete']->addDependency($components['connection']);
		$components['autocomplete']->addDependency($components['animation']);

		$components['calendar']->addDependency($components['yahoo']);
		$components['calendar']->addDependency($components['dom']);
		$components['calendar']->addDependency($components['event']);

		$components['connection']->addDependency($components['yahoo']);
		$components['connection']->addDependency($components['event']);

		$components['container']->addDependency($components['yahoo']);
		$components['container']->addDependency($components['dom']);
		$components['container']->addDependency($components['event']);
		$components['container']->addDependency($components['connection']);
		$components['container']->addDependency($components['animation']);

		$components['container_core']->addDependency($components['yahoo']);
		$components['container_core']->addDependency($components['dom']);
		$components['container_core']->addDependency($components['event']);
		$components['container_core']->addDependency($components['connection']);
		$components['container_core']->addDependency($components['animation']);

		$components['dom']->addDependency($components['yahoo']);

		$components['dragdrop']->addDependency($components['yahoo']);
		$components['dragdrop']->addDependency($components['dom']);
		$components['dragdrop']->addDependency($components['event']);

		$components['event']->addDependency($components['yahoo']);

		$components['grids']->addDependency($components['fonts']);

		$components['logger']->addDependency($components['yahoo']);
		$components['logger']->addDependency($components['dom']);
		$components['logger']->addDependency($components['event']);
		$components['logger']->addDependency($components['dragdrop']);
		
		$components['menu']->addDependency($components['yahoo']);
		$components['menu']->addDependency($components['dom']);
		$components['menu']->addDependency($components['event']);
		$components['menu']->addDependency($components['fonts']);
		$components['menu']->addDependency($components['container_core']);

		$components['slider']->addDependency($components['yahoo']);
		$components['slider']->addDependency($components['dom']);
		$components['slider']->addDependency($components['event']);
		$components['slider']->addDependency($components['dragdrop']);

		$components['treeview']->addDependency($components['yahoo']);

		self::$components = $components;

		$components_built = true;
	}

	// }}}
}

?>
