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
use Mageplaza\Gdpr\Helper\Data;

/**
 * Class GeneralConfig
 * @package Mageplaza\Gdpr\Model\Api\Data\Config
 */
class GeneralConfig extends \Magento\Framework\DataObject implements GeneralConfigInterface
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @return bool
     */
    public function getEnable()
    {
        return (bool) $this->helperData->getConfigGeneral(self::ENABLE);
    }

    /**
     * @return bool
     */
    public function getAllowDeleteCustomer()
    {
        return (bool) $this->helperData->getConfigGeneral(self::ALLOW_DELETE_CUSTOMER);
    }

    /**
     * @return array|mixed|string
     */
    public function getDeleteCustomerMessage()
    {
        return $this->helperData->getConfigGeneral(self::DELETE_CUSTOMER_MESSAGE);
    }

    /**
     * @return bool
     */
    public function getAllowDeleteDefaultAddress()
    {
        return (bool) $this->helperData->getConfigGeneral(self::ALLOW_DELETE_DEFAULT_ADDRESS);
    }
}
