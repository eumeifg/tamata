<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magedelight\SocialLogin\Model\Resolver;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magedelight\SocialLogin\Api\SocialLoginApiServicesInterface;


/**
 * Customers Token resolver, used for GraphQL request processing.
 */
class AuthenticateCustomerWithSocialLogin implements ResolverInterface
{
    /**
     * @var SocialLoginApiServicesInterface
     */
    private $socialLoginApiServices;

    /**
     * @param SocialLoginApiServicesInterface $customerTokenService
     */
    public function __construct(
        SocialLoginApiServicesInterface $socialLoginApiServices
    ) {
        $this->socialLoginApiServices = $socialLoginApiServices;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['input']) || empty($args['input'])) {
            throw new GraphQlInputException(__('Specify the "input" value.'));
        }

        try {
            $token = $this->socialLoginApiServices->authenticateCustomerWithSocialLogin($args['input']);
            return ['token' => $token];
        } catch (AuthenticationException $e) {
            throw new GraphQlAuthenticationException(__($e->getMessage()), $e);
        }
    }
}
