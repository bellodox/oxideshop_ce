<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Internal\Twig\Extensions;

use OxidEsales\EshopCommunity\Internal\Twig\Extensions\PhpFunctionsExtension;

/**
 * Class PhpFunctionsExtensionTest
 */
class PhpFunctionsExtensionTest extends AbstractExtensionTest
{

    /** @var PhpFunctionsExtension */
    protected $extension;

    protected $functions = ['count', 'empty', 'isset'];

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->extension = new PhpFunctionsExtension();
    }

    /**
     * @return array
     */
    public function dummyTemplateProvider(): array
    {
        return [
            ["{{ count({0:0, 1:1, 2:2}) }}", 3],
            ["{{ empty({0:0, 1:1}) }}", false],
            ["{{ empty({}) }}", true],
            ["{{ isset(foo) }}", false],
            ["{% set foo = 'bar' %} {{ isset(foo) }}", true],
        ];
    }

    /**
     * @param string $template
     * @param string $expected
     *
     * @dataProvider dummyTemplateProvider
     */
    public function testIfPhpFunctionsAreCallable(string $template, string $expected): void
    {
        $this->assertEquals($expected, $this->getTemplate($template)->render([]));
    }
}
