<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Templating;


use OxidEsales\Eshop\Core\Exception\SystemComponentException;

class TemplateLoader implements TemplateLoaderInterface
{
    /**
     * @var FileLocatorInterface
     */
    private $fileLocator;

    /**
     * @var TemplateNameResolverInterface
     */
    private $templateNameResolver;

    public function __construct(FileLocatorInterface $fileLocator, TemplateNameResolverInterface $templateNameResolver)
    {
        $this->fileLocator = $fileLocator;
        $this->templateNameResolver = $templateNameResolver;
    }

    public function exists($name)
    {
        try {
            $this->findTemplate($name);
        } catch (SystemComponentException $e) {
            return false;
        }
        return true;
    }

    public function getContext($name)
    {
        $path = $this->findTemplate($name);

        return file_get_contents($path);
    }

    private function findTemplate($name)
    {
        $templateName = $this->templateNameResolver->resolve($name, 'tpl');
        $file = $this->fileLocator->locate($templateName);

        if (false === $file || null === $file || '' === $file) {
            $ex = oxNew(SystemComponentException::class);
            $ex->setMessage('EXCEPTION_SYSTEMCOMPONENT_TEMPLATENOTFOUND' . ' ' . $templateName);
            $ex->setComponent($templateName);

            throw $ex;
        }
        return $file;
    }

}