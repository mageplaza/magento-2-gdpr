<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Gdpr
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Gdpr\Model\Api\Data\Config;

use Mageplaza\Gdpr\Api\Data\Config\GeneralConfigInterface;

/**
 * Class GeneralConfig
 * @package Mageplaza\Gdpr\Model\Api\Data\Config
 */
class GeneralConfig extends \Magento\Framework\DataObject implements GeneralConfigInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEnable()
    {
        return $this->getData(self::ENABLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setEnable($value)
    {
        return $this->setData(self::ENABLE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowDeleteCustomer()
    {
        return $this->getData(self::ALLOW_DELETE_CUSTOMER);
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowDeleteCustomer($value)
    {
        return $this->setData(self::ALLOW_DELETE_CUSTOMER, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getDeleteCustomerMessage()
    {
        return $this->getData(self::DELETE_CUSTOMER_MESSAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setDeleteCustomerMessage($value)
    {
        return $this->setData(self::DELETE_CUSTOMER_MESSAGE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowDeleteDefaultAddress()
    {
        return $this->getData(self::ALLOW_DELETE_DEFAULT_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowDeleteDefaultAddress($value)
    {
        return $this->setData(self::ALLOW_DELETE_DEFAULT_ADDRESS, $value);
    }
}
