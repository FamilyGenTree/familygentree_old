<?php
namespace Webtrees\LegacyBundle\Legacy;

use Fgt\Application;
use Fgt\UrlConstants;
use Knp\Menu\FactoryInterface;

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

/**
 * Class individual_ext_report_WT_Module
 */
class individual_ext_report_WT_Module extends Module implements ModuleReportInterface
{
    /** {@inheritdoc} */
    public function getTitle()
    {
        // This text also appears in the .XML file - update both together
        return /* I18N: Name of a module/report */
            I18N::translate('Related families');
    }

    /** {@inheritdoc} */
    public function getDescription()
    {
        // This text also appears in the .XML file - update both together
        return /* I18N: Description of the “Related families” */
            I18N::translate('A report of the families that are closely related to an individual.');
    }

    /** {@inheritdoc} */
    public function defaultAccessLevel()
    {
        return WT_PRIV_USER;
    }

    /** {@inheritdoc} */
    public function getReportMenus(FactoryInterface $factory, array $options)
    {
        $controller = Application::i()->getActiveController();

        $menus   = array();
        $menu    = $factory->createItem(
            $this->getTitle(),
            [
                'uri'        => UrlConstants::url(UrlConstants::REPORTENGINE_PHP, 'ged=' . WT_GEDURL . '&amp;action=setup&amp;report=' . WT_MODULES_DIR . $this->getName() . '/report.xml&amp;pid=' . $controller->getSignificantIndividual()
                                                                                                                                                                                ->getXref()),
                'attributes' => [
                    'id' => 'menu-report-' . $this->getName()
                ]
            ]
        );
        $menus[] = $menu;

        return $menus;
    }
}
