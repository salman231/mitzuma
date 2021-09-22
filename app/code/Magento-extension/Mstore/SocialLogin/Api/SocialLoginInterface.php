<?php
namespace Mstore\SocialLogin\Api;

/**
 * @api
 */
interface SocialLoginInterface
{
    /**
     * Social Login
     *
     * @param string $token
     * @param string $type
     * @return string Token created
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function login($token, $type);

    /**
     * Apple Login
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @return string Token created
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function appleLogin($email, $firstName, $lastName);
}