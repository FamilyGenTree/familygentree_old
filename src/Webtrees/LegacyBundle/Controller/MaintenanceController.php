<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace Webtrees\LegacyBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceController extends AbstractController
{

    public function siteOfflinePhpAction()
    {
        $response = $this->render(
            'WebtreesLegacyThemeBundle:Maintenance:offline.html.twig',
            array(
                'title'       => 'This website is temporarily unavailable',
                'offline_txt' => null
            )
        );
        $response->setStatusCode(Response::HTTP_SERVICE_UNAVAILABLE);

        return $response;
    }

    public function siteUnavailablePhpAction()
    {
        $response = $this->render(
            'WebtreesLegacyThemeBundle:Maintenance:unavailable.html.twig',
            array(
                'title'       => 'This website is temporarily unavailable',
                'offline_txt' => null
            )
        );
        $response->setStatusCode(Response::HTTP_SERVICE_UNAVAILABLE);

        return $response;
    }
}