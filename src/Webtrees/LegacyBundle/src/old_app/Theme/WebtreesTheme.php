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
use Fgt\Application;

/**
 * Class WebtreesTheme - The webtrees (default) theme.
 */
class WebtreesTheme extends BaseTheme
{
    // We can't load these from a CDN, as these have been patched.
    const WT_JQUERY_COLORBOX_URL  = 'assets/js-1.7.0/jquery.colorbox-1.5.14.js';
    const WT_JQUERY_WHEELZOOM_URL = 'assets/js-1.7.0/jquery.wheelzoom-2.0.0.js';
    const WT_CKEDITOR_BASE_URL    = 'packages/ckeditor-4.4.7-custom/';

// Location of our own scripts
    const WT_ADMIN_JS_URL        = 'assets/js-1.7.0/admin.js';
    const WT_AUTOCOMPLETE_JS_URL = 'assets/js-1.7.0/autocomplete.js';
    const WT_WEBTREES_JS_URL     = 'assets/js-1.7.0/webtrees.js';


    /** {@inheritdoc} */
    public function assetUrl()
    {
        return 'themes/webtrees/css-1.7.0/';
    }

    /** {@inheritdoc} */
    protected function favicon()
    {
        return '<link rel="icon" href="' . $this->assetUrl() . 'favicon.png" type="image/png">';
    }

    /** {@inheritdoc} */
    protected function flashMessageContainer(\stdClass $message)
    {
        // This theme uses jQuery markup.
        return '<p class="ui-state-highlight">' . $message->text . '</p>';
    }

    /** {@inheritdoc} */
    protected function formQuickSearchFields()
    {
        return
            '<input type="search" name="query" size="25" placeholder="' . I18N::translate('Search') . '">' .
            '<input type="image" class="image" src="' . Application::i()->getTheme()
                                                             ->parameter('image-search') . '" alt="' . I18N::translate('Search') . '" title="' . I18N::translate('Search') . '">';
    }

    /** {@inheritdoc} */
    public function hookFooterExtraJavascript()
    {
        return
            '<script src="' . WT_STATIC_URL . WebtreesTheme::WT_JQUERY_COLORBOX_URL . '"></script>' .
            '<script src="' . WT_STATIC_URL . WebtreesTheme::WT_JQUERY_WHEELZOOM_URL . '"></script>' .
            '<script>' .
            'activate_colorbox();' .
            'jQuery.extend(jQuery.colorbox.settings, {' .
            ' width:"85%",' .
            ' height:"85%",' .
            ' transition:"none",' .
            ' slideshowStart:"' . I18N::translate('Play') . '",' .
            ' slideshowStop:"' . I18N::translate('Stop') . '",' .
            ' title: function() { return jQuery(this).data("title"); }' .
            '});' .
            '</script>';
    }

    /** {@inheritdoc} */
    public function parameter($parameter_name)
    {
        $parameters = array(
            'chart-background-f'             => 'e9daf1',
            'chart-background-m'             => 'b1cff0',
            'distribution-chart-high-values' => '84beff',
            'distribution-chart-low-values'  => 'c3dfff',
            'image-search'                   => $this->assetUrl() . 'images/search.png',
        );

        if (array_key_exists($parameter_name, $parameters)) {
            return $parameters[$parameter_name];
        } else {
            return parent::parameter($parameter_name);
        }
    }

    /** {@inheritdoc} */
    protected function stylesheets()
    {
        return array(
            'themes/webtrees/jquery-ui-1.11.2/jquery-ui.css',
            $this->assetUrl() . 'style.css',
        );
    }

    /** {@inheritdoc} */
    public function themeId()
    {
        return 'webtrees';
    }

    /** {@inheritdoc} */
    public function themeName()
    {
        return I18N::translate('webtrees');
    }
}
