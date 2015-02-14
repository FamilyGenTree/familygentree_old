<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace Webtrees\LegacyBundle\Controller;


class MaintenanceController extends AbstractController
{

    public function siteOfflinePhpAction()
    {
        return $this->render(
            'WebtreesLegacyThemeBundle:Maintenance:offline.html.twig',
            array(
                'title' => 'webtrees',
                'offline_txt' => null
            )
        );
    }

    public function siteUnavailablePhpAction()
    {
    }
}