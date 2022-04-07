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
 * @category  Mageplaza
 * @package   Mageplaza_Gdpr
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Gdpr\Controller\Account;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Mageplaza\Gdpr\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class Account
 *
 * @package Mageplaza\Gdpr\Controller\Delete
 */
class Delete extends AbstractAccount
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
     * @var Registry
     */
    protected $registry;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
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
     *
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
    ) {
        $this->_customerRepository = $customerRepository;
        $this->_customerSession    = $customerSession;
        $this->registry            = $registry;
        $this->logger              = $logger;
        $this->_helper             = $helper;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
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
        $checktoken = new DataObject(['flag' => true]);

        /** event anonymise & delete customer before delete account */
        $this->_eventManager->dispatch('anonymise_account_before_delete', compact('customer', 'checktoken'));

        if (!$checktoken->getFlag()) {
            $this->registry->register('use_page_cache_plugin', false);
            $this->_forward('noRoute');

            return;
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            /** When perform delete operation, magento check isSecureArea is true/false. */
            $this->registry->register('isSecureArea', true, true);
            $this->_customerSession->logout();
            $this->_customerRepository->deleteById($customerId);

            /** event anonymise & delete customer after delete account */
            $this->_eventManager->dispatch('anonymise_account_after_delete', ['customer' => $customer]);

            if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                $metadata->setPath('/');
                $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
            }

            $resultRedirect->setPath('*/*/deleteSuccess');
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->messageManager->addErrorMessage(__('Something wrong while deleting your account. Please contact the store owner.'));
            $resultRedirect->setPath('*/*/');
        }

        return $resultRedirect;
    }

    /**
     * Retrieve cookie manager
     *
     * @return     PhpCookieManager
     * @deprecated
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
     * @return     CookieMetadataFactory
     * @deprecated
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = ObjectManager::getInstance()->get(CookieMetadataFactory::class);
        }

        return $this->cookieMetadataFactory;
    }
}
