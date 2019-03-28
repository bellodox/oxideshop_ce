<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Integration\Core\Module;

use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Core\Module\ModuleList;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Application\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Module\Install\DataObject\OxidEshopPackage;
use OxidEsales\EshopCommunity\Internal\Module\Install\Service\ModuleInstallerInterface;
use OxidEsales\EshopCommunity\Internal\Module\Setup\Bridge\ModuleActivationBridgeInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 */
class ModuleListTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setUp()
    {
        $this->container = ContainerFactory::getInstance()->getContainer();

        $this->container
            ->get('oxid_esales.module.install.service.lanched_shop_project_configuration_generator')
            ->generate();

        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->container
            ->get('oxid_esales.module.install.service.lanched_shop_project_configuration_generator')
            ->generate();

        Registry::getConfig()->saveShopConfVar('aarr', 'activeModules', []);
    }

    public function testDisabledModules()
    {
        $this->installModule('with_metadata_v21');
        $this->installModule('with_class_extensions');

        $this->assertSame(
            [
                'with_metadata_v21',
                'with_class_extensions',
            ],
            oxNew(ModuleList::class)->getDisabledModules()
        );
    }

    public function testDisabledModulesInfo()
    {
        $activeModuleId = 'with_metadata_v21';
        $this->installModule($activeModuleId);
        $this->activateModule($activeModuleId);

        $notActiveModuleId = 'with_class_extensions';
        $this->installModule($notActiveModuleId);

        $this->assertSame(
            ['with_class_extensions' => 'with_class_extensions'],
            oxNew(ModuleList::class)->getDisabledModuleInfo()
        );
    }

    public function testDisabledModulesInfoWithNoModules()
    {
        $this->assertSame(
            [],
            oxNew(ModuleList::class)->getDisabledModuleInfo()
        );
    }

    public function testGetDisabledModuleClasses()
    {
        $notActiveModuleId = 'with_class_extensions';
        $this->installModule($notActiveModuleId);

        $this->assertSame(
            [
                Article::class => 'with_class_extensions/ModuleArticle',
            ],
            oxNew(ModuleList::class)->getDisabledModuleClasses()
        );
    }

    public function testCleanup()
    {
        $activeModuleId = 'with_metadata_v21';
        $this->installModule($activeModuleId);
        $this->activateModule($activeModuleId);

        $moduleList = $this
            ->getMockBuilder(ModuleList::class)
            ->setMethods(['getDeletedExtensions'])
            ->getMock();

        $moduleList
            ->method('getDeletedExtensions')
            ->willReturn(
                [
                    'with_metadata_v21' => 'someExtension',
                ]
            );

        $moduleList->cleanup();

        $moduleActivationBridge = $this->container->get(ModuleActivationBridgeInterface::class);

        $this->assertFalse(
            $moduleActivationBridge->isActive('with_metadata_v21', 1)
        );
    }

    private function installModule(string $id)
    {
        $this->container
            ->get(ModuleInstallerInterface::class)
            ->install(
                new OxidEshopPackage(
                    $id,
                    __DIR__ . '/Fixtures/' . $id,
                    []
                )
            );
    }

    private function activateModule(string $id)
    {
        $this->container->get(ModuleActivationBridgeInterface::class)->activate($id, 1);
    }
}
