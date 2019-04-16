<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Integration\Internal\Password\Bridge;

use OxidEsales\EshopCommunity\Internal\Authentication\Bridge\PasswordServiceBridgeInterface;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class PasswordServiceBridgeTest extends TestCase
{
    use ContainerTrait;

    /**
     * End-to-end test for the PasswordService bridge
     */
    public function testHashWithBcrypt()
    {
        /** @var PasswordServiceBridgeInterface $passwordServiceBridge */
        $passwordServiceBridge = $this->get(PasswordServiceBridgeInterface::class);
        $hash = $passwordServiceBridge->hash('secret');
        $info = password_get_info($hash);

        $this->assertSame(PASSWORD_BCRYPT, $info['algo']);
    }

    /**
     * End-to-end test for the password verification service.
     */
    public function testVerifyPasswordWithBcrypt()
    {
        /** @var PasswordServiceBridgeInterface $passwordServiceBridge */
        $passwordServiceBridge = $this->get(PasswordServiceBridgeInterface::class);

        $password = 'secret';
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $this->assertTrue(
            $passwordServiceBridge->verifyPassword($password, $passwordHash)
        );
    }

    /**
     * End-to-end test for the password verification service.
     */
    public function testVerifyPasswordWithArgon2i()
    {
        if (!defined('PASSWORD_ARGON2I')) {
            $this->markTestSkipped('The password hashing algorithm "PASSWORD_ARGON2I" is not available');
        }

        /** @var PasswordServiceBridgeInterface $passwordServiceBridge */
        $passwordServiceBridge = $this->get(PasswordServiceBridgeInterface::class);

        $password = 'secret';
        $passwordHash = password_hash($password, PASSWORD_ARGON2I);

        $this->assertTrue(
            $passwordServiceBridge->verifyPassword($password, $passwordHash)
        );
    }
}
