<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Internal\Authentication\Service;

use OxidEsales\EshopCommunity\Internal\Authentication\Exception\PasswordHashException;
use OxidEsales\EshopCommunity\Internal\Authentication\Service\Argon2IPasswordHashService;
use OxidEsales\EshopCommunity\Internal\Authentication\Service\PasswordHashServiceInterface;
use OxidEsales\EshopCommunity\Internal\Authentication\Policy\PasswordPolicyInterface;
use PHPUnit\Framework\TestCase;
use TypeError;


/**
 * Class Argon2IPasswordHashServiceTest
 */
class Argon2IPasswordHashServiceTest extends TestCase
{

    /**
     *
     */
    protected function setUp()
    {
        if (!defined('PASSWORD_ARGON2I')) {
            $this->markTestSkipped('The password hashing algorithm "PASSWORD_ARGON2I" is not available');
        }
        $this->hashingAlgorithm = PASSWORD_ARGON2I;
    }

    /**
     *
     */
    public function testHashForGivenPasswordIsEncryptedWithProperAlgorithm()
    {
        $password = 'secret';
        $passwordHashService = $this->getPasswordHashService();
        $hash = $passwordHashService->hash($password);
        $info = password_get_info($hash);

        $this->assertSame($this->hashingAlgorithm, $info['algo']);
    }

    /**
     *
     */
    public function testHashForEmptyPasswordIsEncryptedWithProperAlgorithm()
    {
        $password = '';

        $passwordHashService = $this->getPasswordHashService();
        $hash = $passwordHashService->hash($password);
        $info = password_get_info($hash);

        $this->assertSame($this->hashingAlgorithm, $info['algo']);
    }

    /**
     *
     */
    public function testConsecutiveHashingTheSamePasswordProducesDifferentHashes()
    {
        $password = 'secret';

        $passwordHashService = $this->getPasswordHashService();
        $hash_1 = $passwordHashService->hash($password);
        $hash_2 = $passwordHashService->hash($password);

        $this->assertNotSame($hash_1, $hash_2);
    }

    /**
     * Invalid values as a memory cost value of 2^32 + 1 can cause the method hash to fail.
     *
     * @expectedException TypeError
     */
    public function testHashThrowsExceptionOnInvalidSettings()
    {
        $options = [
            1 << 32, // The value 2^32 is out of range and will produce a PHP Warning.
            PASSWORD_ARGON2_DEFAULT_TIME_COST,
            PASSWORD_ARGON2_DEFAULT_THREADS
        ];

        $passwordPolicyMock = $this->getPasswordPolicyMock();

        $passwordHashService = new Argon2IPasswordHashService(
            $passwordPolicyMock,
            $options
        );

        $passwordHashService->hash('secret');
    }


    /**
     */
    public function testPasswordNeedsRehashReturnsTrueOnChangedAlgorithm()
    {
        $originalAlgorithm = 'PASSWORD_BCRYPT';
        $newAlgorithm = 'PASSWORD_ARGON2I';
        if (!defined($originalAlgorithm) || !defined($newAlgorithm)) {
            $this->markTestSkipped(
                'The password hashing algorithms "' . $originalAlgorithm . '" and/or "' . $newAlgorithm . '" are not available'
            );
        }

        $passwordHashedWithOriginalAlgorithm = password_hash('secret', PASSWORD_BCRYPT);


        $passwordHashService = $this->getPasswordHashService();

        $this->assertTrue(
            $passwordHashService->passwordNeedsRehash($passwordHashedWithOriginalAlgorithm)
        );
    }


    public function testHashWithValidCostOption()
    {
        $passwordHashService = $this->getPasswordHashService();
        $hash = $passwordHashService->hash('secret');

        $info = password_get_info($hash);

        $this->assertSame(PASSWORD_ARGON2I, $info['algo']);
        $this->assertSame(
            [
                'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
                'time_cost' => PASSWORD_ARGON2_DEFAULT_TIME_COST,
                'threads' => PASSWORD_ARGON2_DEFAULT_THREADS
            ],
            $info['options']
        );
    }

    /**
     * @dataProvider invalidOptionsDataProvider
     *
     * @param mixed $invalidCostOption
     */
    public function testHashWithInvalidCostOptionValueThrowsPasswordHashException($invalidCostOption)
    {
        $this->expectException(TypeError::class);

        $passwordPolicy = $this->getPasswordPolicyMock();
        new Argon2IPasswordHashService(
            $passwordPolicy,
            $invalidCostOption
        );
    }

    /**
     * @return array
     */
    public function invalidOptionsDataProvider(): array
    {
        return [
            [
                [
                    '1',
                    1,
                    1
                ]
            ],
            [
                [
                    1,
                    false,
                    1
                ]
            ],
            [
                [
                    1,
                    1,
                    []
                ]
            ]
        ];
    }

    /**
     * @return PasswordHashServiceInterface
     */
    private function getPasswordHashService(): PasswordHashServiceInterface
    {
        $passwordPolicyMock = $this->getPasswordPolicyMock();

        $passwordHashService = new Argon2IPasswordHashService(
            $passwordPolicyMock,
            PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
            PASSWORD_ARGON2_DEFAULT_TIME_COST,
            PASSWORD_ARGON2_DEFAULT_THREADS
        );

        return $passwordHashService;
    }

    /**
     * @return PasswordPolicyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getPasswordPolicyMock(): PasswordPolicyInterface
    {
        $passwordPolicyMock = $this
            ->getMockBuilder(PasswordPolicyInterface::class)
            ->setMethods(['enforcePasswordPolicy'])
            ->getMock();

        return $passwordPolicyMock;
    }
}
