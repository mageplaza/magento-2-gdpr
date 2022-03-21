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

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\GraphQl\Model\Query\ContextInterface;
use Mageplaza\Gdpr\Helper\Data;
use Exception;


/**
 * Class CreateDeleteDefaultAddressRequest
 * @package Mageplaza\Gdpr\Model\Resolver
 */
class CreateDeleteDefaultAddressRequest implements ResolverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * CreateDeleteDefaultAddressRequest constructor.
     *
     * @param Data $helperData
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        Data $helperData,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->helperData         = $helperData;
        $this->_addressRepository = $addressRepository;
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

        try {
            $addressId = $args['input']['id'];
            $this->_addressRepository->deleteById($addressId);
        } catch (Exception $e) {
            throw new GraphQlNoSuchEntityException(__('Requested entity doesn\'t exist'));
        }

        return true;
    }
}
