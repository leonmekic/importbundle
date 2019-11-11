<?php

namespace Factory\InstallBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class FactoryInstallBundle extends AbstractPimcoreBundle
{
    public function getJsPaths()
    {
        return [
            '/bundles/factoryinstall/js/pimcore/startup.js'
        ];
    }
}