<?php
/**
 * Copyright © 2016 Owebia. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Owebia\ShippingCore\Model\Wrapper;

abstract class AbstractWrapper
{

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $cache = null;

    /**
     * @var mixed
     */
    protected $data = null;

    /**
     * @var array
     */
    protected $aliasMap = [];

    /**
     * @var array
     */
    protected $additionalAttributes = [];

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateRequest
     */
    protected $request;

    /**
     * @var \Owebia\ShippingCore\Helper\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @param \Owebia\ShippingCore\Helper\Registry $registry
     * @param mixed $data
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Quote\Model\Quote\Address\RateRequest $request,
        \Owebia\ShippingCore\Helper\Registry $registry,
        $data = null
    ) {
        $this->objectManager = $objectManager;
        $this->backendAuthSession = $backendAuthSession;
        $this->request = $request;
        $this->registry = $registry;
        $this->logger = $this->objectManager->get('Owebia\ShippingCore\Logger\Logger');
        $this->data = $data;
        $this->cache = new \Magento\Framework\DataObject();
    }

    protected function isBackendOrder()
    {
        return $this->backendAuthSession->isLoggedIn();
    }

    /**
     * return array
     */
    protected function getAdditionalData()
    {
        $data = [];
        foreach ($this->additionalAttributes as $k) {
            $data[$k] = $this->{$k};
        }
        return $data;
    }

    /**
     * @param mixed $value
     * @param string|null $variableName
     * @return mixed
     */
    protected function convertToString($value, $variableName = null)
    {
        if (!isset($value) || in_array(gettype($value), ['boolean', 'double', 'integer', 'string'])) {
            return var_export($value, true);
        } elseif (is_array($value)) {
            foreach ($value as $item) {
                if (is_object($item) || is_array($item)) {
                    return 'array(size:' . count($value) . ')';
                }
            }
            return var_export($value, true);
        } elseif (is_object($value)) {
            $variableName = isset($variableName) ? $variableName : 'obj';
            return "/** @var \\" . get_class($value) . " */ \$$variableName";
        } else {
            return $value;
        }
    }

    /**
     * @param mixed $data
     * @param string $className
     * @return \Owebia\ShippingCore\Model\Wrapper\AbstractWrapper
     */
    protected function createWrapper($data, $className = 'SourceWrapper')
    {
        return $this->registry->create($className, [ 'data' => $data ]);
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    protected function wrap($data)
    {
        return $this->registry->wrap($data);
    }

    /**
     * @return array
     */
    abstract protected function getKeys();

    /**
     * @param string $key
     * @return mixed
     */
    abstract protected function loadData($key);

    /**
     * @return string
     */
    protected function helpValue($value, $key)
    {
        $value = htmlspecialchars($this->convertToString($this->wrap($value), $key));
        $value = str_replace("\n", "\n    ", $value);
        return "    " . $this->convertToString($key) . " => " . $value;
    }

    /**
     * @return string
     */
    public function help()
    {
        $output = " [\n";
        foreach ($this->getKeys() as $k) {
            $output .= $this->helpValue($this->{$k}, $k) . "\n";
        }
        if ($this->aliasMap) {
            $output .= "  // aliases\n";
            foreach ($this->aliasMap as $k => $originalKey) {
                $output .= $this->helpValue($this->{$k}, $k) . " // $originalKey\n";
            }
        }
        $additionalData = array_keys($this->getAdditionalData());
        if ($additionalData) {
            $output .= "  // additional attributes\n";
            foreach ($additionalData as $k) {
                $output .= $this->helpValue($this->{$k}, $k) . "\n";
            }
        }
        $output .= "]";
        return $output;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        // echo "{$this->_uid}.__get(" . $key . ')';//var_export($this->aliasMap);
        if (isset($this->aliasMap[$key])) {
            return $this->__get($this->aliasMap[$key]);
        }
        if (!$this->cache->hasData($key)) {
            // echo '!hasData(' . $key . ')';
            $value = $this->wrap($this->loadData($key));
            $this->cache->setData($key, $value);
        }
        // echo 'getData(' . $key . ')';
        return $this->cache->getData($key);
    }
}
