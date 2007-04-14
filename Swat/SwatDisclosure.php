<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Swat/SwatDisplayableContainer.php';
require_once 'Swat/SwatHtmlTag.php';
require_once 'Swat/SwatYUI.php';

/**
 * A container to show and hide child widgets
 *
 * @package   Swat
 * @copyright 2004-2007 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class SwatDisclosure extends SwatDisplayableContainer
{
	// {{{ public properties

	/**
	 * A visible title for the label shown beside the disclosure triangle
	 *
	 * @var string
	 */
	public $title = null;

	/**
	 * The initial state of the disclosure
	 *
	 * @var boolean
	 */
	public $open = true;

	// }}}
	// {{{ public function __construct()

	/**
	 * Creates a new disclosure container
	 *
	 * @param string $id a non-visible unique id for this widget.
	 *
	 * @see SwatWidget::__construct()
	 */
	public function __construct($id = null)
	{
		parent::__construct($id);
		$this->requires_id = true;

		$yui = new SwatYUI(array('dom', 'animation'));
		$this->html_head_entry_set->addEntrySet($yui->getHtmlHeadEntrySet());

		$this->addJavaScript('packages/swat/javascript/swat-disclosure.js',
			Swat::PACKAGE_ID);

		$this->addStyleSheet('packages/swat/styles/swat-disclosure.css',
			Swat::PACKAGE_ID);
	}

	// }}}
	// {{{ public function display()

	/**
	 * Displays this disclosure container
	 *
	 * Creates appropriate divs and outputs closed or opened based on the
	 * initial state.
	 *
	 * The disclosure is always displayed as opened in case the user has
	 * JavaScript turned off.
	 */
	public function display()
	{
		if (!$this->visible)
			return;

		$control_div = $this->getControlDivTag();
		$anchor = $this->getAnchorTag();
		$input = $this->getInputTag();
		$img = $this->getImgTag();
		$container_div = $this->getContainerDivTag();
		$animate_div = $this->getAnimateDivTag();

		$control_div->open();
		$anchor->open();
		$input->display();
		$img->display();
		echo ' ';
		$anchor->displayContent();
		$anchor->close();

		$container_div->open();
		$animate_div->open();
		$this->displayChildren();
		$animate_div->close();
		$container_div->close();

		Swat::displayInlineJavaScript($this->getInlineJavascript());

		$control_div->close();
	}

	// }}}
	// {{{ protected function getControlDivTag()

	protected function getControlDivTag()
	{
		$div = new SwatHtmlTag('div');
		$div->id = $this->id;
		$div->class = $this->getCSSClassString();

		return $div;
	}

	// }}}
	// {{{ protected function getContainerDivTag()

	protected function getContainerDivTag()
	{
		$div = new SwatHtmlTag('div');
		$div->class = 'swat-disclosure-container';

		return $div;
	}

	// }}}
	// {{{ protected function getAnimateDivTag()

	protected function getAnimateDivTag()
	{
		$div = new SwatHtmlTag('div');

		return $div;
	}

	// }}}
	// {{{ protected function getImgTag()

	protected function getImgTag()
	{
		$img = new SwatHtmlTag('img');
		$img->src = 'packages/swat/images/swat-disclosure-open.png';
		$img->alt = Swat::_('close');
		$img->width = 16;
		$img->height = 16;
		$img->id = $this->id.'_img';
		$img->class = 'swat-disclosure-image';

		return $img;
	}

	// }}}
	// {{{ protected function getInputTag()

	protected function getInputTag()
	{
		$input = new SwatHtmlTag('input');
		$input->type = 'hidden';
		// initial value is blank, value is set by JavaScript
		$input->value = '';
		$input->id = $this->id.'_input';

		return $input;
	}

	// }}}
	// {{{ protected function getAnchorTag()

	protected function getAnchorTag()
	{
		$anchor = new SwatHtmlTag('a');
		$anchor->class = 'swat-disclosure-anchor';
		$anchor->href = sprintf('javascript:%s_obj.toggle();', $this->id);
		$anchor->setContent($this->title);

		return $anchor;
	}

	// }}}
	// {{{ protected function getInlineJavaScript()

	/**
	 * Gets disclosure specific inline JavaScript
	 *
	 * @return string disclosure specific inline JavaScript.
	 */
	protected function getInlineJavaScript()
	{
		static $shown = false;

		if (!$shown) {
			$javascript = $this->getInlineJavaScriptTranslations();
			$shown = true;
		} else {
			$javascript = '';
		}

		$open = ($this->open) ? 'true' : 'false';
		$javascript.= sprintf("var %s_obj = new SwatDisclosure('%s', %s);",
			$this->id, $this->id, $open);

		return $javascript;
	}

	// }}}
	// {{{ protected function getInlineJavaScriptTranslations()

	/**
	 * Gets translatable string resources for the JavaScript object for
	 * this widget
	 *
	 * @return string translatable JavaScript string resources for this widget.
	 */
	protected function getInlineJavaScriptTranslations()
	{
		$open_text  = Swat::_('open');
		$close_text = Swat::_('close');

		return
			"SwatDisclosure.open_text = '{$open_text}';\n".
			"SwatDisclosure.close_text = '{$close_text}';\n";
	}

	// }}}
	// {{{ protected function getCSSClassNames()

	/**
	 * Gets the array of CSS classes that are applied to this disclosure
	 *
	 * @return array the array of CSS classes that are applied to this
	 *                disclosure.
	 */
	protected function getCSSClassNames()
	{
		$classes = array(
			'swat-disclosure',
			// always display open in case JavaScript is turned off
			'swat-disclosure-control-opened',
		);

		$classes = array_merge($classes, $this->classes);
		return $classes;
	}

	// }}}
}

?>
