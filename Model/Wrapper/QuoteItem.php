<?php

/**
 * Copyright © 2016 Owebia. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Owebia\ShippingCore\Model\Wrapper;

class QuoteItem extends SourceWrapper
{
    /**
     * @var array
     */
    protected $additionalAttributes = [ 'options' ];

    /**
     * {@inheritDoc}
     * @see \Owebia\ShippingCore\Model\Wrapper\AbstractWrapper::loadData()
     */
    protected function loadData($key)
    {
        switch ($key) {
            case 'options':
                $options = array();
                $helper = $this->objectManager->get('Magento\Catalog\Helper\Product\Configuration');
                $customOptions = $helper->getCustomOptions($this->getSource());
                if ($customOptions) {
                    foreach ($customOptions as $option) {
                        $options[$option['label']] = $option;
                    }
                }
                return $options;
        }
        return parent::loadData($key);
    }
}
