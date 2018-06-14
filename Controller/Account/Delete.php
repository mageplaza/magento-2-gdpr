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

namespace Mageplaza\Gdpr\Controller\Account;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Mageplaza\Gdpr\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class Account
 * @package Mageplaza\Gdpr\Controller\Delete
 */
class Delete extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Mageplaza\Gdpr\Helper\Data
     */
    protected $_helper;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * Delete constructor.
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param Session $customerSession
     * @param Registry $registry
     * @param LoggerInterface $logger
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        Registry $registry,
        LoggerInterface $logger,
        Data $helper
    )
    {
        $this->_customerRepository = $customerRepository;
        $this->_customerSession    = $customerSession;
        $this->registry            = $registry;
        $this->logger              = $logger;
        $this->_helper             = $helper;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        if (!$this->_helper->allowDeleteAccount() || !$this->_customerSession->isLoggedIn()) {
            $this->registry->register('use_page_cache_plugin', false);
            $this->_forward('noRoute');

            return;
        }

        $customerId = $this->_customerSession->getCustomerId();
        $customer   = $this->_customerRepository->getById($customerId);
        $checktoken = new \Magento\Framework\DataObject(['flag' => true]);

        /** event anonymise & delete customer before delete account*/
        $this->_eventManager->dispatch('anonymise_account_before_delete', ['customer' => $customer, 'checktoken' => $checktoken]);

        if (!$checktoken->getFlag()) {
            $this->registry->register('use_page_cache_plugin', false);
            $this->_forward('noRoute');

            return;
        }

        try {
            /**When perform delete operation, magento check isSecureArea is true/false.*/
            $this->registry->register('isSecureArea', true, true);
            $this->_customerSession->logout();
            $this->_customerRepository->deleteById($customerId);

            /** event anonymise & delete customer after delete account*/
            $this->_eventManager->dispatch('anonymise_account_after_delete', ['customer' => $customer]);

            if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                $metadata->setPath('/');
                $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
            }

            $path = '*/*/deleteSuccess';
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->messageManager->addErrorMessage(__('Something wrong while deleting your account. Please contact the store owner.'));
            $path = '*/*/';
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($path);

        return $resultRedirect;
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated
     * @return PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = ObjectManager::getInstance()->get(PhpCookieManager::class);
        }

        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated
     * @return CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = ObjectManager::getInstance()->get(CookieMetadataFactory::class);
        }

        return $this->cookieMetadataFactory;
    }
}