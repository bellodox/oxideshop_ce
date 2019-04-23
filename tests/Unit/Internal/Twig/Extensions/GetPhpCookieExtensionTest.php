<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Internal\Twig\Extensions;

use OxidEsales\EshopCommunity\Internal\Twig\Extensions\GetRequestVariablesExtension;

class GetRequestVariablesExtensionTest extends AbstractExtensionTest
{

    /** @var GetRequestVariablesExtension */
    protected $extension;

    public function setUp()
    {
        $this->extension = new GetRequestVariablesExtension();
        $_COOKIE['foo'] = 'bar';
        $_GET['foo'] = 'bar';
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($_COOKIE['foo']);
        unset($_GET['foo']);
    }

    /**
     * @return array
     */
    public function dummyTemplateProvider(): array
    {
        return [
            ['{{ get_php_cookie("foo") }}', 'bar'],
            ['{{ get_php_cookie("bar") }}', ''],
            ['{{ get_php_get("foo") }}', 'bar'],
            ['{{ get_php_get("bar") }}', ''],
        ];
    }

    /**
     * @param string $template
     * @param string $expected
     *
     * @dataProvider dummyTemplateProvider
     */
    public function testIfPhpFunctionsAreCallable(string $template, string $expected)
    {
        $this->assertEquals($expected, $this->getTemplate($template)->render([]));
    }
}
