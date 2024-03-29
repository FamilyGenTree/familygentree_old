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
 * Class gedcom_block_WT_Module
 */
class gedcom_block_WT_Module extends Module implements ModuleBlockInterface
{
    /** {@inheritdoc} */
    public function getTitle()
    {
        return /* I18N: Name of a module */
            I18N::translate('Home page');
    }

    /** {@inheritdoc} */
    public function getDescription()
    {
        return /* I18N: Description of the “Home page” module */
            I18N::translate('A greeting message for site visitors.');
    }

    /** {@inheritdoc} */
    public function getBlock($block_id, $template = true, $cfg = null)
    {
        $controller = Application::i()->getActiveController();

        $indi_xref = $controller->getSignificantIndividual()
                                ->getXref();
        $id        = $this->getName() . $block_id;
        $class     = $this->getName() . '_block';
        $title     = '<span dir="auto">' . WT_TREE_TITLE . '</span>';
        $content   = '<table><tr>';
        $content .= '<td><a href="pedigree.php?rootid=' . $indi_xref . '&amp;ged=' . WT_GEDURL . '"><i class="icon-pedigree"></i><br>' . I18N::translate('Default chart') . '</a></td>';
        $content .= '<td><a href="individual.php?pid=' . $indi_xref . '&amp;ged=' . WT_GEDURL . '"><i class="icon-indis"></i><br>' . I18N::translate('Default individual') . '</a></td>';
        if (Site::getPreference('USE_REGISTRATION_MODULE') && !Auth::check()) {
            $content .= '<td><a href="' . WT_LOGIN_URL . '?action=register"><i class="icon-user_add"></i><br>' . I18N::translate('Request new user account') . '</a></td>';
        }
        $content .= "</tr>";
        $content .= "</table>";

        if ($template) {
            return Application::i()->getTheme()
                        ->formatBlock($id, $title, $class, $content);
        } else {
            return $content;
        }
    }

    /** {@inheritdoc} */
    public function loadAjax()
    {
        return false;
    }

    /** {@inheritdoc} */
    public function isUserBlock()
    {
        return false;
    }

    /** {@inheritdoc} */
    public function isGedcomBlock()
    {
        return true;
    }

    /** {@inheritdoc} */
    public function configureBlock($block_id)
    {
    }
}
