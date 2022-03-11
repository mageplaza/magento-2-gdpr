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

use Magento\Framework\DataObject;
use Magento\Framework\Webapi\Rest\Request;
use Mageplaza\Gdpr\Api\Data\Config\GeneralConfigInterface;
use Mageplaza\Gdpr\Helper\Data;

/**
 * Class GeneralConfig
 * @package Mageplaza\Gdpr\Model\Api\Data\Config
 */
class GeneralConfig implements GeneralConfigInterface
{
    /**
     * @var Data
     */
    private $_helperData;

    /**
     * @var Request
     */
    private $_request;
    /**
     * @var int
     */
    protected $storeId;

    /**
     * GeneralConfig constructor.
     * @param Data $helperData
     * @param Request $request
     */
    public function __construct(
        Data $helperData,
        Request $request
    ) {
        $this->_helperData = $helperData;
        $this->_request    = $request;
        if (isset($this->_request->getBodyParams()['storeId'])) {
            $this->storeId = $this->_request->getBodyParams()['storeId'];

        }
    }

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        return new DataObject([
            "enable"                       => $this->getEnable(),
            "allow_delete_customer"        => $this->getAllowDeleteCustomer(),
            "delete_customer_message"      => $this->getDeleteCustomerMessage(),
            "allow_delete_default_address" => $this->getAllowDeleteDefaultAddress()
        ]);
    }

    /**
     * @return bool
     */
    public function getEnable()
    {
        return $this->_helperData->getConfigGeneral(self::ENABLE, $this->storeId);
    }

    /**
     * @inheritDoc
     */
    public function getAllowDeleteCustomer()
    {
        return $this->_helperData
            ->getConfigGeneral(self::ALLOW_DELETE_CUSTOMER, $this->storeId);
    }

    /**
     * @inheritDoc
     */
    public function getDeleteCustomerMessage()
    {
        return $this->_helperData
            ->getConfigGeneral(self::DELETE_CUSTOMER_MESSAGE, $this->storeId);
    }

    /**
     * @inheritDoc
     */
    public function getAllowDeleteDefaultAddress()
    {
        return $this->_helperData
            ->getConfigGeneral(self::ALLOW_DELETE_DEFAULT_ADDRESS, $this->storeId);
    }
}
