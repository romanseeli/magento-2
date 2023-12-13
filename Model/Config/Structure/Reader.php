<?php
/**
 WeArePlanet Magento 2
 *
 * This Magento 2 extension enables to process payments with WeArePlanet (https://www.weareplanet.com).
 *
 * @package WeArePlanet_Payment
 * @author Planet Merchant Services Ltd (https://www.weareplanet.com)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)

 */
namespace WeArePlanet\Payment\Model\Config\Structure;

use Magento\Config\Model\Config\SchemaLocator;
use Magento\Config\Model\Config\Structure\Converter;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;
use WeArePlanet\Payment\Model\Config\Dom;

/**
 * Reader to retrieve system configuration from system.xml files.
 * Merges configuration and caches it.
 */
class Reader extends \Magento\Config\Model\Config\Structure\Reader
{

    /**
     *
     * @param FileResolverInterface $fileResolver
     * @param Converter $converter
     * @param SchemaLocator $schemaLocator
     * @param ValidationStateInterface $validationState
     * @param CompilerInterface $compiler
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(FileResolverInterface $fileResolver, Converter $converter, SchemaLocator $schemaLocator,
        ValidationStateInterface $validationState, CompilerInterface $compiler, $fileName = 'system.xml', $idAttributes = [],
        $domDocumentClass = \Magento\Framework\Config\Dom::class, $defaultScope = 'global')
    {
        parent::__construct($fileResolver, $converter, $schemaLocator, $validationState, $compiler, $fileName,
            $idAttributes, $domDocumentClass, $defaultScope);
    }

    /**
     *
     * @return Dom
     */
    public function createConfigMerger()
    {
        return $this->_createConfigMerger(Dom::class, Dom::SYSTEM_INITIAL_CONTENT);
    }

    /**
     *
     * @param string $content
     * @return string
     */
    public function processDocument($content)
    {
        return $this->processingDocument($content);
    }
}