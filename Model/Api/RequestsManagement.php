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

namespace Mageplaza\Gdpr\Model\Api;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Webapi\Exception as ExceptionApi;
use Mageplaza\Gdpr\Api\RequestsManagementInterface;
use Mageplaza\Gdpr\Helper\Data;
use Mageplaza\Gdpr\Model\Api\Data\Config\GeneralConfig;

/**
 * Class GeneralConfig
 * @package Mageplaza\Gdpr\Model\Api
 */
class RequestsManagement implements RequestsManagementInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var EventManagerInterface
     */
    protected $_eventManager;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var GeneralConfig
     */
    protected $_generalConfig;
    /**
     * @var AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * @var UserContextInterface
     */
    protected $_userContext;

    /**
     * @param GeneralConfig $generalConfig
     * @param AddressRepositoryInterface $addressRepository
     * @param Data $helperData
     * @param CustomerRepositoryInterface $customerRepository
     * @param EventManagerInterface $eventManager
     * @param Registry $registry
     * @param UserContextInterface $userContext
     */
    public function __construct(
        GeneralConfig $generalConfig,
        AddressRepositoryInterface $addressRepository,
        Data $helperData,
        CustomerRepositoryInterface $customerRepository,
        EventManagerInterface $eventManager,
        Registry $registry,
        UserContextInterface $userContext
    ) {
        $this->_generalConfig      = $generalConfig;
        $this->_addressRepository  = $addressRepository;
        $this->helperData          = $helperData;
        $this->_customerRepository = $customerRepository;
        $this->_eventManager       = $eventManager;
        $this->registry            = $registry;
        $this->_userContext        = $userContext;
    }

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        return new DataObject(
            [
                'general_config' => $this->_generalConfig->getConfig()
            ]
        );
    }

    /**
     * @inheritDoc
     * @throws ExceptionApi
     */
    public function deleteDefaultAddress($addressId)
    {
        try {
            $this->_addressRepository->deleteById($addressId);
        } catch (\Exception $e) {
            throw new ExceptionApi(__($e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritDoc
     * @throws ExceptionApi
     */
    public function deleteCustomerAccount()
    {
        if (!$this->helperData->allowDeleteAccount()) {
            $message = __('Not allow delete');
            throw new ExceptionApi(__($message));
        }

        $checktoken = new DataObject(['flag' => true]);
        $customerId = $this->getCurrentUserId();
        try {
            $customer = $this->_customerRepository->getById($customerId);
        } catch (NoSuchEntityException | LocalizedException $e) {
            throw new ExceptionApi(__($e->getMessage()));
        }

        /** event anonymise & delete customer before delete account */
        $this->_eventManager->dispatch('anonymise_account_before_delete', compact('customer', 'checktoken'));

        if (!$checktoken->getFlag()) {
            $message = __('Flag not exit');

            throw new ExceptionApi(__($message));
        }
        try {
            $this->registry->register('isSecureArea', true, true);
            /** When perform delete operation, magento check isSecureArea is true/false. */
            $this->_customerRepository->deleteById($customerId);

            /** event anonymise & delete customer after delete account */
            $this->_eventManager->dispatch('anonymise_account_after_delete', ['customer' => $customer]);

            return true;
        } catch (\Exception $e) {
            throw new ExceptionApi(__($e->getMessage()));
        }
    }

    /**
     * @return int
     */
    public function getCurrentUserId()
    {
        return $this->_userContext->getUserId();
    }
}
