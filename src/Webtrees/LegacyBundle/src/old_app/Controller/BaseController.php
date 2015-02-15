<?php
namespace Webtrees\LegacyBundle\Legacy;

/**
 * webtrees: online genealogy
 * Copyright (C) 2015 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

use FamGeneTree\AppBundle\Context\Configuration\Domain\FgtConfig;
use Fgt\Application;

/**
 * Class BaseController - Base controller for all other controllers
 */
class BaseController implements \ArrayAccess
{
    // The controller accumulates Javascript (inline and external), and renders it in the footer
    const JS_PRIORITY_HIGH   = 0;
    const JS_PRIORITY_NORMAL = 1;
    const JS_PRIORITY_LOW    = 2;

    public static $activeController;
    protected     $page_header   = false;
    protected     $collectOutput = false;
    protected     $output        = []; // Have we printed a page header?
    /**
     * @var \Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine
     */
    protected $templateEngine;
    /**
     * @var string
     */
    protected $defaultTemplate;
    private   $inline_javascript   = array(
        self::JS_PRIORITY_HIGH   => array(),
        self::JS_PRIORITY_NORMAL => array(),
        self::JS_PRIORITY_LOW    => array(),
    );
    private   $external_javascript = array();

    /**
     * Startup activity
     *
     * @param \Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine $templateEngine
     * @param string                                           $template
     */
    public function __construct(
        \Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine $templateEngine,
        $template = 'WebtreesLegacyThemeBundle:Default:index.html.twig'
    ) {
        static::$activeController = $this;
        $this->templateEngine     = $templateEngine;
        $this->defaultTemplate    = $template;


    }

    /**
     * Shutdown activity
     */
    public function __destruct()
    {
        // If we printed a header, automatically print a footer
        if ($this->page_header) {
            $this->flushOutput();
            echo $this->pageFooter();
        }
    }

    public function flushOutput()
    {
        if ($this->collectOutput) {
            echo implode('', $this->output);
            $this->collectOutput = [];
        }
    }

    /**
     * Print the page footer, using the theme
     */
    protected function pageFooter()
    {
        $ret = $this->getJavascript();
        if (Database::i()->isDebugSql()) {
            $ret = Database::i()->getQueryLog() . $ret;
        }

        return $ret;
    }

    /**
     * We've collected up Javascript fragments while rendering the page.
     * Now display them in order.
     *
     * @return string
     */
    public function getJavascript()
    {
        $javascript1 = '';
        $javascript2 = '';
        $javascript3 = '';

        // Inline (high priority) javascript
        foreach ($this->inline_javascript[self::JS_PRIORITY_HIGH] as $script) {
            $javascript1 .= $script;
        }

        // External javascript
        foreach (array_keys($this->external_javascript) as $script_name) {
            $javascript2 .= '<script src="' . $script_name . '"></script>';
        }

        // Inline (lower priority) javascript
        if ($this->inline_javascript) {
            foreach ($this->inline_javascript as $priority => $scripts) {
                if ($priority !== self::JS_PRIORITY_HIGH) {
                    foreach ($scripts as $script) {
                        $javascript3 .= $script;
                    }
                }
            }
        }

        // We could, in theory, inject JS at any point in the page (not just the bottom) - prepare for next time
        $this->inline_javascript   = array(
            self::JS_PRIORITY_HIGH   => array(),
            self::JS_PRIORITY_NORMAL => array(),
            self::JS_PRIORITY_LOW    => array(),
        );
        $this->external_javascript = array();

        return '<script>' . $javascript1 . '</script>' . $javascript2 . '<script>' . $javascript3 . '</script>';
    }

    /**
     * Make a list of external Javascript, so we can render them in the footer
     *
     * @param string $script_name
     *
     * @return $this
     */
    public function addExternalJavascript($script_name)
    {
        $this->external_javascript[$script_name] = true;

        return $this;
    }

    /**
     * Make a list of inline Javascript, so we can render them in the footer
     * NOTE: there is no need to use "jQuery(document).ready(function(){...})", etc.
     * as this Javascript won’t be inserted until the very end of the page.
     *
     * @param string  $script
     * @param integer $priority
     *
     * @return $this
     */
    public function addInlineJavascript($script, $priority = self::JS_PRIORITY_NORMAL)
    {
        if (WT_DEBUG) {
            /* Show where the JS was added */
            $backtrace = debug_backtrace();
            $script    = '/* ' . $backtrace[0]['file'] . ':' . $backtrace[0]['line'] . ' */' . PHP_EOL . $script;
        }
        $tmp   = &$this->inline_javascript[$priority];
        $tmp[] = $script;

        return $this;
    }

    /**
     * Print the page header, using the theme
     *
     * @return $this
     */
    public function pageHeader()
    {
        // We've displayed the header - display the footer automatically
        $this->page_header = true;

        return $this;
    }

    public function render($templateName = null, array $arguments = array())
    {
        if ($templateName == null) {
            $templateName = $this->getDefaultTemplate();
        }
        $arguments    = array_merge(
            array(
                'html_markup'               => I18N::html_markup(),
                'head_contents'             => Application::i()->getTheme()->headContents($this),
                'hook_header_extra_content' => Application::i()
                                                          ->getTheme()
                                                          ->hookHeaderExtraContent(),
                'analytics'                 => Application::i()->getTheme()->analytics(),
                'inline_javascript'         => $this->getJavascript(),
                'javascript_at_end'         => null,
                'title'                     => Application::i()->getTree()
                    ? Application::i()->getTree()->getName()
                    : Application::i()->getConfig()->getValue(FgtConfig::SITE_NAME),
                'collected_output'          => implode('', $this->output),
                'ged_name'                  => 'ged_name'
            ),
            $arguments
        );
        $this->output = [];

        return $this->getTemplateEngine()->render($templateName, $arguments);
    }

    protected function getDefaultTemplate()
    {
        return $this->defaultTemplate;
    }

    /**
     * @return \Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine
     */
    public function getTemplateEngine()
    {
        return $this->templateEngine;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->output[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->output[] = $value;
        } else {
            $this->output[$offset] = $value;
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->output[$offset]);
    }

    public function flush()
    {
        echo implode('', $this->output);
    }
}
