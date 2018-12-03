<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Twig\Escaper;

use Twig\Environment;

/**
 * Class UrlPathInfoEscaper
 *
 * @author Tomasz Kowalewski (t.kowalewski@createit.pl)
 */
class UrlPathInfoEscaper implements EscaperInterface
{

    /**
     * @return string
     */
    public function getStrategy(): string
    {
        return 'urlpathinfo';
    }

    /**
     * @param Environment $environment
     * @param string      $string
     * @param string      $charset
     *
     * @return string
     */
    public function escape(Environment $environment, $string, $charset): string
    {
        return str_replace('%2F', '/', rawurlencode($string));
    }
}
