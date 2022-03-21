<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
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
declare(strict_types=1);

namespace Mageplaza\Gdpr\GraphQl\Model\Resolver;

use Magento\Framework\DataObject;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\Registry;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Mageplaza\Gdpr\Helper\Data;
use Exception;


/**
 * Class CreateDeleteAccountRequest
 * @package Mageplaza\Gdpr\GraphQl\Model\Resolver
 */
class CreateDeleteAccountRequest implements ResolverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var GetCustomer
     */
    protected $_getCustomer;

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
     * CreateDeleteAccountRequest constructor.
     *
     * @param Data $helperData
     * @param GetCustomer $getCustomer
     * @param CustomerRepositoryInterface $customerRepository
     * @param EventManagerInterface $eventManager
     * @param Registry $registry
     */
    public function __construct(
        Data $helperData,
        GetCustomer $getCustomer,
        CustomerRepositoryInterface $customerRepository,
        EventManagerInterface $eventManager,
        Registry $registry
    ) {
        $this->helperData          = $helperData;
        $this->_getCustomer        = $getCustomer;
        $this->_customerRepository = $customerRepository;
        $this->_eventManager       = $eventManager;
        $this->registry            = $registry;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        if (!$this->helperData->isEnabled()) {
            throw new GraphQlAuthorizationException(__('The Gdpr is disabled.'));
        }

        if (!$this->helperData->allowDeleteAccount()) {
            $message = __('Not allow delete');

            return $this->returnResult($message);
        }

        $checkToken = new DataObject(['flag' => true]);
        $customerId = $context->getUserId();
        $customer   = $this->_customerRepository->getById($customerId);

        /** event anonymise & delete customer before delete account */
        $this->_eventManager->dispatch('anonymise_account_before_delete', compact('customer', 'checkToken'));

        if (!$checkToken->getFlag()) {
            $message = __('Flag not exit');

            return $this->returnResult($message);
        }

        try {
            $this->registry->register('isSecureArea', true, true);
            /** When perform delete operation, magento check isSecureArea is true/false. */
            $this->_customerRepository->deleteById($customerId);

            /** event anonymise & delete customer after delete account */
            $this->_eventManager->dispatch('anonymise_account_after_delete', ['customer' => $customer]);
            $message = __('Customer id %1 has been deleted', $customerId);

        } catch (Exception $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        }

        return $this->returnResult($message);
    }

    /**
     * @param string $message
     *
     * @return array
     */
    public function returnResult($message)
    {
        return ['result' => $message];
    }
}
