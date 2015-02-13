<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\AppBundle\Context\Configuration\Domain;


interface ConfigKeys
{

    const SYSTEM_NAME               = 'system.name';
    const SYSTEM_VERSION            = 'system.version';
    const PROJECT_HOMEPAGE_URL      = 'project.homepage.url';
    const PROJECT_WIKI_HOMEPAGE_URL = 'project.wiki.homepage.url';
    const SYSTEM_PATH_DATA    = 'system.path.data';
    const SYSTEM_PATH_CONFIG  = 'system.path.config';
    const SYSTEM_CACHE_DIR    = 'system.cache.dir';
    const SYSTEM_CACHE        = 'system.cache';
    const SYSTEM_MODULES_PATH = 'system.modules.path';
}