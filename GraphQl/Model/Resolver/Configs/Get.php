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

namespace Mageplaza\Gdpr\GraphQl\Model\Resolver\Configs;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mageplaza\Gdpr\Helper\Data;

/**
 * Class Get
 * @package Mageplaza\Gdpr\GraphQl\Model\Resolver\Configs
 */
class Get implements ResolverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData    = $helperData;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $store   = $context->getExtensionAttributes()->getStore();
        $storeId = $store->getId();

        if (!$this->helperData->isEnabled($storeId)) {
            throw new GraphQlInputException(__('Gdpr is disabled.'));
        }

        return $this->getGeneralConfig($storeId);
    }

    /**
     * @param $storeId
     *
     * @return array
     */
    public function getGeneralConfig($storeId)
    {
        return [
            'general' => [
                'enabled'                      => $this->helperData->getConfigGeneral('enabled', $storeId),
                'allow_delete_customer'        => $this->helperData
                    ->getConfigGeneral('allow_delete_customer', $storeId),
                'delete_customer_message'      => $this->helperData
                    ->getConfigGeneral('delete_customer_message', $storeId),
                'allow_delete_default_address' => $this->helperData
                    ->getConfigGeneral('allow_delete_default_address', $storeId),
            ]
        ];
    }
}
