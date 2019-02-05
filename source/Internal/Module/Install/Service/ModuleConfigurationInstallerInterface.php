<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Module\Install\Service;

/**
 * @internal
 */
interface ModuleConfigurationInstallerInterface
{
    /**
     * @param string $moduleFullPath
     */
    public function install(string $moduleFullPath);

    /**
     * @param string $packagePath
     * @return bool
     */
    public function isInstalled(string $packagePath): bool;
}
