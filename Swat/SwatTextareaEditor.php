<?php

/**
 * A what-you-see-is-what-you-get (WYSIWYG) XHTML textarea editor widget
 *
 * This textarea editor widget is powered by TinyMCE, which, like Swat is
 * licensed under the LGPL. See {@link http://tinymce.moxiecode.com/} for
 * details.
 *
 * @package   Swat
 * @copyright 2022 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class SwatTextareaEditor extends SwatTextarea
{
    // {{{ class constants

    const MODE_VISUAL = 1;
    const MODE_SOURCE = 2;

    // }}}
    // {{{ public properties

    public static $tiny_mce_api_key = null;

    /**
     * The text highlight colors, null will show the defaults
     */
    public static $color_map = null;

    /**
     * Width of the editor
     *
     * Specified in CSS units (percent, pixels, ems, etc). If not specified,
     * the {@link SwatTextarea::$cols} property will determine the width of
     * the editor.
     *
     * @var string
     */
    public $width = null;

    /**
     * Height of the editor
     *
     * Specified in CSS units (percent, pixels, ems, etc). If not specified,
     * the {@link SwatTextarea::$rows} property will determine the height of
     * the editor.
     *
     * @var string
     */
    public $height = null;

    /**
     * Base-Href
     *
     * Optional base-href, used to reference images and other urls in the
     * editor.
     *
     * @var string
     */
    public $base_href = null;

    /**
     * Editing mode to use
     *
     * Must be one of either {@link SwatTextareaEditor::MODE_VISUAL} or
     * {@link SwatTextareaEditor::MODE_SOURCE}.
     *
     * Defaults to <kbd>SwatTextareaEditor::MODE_VISUAL</kbd>.
     *
     * @var integer
     */
    public $mode = self::MODE_VISUAL;

    /**
     * Whether or not the mode switching behavior is enabled
     *
     * If set to false, only {@link SwatTextAreaEditor::$mode} will be ignored
     * and only {@link SwatTextAreaEditor::MODE_VISUAL} will be available for
     * the editor.
     *
     * @var boolean
     */
    public $modes_enabled = true;

    /**
     * An optional JSON server used to provide uploaded image data to the
     * insert image dialog
     *
     * If specified, the insert image diaplog will show a list of thumbnails
     * from which the user can select an image to insert.
     *
     * The server should return a JSON response formatted as follows:
     * <code>
     * [
     *   {
     *     'id':     $image_id,
     *     'images': {
     *       $size_shortname : {
     *         'title':  $visible_size_title,
     *         'uri':    $uri_of_image_at_size,
     *         'width':  $width_of_image_at_size,
     *         'height': $height_of_image_at_size
     *       },
     *       ... more image sizes ...
     *     }
     *   },
     *   ... more images ...
     * ]
     * </code>
     *
     * The size shortname <i>pinky</i> must be present and should be 48x48
     * pixels. If a size shortname of <i>small</i> is present, it will be
     * selected as the default size.
     *
     * Other data my be returned by the JSON server, and will be ignored.
     *
     * @var string
     */
    public $image_server;

    /**
     * Base-Href
     *
     * @var string
     *
     * @deprecated Use {@link SwatTextareaEditor::$base_href} instead.
     */

    public $basehref = null;

    // }}}
    // {{{ public function __construct()

    /**
     * Creates a new what-you-see-is-what-you-get XHTML textarea editor
     *
     * @param string $id a non-visible unique id for this widget.
     *
     * @see SwatWidget::__construct()
     */
    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->requires_id = true;
        $this->rows = 30;

        if (self::$tiny_mce_api_key !== null) {
            $tiny_mce_url =
                "https://cdn.tiny.cloud/1/" .
                self::$tiny_mce_api_key .
                "/tinymce/5/tinymce.min.js";
        } else {
            $tiny_mce_url =
                "https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js";
        }

        $this->addExternalJavaScript($tiny_mce_url);
        $this->addJavaScript(
            'packages/swat/javascript/swat-z-index-manager.js'
        );
    }

    // }}}
    // {{{ public function display()

    public function display()
    {
        if (!$this->visible) {
            return;
        }

        SwatWidget::display();

        // textarea tags cannot be self-closing when using HTML parser on XHTML
        $value = $this->value === null ? '' : $this->value;

        // escape value for display because we actually want to show entities
        // for editing
        $value = htmlspecialchars($value);

        $div_tag = new SwatHtmlTag('div');
        $div_tag->class = 'swat-textarea-editor-container';
        $div_tag->open();

        $textarea_tag = new SwatHtmlTag('textarea');
        $textarea_tag->name = $this->id;
        $textarea_tag->id = $this->id;
        $textarea_tag->class = $this->getCSSClassString();
        // NOTE: The attributes rows and cols are required in
        //       a textarea for XHTML strict.
        $textarea_tag->rows = $this->rows;
        $textarea_tag->cols = $this->cols;
        $textarea_tag->setContent($value);
        $textarea_tag->accesskey = $this->access_key;

        // set element styles if width and/or height are specified
        if ($this->width !== null || $this->height !== null) {
            if ($this->width !== null && $this->height !== null) {
                $textarea_tag->style = sprintf(
                    'width: %s; height: %s;',
                    $this->width,
                    $this->height
                );
            } elseif ($this->width !== null) {
                $textarea_tag->style = sprintf('width: %s;', $this->width);
            } else {
                $textarea_tag->style = sprintf('height: %s;', $this->height);
            }
        }

        if (!$this->isSensitive()) {
            $textarea_tag->disabled = 'disabled';
        }

        $textarea_tag->display();

        // hidden field to preserve editing mode in form data
        $input_tag = new SwatHtmlTag('input');
        $input_tag->type = 'hidden';
        $input_tag->id = $this->id . '_mode';
        $input_tag->value = $this->mode;
        $input_tag->display();

        $div_tag->close();

        Swat::displayInlineJavaScript($this->getInlineJavaScript());
    }

    // }}}
    // {{{ public function getFocusableHtmlId()

    /**
     * Gets the id attribute of the XHTML element displayed by this widget
     * that should receive focus
     *
     * @return string the id attribute of the XHTML element displayed by this
     *                 widget that should receive focus or null if there is
     *                 no such element.
     *
     * @see SwatWidget::getFocusableHtmlId()
     */
    public function getFocusableHtmlId()
    {
        return null;
    }

    // }}}
    // {{{ protected function getConfig()

    protected function getConfig()
    {
        $buttons = implode(' ', $this->getConfigButtons());

        $blockformats = array(
            'Paragraph=p',
            'Blockquote=blockquote',
            'Preformatted=pre',
            'Header 1=h1',
            'Header 2=h2',
            'Header 3=h3',
            'Header 4=h4',
            'Header 5=h5',
            'Header 6=h6'
        );

        $blockformats = implode('; ', $blockformats);

        $modes = $this->modes_enabled ? 'yes' : 'no';
        $image_server = $this->image_server ? $this->image_server : '';

        $has_api_key =
            self::$tiny_mce_api_key !== null && !empty(self::$tiny_mce_api_key);
        $paste_plugin = $has_api_key ? ' powerpaste' : ' paste';

        $config = array(
            'selector' => '#' . $this->id,
            'toolbar' => $buttons,
            // https://www.tiny.cloud/docs/configure/editor-appearance/#block_formats
            'block_formats' => $blockformats,
            'skin' => 'outside',
            'plugins' => 'code table lists media image link ' . $paste_plugin,
            'convert_urls' => false,
            'paste_retain_style_properties' => 'background-color',
            'branding' => false,
            'powerpaste_word_import' => 'merge',
            'powerpaste_googledocs_import' => 'merge',
            'powerpaste_html_import' => 'merge'
        );

        return $config;
    }

    // }}}
    // {{{ protected function getConfigButtons()

    protected function getConfigButtons()
    {
        return array(
            'bold',
            'italic',
            '|',
            'formatselect',
            '|',
            'removeformat',
            '|',
            'undo',
            'redo',
            '|',
            'outdent',
            'indent',
            '|',
            'bullist',
            'numlist',
            '|',
            'link',
            'image',
            'backcolor',
            'code'
        );
    }

    // }}}
    // {{{ protected function displayColorMap()

    protected function displayColorMap()
    {
        if (self::$color_map !== null) {
            echo "\tcolor_map: [\n";
            foreach (self::$color_map as $elem) {
                echo "\t\t" . SwatString::quoteJavaScriptString($elem) . ",\n";
            }
            echo "\t],\n";
        }
    }

    // }}}
    // {{{ protected function getInlineJavaScript()

    protected function getInlineJavaScript()
    {
        $base_href = 'editor_base_' . $this->id;
        ob_start();

        if ($this->base_href === null) {
            echo "var {$base_href} = " .
                "document.getElementsByTagName('base');\n";

            echo "if ({$base_href}.length) {\n";
            echo "\t{$base_href} = {$base_href}[0];\n";
            echo "\t{$base_href} = {$base_href}.href;\n";
            echo "} else {\n";
            echo "\t{$base_href} = location.href.split('#', 2)[0];\n";
            echo "\t{$base_href} = {$base_href}.split('?', 2)[0];\n";
            echo "\t{$base_href} = {$base_href}.substring(\n";
            echo "\t\t0, {$base_href}.lastIndexOf('/') + 1);\n";
            echo "}\n";
        } else {
            echo "var {$base_href} = " .
                SwatString::quoteJavaScriptString($this->base_href) .
                ";\n";
        }

        echo "tinyMCE.init({\n";

        $lines = array();
        foreach ($this->getConfig() as $name => $value) {
            if (is_string($value)) {
                $value = SwatString::quoteJavaScriptString($value);
            } elseif (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $lines[] = "\t" . $name . ": " . $value;
        }

        $lines[] = "\tdocument_base_url: {$base_href},\n";

        echo implode(",\n", $lines);

        $this->displayColorMap();

        // Post process the pasted nodes to remove extra styling while preserving
        // highlighted text. Also removes extra br tags
        echo "\tpaste_postprocess: function(pluginApi, data) {
				const toRemove = [];
				function execOnChildren(elem, fn) {
					fn(elem);
					for (let i = 0; i < elem.children.length; i++) {
						execOnChildren(elem.children[i], fn);
					}
				}
				function removeStyle(elem) {
					if (!elem || !elem.hasAttribute('style')) return;
					const match = elem.getAttribute('style').match(/background(-color)?:[^\"]*;/g);
					if (match) {
						elem.setAttribute('style', match[0]);
					} else {
						elem.removeAttribute('style');
					}
				}
				function removeNestedP(elem) {
					if (!elem || !elem.children[0]) return;
					if (elem.nodeName === 'LI' && elem.children[0].nodeName === 'P') {
						const p = elem.removeChild(elem.children[0]);
						const children = [];
						for (let i = 0; i < p.children.length; i++) {
							children.push(p.children[i]);
						}
						for (let i = 0; i < elem.children.length; i++) {
							children.push(elem.children[i]);
						}
						elem.replaceChildren(...children);
					}
				}
				function clean(elem) {
					removeStyle(elem);
					removeNestedP(elem);
				}
				for (let i = 0; i < data.node.children.length; i++) {
					const child = data.node.children[i];
					if (child.nodeName === 'BR') {
						toRemove.push(child);
					} else {
						execOnChildren(child, clean);
					}
				}
				toRemove.forEach(r => data.node.removeChild(r));
			},\n" .
            "\tmenubar: false,\n" .
            "\tformats: {\n" .
            "\t\tremoveformat : [\n" .
            "\t\t\t{\n" .
            "\t\t\t\tselector     : 'b,strong,em,i,font,u,strike',\n" .
            "\t\t\t\tremove       : 'all',\n" .
            "\t\t\t\tsplit        : true,\n" .
            "\t\t\t\texpand       : false,\n" .
            "\t\t\t\tblock_expand : true,\n" .
            "\t\t\t\tdeep         : true\n" .
            "\t\t\t},\n" .
            "\t\t\t{\n" .
            "\t\t\t\tselector     : 'span',\n" .
            "\t\t\t\tattributes   : [\n" .
            "\t\t\t\t\t'style',\n" .
            "\t\t\t\t\t'class',\n" .
            "\t\t\t\t\t'align',\n" .
            "\t\t\t\t\t'color',\n" .
            "\t\t\t\t\t'background'\n" .
            "\t\t\t\t],\n" .
            "\t\t\t\tremove       : 'empty',\n" .
            "\t\t\t\tsplit        : true,\n" .
            "\t\t\t\texpand       : false,\n" .
            "\t\t\t\tdeep         : true\n" .
            "\t\t\t},\n" .
            "\t\t\t{\n" .
            "\t\t\t\tselector     : '*',\n" .
            "\t\t\t\tattributes   : [\n" .
            "\t\t\t\t\t'style',\n" .
            "\t\t\t\t\t'class',\n" .
            "\t\t\t\t\t'align',\n" .
            "\t\t\t\t\t'color',\n" .
            "\t\t\t\t\t'background'\n" .
            "\t\t\t\t],\n" .
            "\t\t\t\tsplit        : false,\n" .
            "\t\t\t\texpand       : false,\n" .
            "\t\t\t\tdeep         : true\n" .
            "\t\t\t}\n" .
            "\t\t]\n" .
            "\t}";

        echo "\n});";

        return ob_get_clean();
    }

    // }}}
    // {{{ protected function getCSSClassNames()

    /**
     * Gets the array of CSS classes that are applied to this textarea
     *
     * @return array the array of CSS classes that are applied to this textarea.
     */
    protected function getCSSClassNames()
    {
        $classes = array('swat-textarea-editor');
        $classes = array_merge($classes, parent::getCSSClassNames());
        return $classes;
    }

    // }}}
}
