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
use Fgt\UrlConstants;

/**
 * Class AdministrationTheme - Theme for the control panel.
 */
class AdministrationTheme extends BaseTheme
{
    /** {@inheritdoc} */
    protected function stylesheets()
    {
        $stylesheets = array(
            WT_FONT_AWESOME_CSS_URL,
            WT_BOOTSTRAP_CSS_URL,
            WT_DATATABLES_BOOTSTRAP_CSS_URL,
            WT_BOOTSTRAP_DATETIMEPICKER_CSS_URL,
            $this->assetUrl() . 'style.css',
        );

        if (I18N::scriptDirection(I18N::languageScript(WT_LOCALE)) === 'rtl') {
            $stylesheets[] = WT_BOOTSTRAP_RTL_CSS_URL;
        }

        return $stylesheets;
    }

    /** {@inheritdoc} */
    public function assetUrl()
    {
        return 'themes/_administration/css-1.7.0/';
    }

    /** {@inheritdoc} */
    protected function footerContent()
    {
        return '';
    }

    /** {@inheritdoc} */
    protected function headerContent()
    {
        return
            $this->accessibilityLinks() .
            $this->secondaryMenuContainer($this->secondaryMenu());
    }

    /** {@inheritdoc} */
    public function hookFooterExtraJavascript()
    {
        return
            '<script src="' . WT_BOOTSTRAP_JS_URL . '"></script>';
    }

    /**
     * @return Menu
     */
    protected function menuAdminSite()
    {
        return new Menu(/* I18N: Menu entry*/
            I18N::translate('Website'), '#', '', '', array(
            new Menu(/* I18N: Menu entry */
                I18N::translate('Website preferences'), UrlConstants::url(UrlConstants::ADMIN_SITE_CONFIG_PHP,array('action'=>'site'))/* 'admin_site_config.php?action=site'*/),
            new Menu(/* I18N: Menu entry */
                I18N::translate('Sending email'), UrlConstants::url(UrlConstants::ADMIN_SITE_CONFIG_PHP,array('action'=>'email'))/*'admin_site_config.php?action=email'*/),

            new Menu(/* I18N: Menu entry */
                I18N::translate('Login and registration'), UrlConstants::url(UrlConstants::ADMIN_SITE_CONFIG_PHP,array('action'=>'login'))/*'admin_site_config.php?action=login'*/),
            new Menu(/* I18N: Menu entry */
                I18N::translate('Tracking and analytics'), UrlConstants::url(UrlConstants::ADMIN_SITE_CONFIG_PHP,array('action'=>'tracking'))/*'admin_site_config.php?action=tracking'*/),
            new Menu(/* I18N: Menu entry */
                I18N::translate('Website logs'), UrlConstants::url(UrlConstants::ADMIN_SITE_LOGS_PHP)),
            new Menu(/* I18N: Menu entry */
                I18N::translate('Website access rules'), UrlConstants::url(UrlConstants::ADMIN_SITE_ACCESS_PHP)),
            new Menu(/* I18N: Menu entry */
                I18N::translate('Clean up data folder'), UrlConstants::url(UrlConstants::ADMIN_SITE_CLEAN_PHP)),
            new Menu(/* I18N: Menu entry */
                I18N::translate('Server information'), UrlConstants::url(UrlConstants::ADMIN_SITE_INFO_PHP)),
            new Menu(/* I18N: Menu entry */
                I18N::translate('README documentation'), UrlConstants::url(UrlConstants::ADMIN_SITE_README_PHP)),
        ));
    }

    /**
     * @return Menu
     */
    protected function menuAdminTrees()
    {
        return new Menu(/* I18N: Menu entry */
            I18N::translate('Family trees'), '#', '', '', array_filter(array(
                                                                           $this->menuAdminTreesManage(),
                                                                           $this->menuAdminTreesSetDefault(),
                                                                           $this->menuAdminTreesMerge(),
                                                                       )));
    }

    /**
     * @return Menu
     */
    protected function menuAdminTreesManage()
    {
        return new Menu(/* I18N: Menu entry */
            I18N::translate('Manage family trees'), UrlConstants::url(UrlConstants::ADMIN_TREES_MANAGE_PHP));
    }

    /**
     * @return Menu|null
     */
    protected function menuAdminTreesMerge()
    {
        if (count(Tree::getAll()) > 1) {
            return new Menu(/* I18N: Menu entry */
                I18N::translate('Merge family trees'), UrlConstants::url(UrlConstants::ADMIN_TREES_MERGE_PHP));
        } else {
            return null;
        }
    }

    /**
     * @return Menu|null
     */
    protected function menuAdminTreesSetDefault()
    {
        if (count(Tree::getAll()) > 1) {
            return new Menu(/* I18N: Menu entry */
                I18N::translate('Set the default blocks for new family trees'), UrlConstants::url(UrlConstants::INDEX_EDIT_PHP,array('gedcom_id'=>'-1'))/*'index_edit.php?gedcom_id=-1'*/);
        } else {
            return null;
        }
    }

    /**
     * @return Menu
     */
    protected function menuAdminUsers()
    {
        return new Menu(/* I18N: Menu entry */
            I18N::translate('Users'), '#', '', '', array(
            new Menu(/* I18N: Menu entry */
                I18N::translate('User administration'), UrlConstants::url(UrlConstants::ADMIN_USERS_PHP)),
            new Menu(/* I18N: Menu entry */
                I18N::translate('Add a new user'), 'admin_users.php?action=edit'),
            new Menu(/* I18N: Menu entry */
                I18N::translate('Send broadcast messages'), UrlConstants::url(UrlConstants::ADMIN_USERS_BULK_PHP)),
            new Menu(/* I18N: Menu entry */
                I18N::translate('Delete inactive users'), 'admin_users.php?action=cleanup'),
            new Menu(/* I18N: Menu entry */
                I18N::translate('Set the default blocks for new users'), 'index_edit.php?user_id=-1'),
        ));
    }

    /**
     * @return Menu
     */
    protected function menuAdminMedia()
    {
        return new Menu(/* I18N: Menu entry */
            I18N::translate('Media'), '#', '', '', array(
            new Menu(/* I18N: Menu entry */
                I18N::translate('Manage media'), UrlConstants::url(UrlConstants::ADMIN_MEDIA_PHP)),
            new Menu(/* I18N: Menu entry */
                I18N::translate('Upload media files'), UrlConstants::url(UrlConstants::ADMIN_MEDIA_UPLOAD_PHP)),
        ));
    }

    /**
     * @return Menu
     */
    protected function menuAdminModules()
    {
        return new Menu(/* I18N: Menu entry */
            I18N::translate('Modules'), '#', '', '', array(
            new Menu(/* I18N: Menu entry */
                I18N::translate('Module administration'), UrlConstants::url(UrlConstants::ADMIN_MODULES_PHP)),
            new Menu(/* I18N: Menu entry */
                I18N::translate('Menus'), UrlConstants::url(UrlConstants::ADMIN_MODULE_MENUS_PHP)),
            new Menu(/* I18N: Menu entry */
                I18N::translate('Tabs'), UrlConstants::url(UrlConstants::ADMIN_MODULE_TABS_PHP)),
            new Menu(/* I18N: Menu entry */
                I18N::translate('Blocks'), UrlConstants::url(UrlConstants::ADMIN_MODULE_BLOCKS_PHP)),
            new Menu(/* I18N: Menu entry */
                I18N::translate('Sidebars'), UrlConstants::url(UrlConstants::ADMIN_MODULE_SIDEBAR_PHP)),
            new Menu(/* I18N: Menu entry */
                I18N::translate('Reports'), UrlConstants::url(UrlConstants::ADMIN_MODULE_REPORTS_PHP)),
        ));
    }

    /** {@inheritdoc} */
    protected function primaryMenu()
    {
        if (Auth::isAdmin()) {
            return array(
                $this->menuAdminSite(),
                $this->menuAdminTrees(),
                $this->menuAdminUsers(),
                $this->menuAdminMedia(),
                $this->menuAdminModules(),
            );
        } else {
            return array(
                $this->menuAdminTrees(),
            );
        }
    }

    /** {@inheritdoc} */
    protected function primaryMenuContainer(array $menus)
    {
        $html = '';
        foreach ($menus as $menu) {
            $html .= $menu->bootstrap();
        }

        return
            '<nav class="navbar navbar-default">' .
            '<div class="navbar-header">' .
            '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#primary-navbar-collapse">' .
            '<span class="sr-only">Toggle navigation</span>' .
            '<span class="icon-bar"></span>' .
            '<span class="icon-bar"></span>' .
            '<span class="icon-bar"></span>' .
            '</button>' .
            '<a class="navbar-brand" href="admin.php">' . I18N::translate('Control panel') . '</a>' .
            '</div>' .
            '<div class="collapse navbar-collapse" id="primary-navbar-collapse">' .
            '<ul class="nav navbar-nav">' .
            $html .
            '</ul>' .
            '</div>' .
            '</nav>';
    }

    /** {@inheritdoc} */
    protected function secondaryMenu()
    {
        return array_filter(array(
                                $this->menuPendingChanges(),
                                $this->menuMyPage(),
                                $this->menuLanguages(),
                                $this->menuLogout(),
                            ));
    }

    /** {@inheritdoc} */
    protected function secondaryMenuContainer(array $menus)
    {
        $html = '';
        foreach ($menus as $menu) {
            $html .= $menu->bootstrap();
        }

        return '<div class="clearfix"><ul class="nav nav-pills small pull-right flip" role="menu">' . $html . '</ul></div>';
    }

    /** {@inheritdoc} */
    public function themeId()
    {
        return '_administration';
    }

    /** {@inheritdoc} */
    public function themeName()
    {
        return 'administration';
    }
}
