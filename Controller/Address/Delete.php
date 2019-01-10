<?php
/**
 * Mageplaza
 */

namespace Mageplaza\Gdpr\Controller\Address;


use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Api\AddressRepositoryInterface;

/**
 * Delete Address
 */
class Delete extends Action
{

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * Delete constructor.
     * @param Context $context
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        Context $context,
        AddressRepositoryInterface $addressRepository

    )
    {
        $this->_addressRepository = $addressRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $addressId = $this->getRequest()->getParam("id");
        try {
            $this->_addressRepository->deleteById($addressId);
            $this->messageManager->addSuccess("Successfully deleted customer address");
            $this->_redirect('customer/address/');

        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('customer/address/');

        }
    }

}