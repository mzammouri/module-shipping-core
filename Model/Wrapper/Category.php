<?php
/**
 * Copyright © 2016 Owebia. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Owebia\ShippingCore\Model\Wrapper;

class Category extends SourceWrapper
{

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRespository;

    /**
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRespository
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @param \Owebia\ShippingCore\Helper\Registry $registry
     * @param mixed $data
     */
    public function __construct(
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRespository,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Quote\Model\Quote\Address\RateRequest $request,
        \Owebia\ShippingCore\Helper\Registry $registry,
        $data = null
    ) {
        parent::__construct($objectManager, $backendAuthSession, $request, $registry, $data);
        $this->categoryRespository = $categoryRespository;
    }

    /**
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     */
    protected function loadSource()
    {
        if ($this->data instanceof \Magento\Catalog\Api\Data\CategoryInterface) {
            return $this->data;
        }
        return $this->categoryRespository
            ->get($this->data['id']);
    }

    /**
     * Load source model
     * 
     * @return \Owebia\ShippingCore\Model\Wrapper\Category
     */
    public function load()
    {
        $this->source = $this->categoryRespository
            ->get($this->entity_id);
        $this->cache->setData([]);
        return $this;
    }
}
