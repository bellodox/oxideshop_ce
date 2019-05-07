<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Templating;

use OxidEsales\Eshop\Core\Config;

class AdminFileLocator implements FileLocatorInterface
{
    /**
     * @var Config
     */
    private $context;

    public function __construct(Config $context)
    {
        $this->context = $context;
    }

    /**
     * Returns a full path for a given file name.
     *
     * @param string $name The file name to locate
     *
     * @return string The full path to the file
     */
    public function locate($name)
    {
        return $this->context->getTemplatePath($name, true);
    }
}