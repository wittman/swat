<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Swat/SwatDisplayableContainer.php';
require_once 'Swat/SwatTitleable.php';
require_once 'Swat/SwatHtmlTag.php';

/**
 * A container with a decorative frame and optional title
 *
 * @package   Swat
 * @copyright 2004-2006 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class SwatFrame extends SwatDisplayableContainer implements SwatTitleable
{
	// {{{ public properties

	/**
	 * A visible title for this frame, or null
	 *
	 * @var string
	 */
	public $title = null;

	/**
	 * An optional visible subtitle for this frame, or null
	 *
	 * @var string
	 */
	public $subtitle = null;

	/**
	 * An optional string to separate subtitle from the title
	 *
	 * @var string
	 */
	public $title_separator = ': ';

	// }}}
	// {{{ public function getTitle()

	/**
	 * Gets the title of this frame
	 *
	 * Implements the {SwatTitleable::getTitle()} interface.
	 *
	 * @return the title of this frame.
	 */
	public function getTitle()
	{
		if ($this->subtitle === null)
			return $this->title;

		if ($this->title === null)
			return $this->subtitle;

		return $this->title.': '.$this->subtitle;
	}

	// }}}
	// {{{ public function display()

	/**
	 * Displays this frame
	 */
	public function display()
	{
		if (!$this->visible)
			return;

		$outer_div = new SwatHtmlTag('div');
		$outer_div->id = $this->id;
		$outer_div->class = $this->getCSSClassString();

		$inner_div = new SwatHtmlTag('div');
		$inner_div->class = 'swat-frame-contents';

		$outer_div->open();

		if ($this->title !== null) {

			// default header level is h2
			$level = 2;
			$ancestor = $this->parent;

			// get appropriate header level, limit to h6
			while ($ancestor !== null && $level < 6) {
				if ($ancestor instanceof SwatFrame)
					$level++;

				$ancestor = $ancestor->parent;
			}

			$header_tag = new SwatHtmlTag('h'.$level);			
			$header_tag->class = 'swat-frame-title';
			$header_tag->setContent($this->title);

			if ($this->subtitle === null) {
				$header_tag->display();
			} else {
				$span_tag = new SwatHtmlTag('span');			
				$span_tag->class = 'swat-frame-subtitle';
				$span_tag->setContent($this->subtitle);

				$header_tag->open();
				$header_tag->displayContent();
				echo $this->title_separator;
				$span_tag->display();
				$header_tag->close();
			}
		}

		$inner_div->open();
		$this->displayChildren();
		$inner_div->close();
		$outer_div->close();
	}

	// }}}
	// {{{ protected function getCSSClassNames()

	/**
	 * Gets the array of CSS classes that are applied to this frame 
	 *
	 * @return array the array of CSS classes that are applied to this frame.
	 */
	protected function getCSSClassNames()
	{
		$classes = array('swat-frame');
		$classes = array_merge($classes, $this->classes);
		return $classes;
	}

	// }}}
}

?>
