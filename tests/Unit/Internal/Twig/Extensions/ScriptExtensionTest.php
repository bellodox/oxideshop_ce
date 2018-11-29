<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Internal\Twig\Extensions;

use OxidEsales\EshopCommunity\Internal\Adapter\TemplateLogic\ScriptLogic;
use OxidEsales\EshopCommunity\Internal\Twig\Extensions\ScriptExtension;

/**
 * Class ScriptExtensionTest
 *
 * @author Tomasz Kowalewski (t.kowalewski@createit.pl)
 */
class ScriptExtensionTest extends AbstractExtensionTest
{

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->extension = new ScriptExtension(new ScriptLogic());
    }

    /**
     * @param $template
     * @param $expected
     *
     * @covers ScriptExtension::script
     * @dataProvider getScriptTests
     */
    public function testScript($template, $expected)
    {
        $this->assertEquals($expected, $this->getTemplate($template)->render([]));
    }

    /**
     * @return array
     */
    public function getScriptTests()
    {
        return [
            // Empty buffer
            [
                "{{ script() }}",
                ""
            ],
            // One script
            [
                "{{ script({ add: 'alert();' }) }}" .
                "{{ script() }}",
                "<script type='text/javascript'>alert();</script>"
            ],
            // Two scripts
            [
                "{{ script({ add: 'alert(\"one\");' }) }}" .
                "{{ script({ add: 'alert(\"two\");' }) }}" .
                "{{ script() }}",
                "<script type='text/javascript'>alert(\"one\");\n" .
                "alert(\"two\");</script>"
            ],
            // Include
            [
                "{{ script({ include: 'http://someurl/src/js/libs/jquery.min.js' }) }}" .
                "{{ script() }}",
                "<script type=\"text/javascript\" src=\"http://someurl/src/js/libs/jquery.min.js\"></script>"
            ],
            // Two includes
            [
                "{{ script({ include: 'http://someurl/src/js/libs/jquery.min.js' }) }}" .
                "{{ script({ include: 'http://another/src/js/libs/jquery.min.js' }) }}" .
                "{{ script() }}",
                "<script type=\"text/javascript\" src=\"http://someurl/src/js/libs/jquery.min.js\"></script>\n" .
                "<script type=\"text/javascript\" src=\"http://another/src/js/libs/jquery.min.js\"></script>"
            ],
            // Two scripts, two includes
            [
                "{{ script({ add: 'alert(\"one\");' }) }}" .
                "{{ script({ include: 'http://someurl/src/js/libs/jquery.min.js' }) }}" .
                "{{ script({ add: 'alert(\"two\");' }) }}" .
                "{{ script({ include: 'http://another/src/js/libs/jquery.min.js' }) }}" .
                "{{ script() }}",
                "<script type=\"text/javascript\" src=\"http://someurl/src/js/libs/jquery.min.js\"></script>\n" .
                "<script type=\"text/javascript\" src=\"http://another/src/js/libs/jquery.min.js\"></script>" .
                "<script type='text/javascript'>alert(\"one\");\n" .
                "alert(\"two\");</script>"
            ],
            // Include widget
            [
                "{{ script({ include: 'http://someurl/src/js/libs/jquery.min.js' }) }}" .
                "{{ script({ widget: 'somewidget', inWidget: true }) }}",
                <<<HTML
<script type='text/javascript'>
    window.addEventListener('load', function() {
        WidgetsHandler.registerFile('http://someurl/src/js/libs/jquery.min.js', 'somewidget');
    }, false)
</script>
HTML
            ],
            // Add widget
            [
                "{{ script({ add: 'alert();' }) }}" .
                "{{ script({ widget: 'somewidget', inWidget: true }) }}",
                "<script type='text/javascript'>window.addEventListener('load', function() { WidgetsHandler.registerFunction('alert();', 'somewidget'); }, false )</script>"
            ]
        ];
    }
}