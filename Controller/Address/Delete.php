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

namespace Mageplaza\Gdpr\Controller\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

/**
 * Class Delete
 *
 * @package Mageplaza\Gdpr\Controller\Address
 */
class Delete extends Action
{
    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * Delete constructor.
     *
     * @param Context $context
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        Context $context,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->_addressRepository = $addressRepository;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $addressId = $this->getRequest()->getParam('id');
        try {
            $this->_addressRepository->deleteById($addressId);
            $this->messageManager->addSuccess(__('Successfully deleted customer address'));
            $this->_redirect('customer/address/');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('customer/address/');
        }
    }
}
