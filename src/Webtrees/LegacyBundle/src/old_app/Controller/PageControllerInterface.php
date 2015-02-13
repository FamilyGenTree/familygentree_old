<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */
namespace Webtrees\LegacyBundle\Legacy;


/**
 * Class PageController Controller for full-page, themed HTML responses
 */
interface PageControllerInterface
{
    /**
     * Make a list of external Javascript, so we can render them in the footer
     *
     * @param string $script_name
     *
     * @return $this
     */
    public function addExternalJavascript($script_name);

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
    public function addInlineJavascript($script, $priority = PageController::JS_PRIORITY_NORMAL);

    /**
     * We've collected up Javascript fragments while rendering the page.
     * Now display them in order.
     *
     * @return string
     */
    public function getJavascript();

    /**
     * What should this page show in the browser’s title bar?
     *
     * @param string $page_title
     *
     * @return $this
     */
    public function setPageTitle($page_title);

    /**
     * Some pages will want to display this as <h2> $page_title </h2>
     *
     * @return string
     */
    public function getPageTitle();

    /**
     * What is the preferred URL for this page?
     *
     * @param $canonical_url
     *
     * @return $this
     */
    public function setCanonicalUrl($canonical_url);

    /**
     * What is the preferred URL for this page?
     *
     * @return string
     */
    public function getCanonicalUrl();

    /**
     * Should robots index this page?
     *
     * @param string $meta_robots
     *
     * @return $this
     */
    public function setMetaRobots($meta_robots);

    /**
     * Should robots index this page?
     *
     * @return string
     */
    public function getMetaRobots();

    /**
     * Restrict access
     *
     * @param boolean $condition
     *
     * @return $this
     */
    public function restrictAccess($condition);

    /**
     * Print the page header, using the theme
     *
     * @param string $view 'simple' or ''
     *
     * @return $this
     */
    public function pageHeader($view = '');

    /**
     * Get significant information from this page, to allow other pages such as
     * charts and reports to initialise with the same records
     *
     * @return Individual
     */
    public function getSignificantIndividual();

    /**
     * Get significant information from this page, to allow other pages such as
     * charts and reports to initialise with the same records
     *
     * @return Family
     */
    public function getSignificantFamily();

    /**
     * Get significant information from this page, to allow other pages such as
     * charts and reports to initialise with the same records
     *
     * @return string
     */
    public function getSignificantSurname();
}