<?php

namespace Tests\Unit;

use App\Services\Payments\NestPay\NestPayHashService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class NestPayHashServiceTest extends TestCase
{
    public function test_generates_hash_for_normal_parameters(): void
    {
        $service = new NestPayHashService();

        $parameters = [
            'clientid' => '290300000',
            'amount' => '19.95',
            'oid' => 'ORD-20260915-0001',
            'okUrl' => 'https://example.test/payment/success',
            'failUrl' => 'https://example.test/payment/failure',
            'currency' => '978',
            'storetype' => '3d_pay_hosting',
            'trantype' => 'Auth',
            'lang' => 'en',
            'hashAlgorithm' => 'ver3',
        ];

        $this->assertSame(
            $this->expectedHash($parameters, 'test-store-key'),
            $service->generateRequestHash($parameters, 'test-store-key'),
        );
    }

    public function test_parameter_ordering_is_deterministic_and_alphabetical(): void
    {
        $service = new NestPayHashService();

        $first = [
            'oid' => 'ORDER-1',
            'amount' => '100.00',
            'clientid' => '290300000',
        ];
        $second = [
            'clientid' => '290300000',
            'oid' => 'ORDER-1',
            'amount' => '100.00',
        ];

        $this->assertSame(
            $service->generateRequestHash($first, 'test-store-key'),
            $service->generateRequestHash($second, 'test-store-key'),
        );

        $this->assertSame(['amount', 'clientid', 'oid'], array_keys($service->hashableParameters($first)));
    }

    public function test_empty_parameter_preserves_position(): void
    {
        $service = new NestPayHashService();
        $parameters = [
            'amount' => '100.00',
            'callbackUrl' => '',
            'clientid' => '290300000',
        ];

        $this->assertSame(
            $this->expectedHash($parameters, 'test-store-key'),
            $service->generateRequestHash($parameters, 'test-store-key'),
        );
    }

    public function test_pipe_character_is_escaped_before_hashing(): void
    {
        $service = new NestPayHashService();
        $parameters = [
            'amount' => '100.00',
            'description' => 'Portable power | backup',
        ];

        $this->assertSame(
            $this->expectedHash($parameters, 'test-store-key'),
            $service->generateRequestHash($parameters, 'test-store-key'),
        );
    }

    public function test_backslash_character_is_escaped_before_hashing(): void
    {
        $service = new NestPayHashService();
        $parameters = [
            'amount' => '100.00',
            'description' => 'Folder\\Order',
        ];

        $this->assertSame(
            $this->expectedHash($parameters, 'test-store-key'),
            $service->generateRequestHash($parameters, 'test-store-key'),
        );
    }

    public function test_excludes_encoding_hash_and_reserved_return_merchant(): void
    {
        $service = new NestPayHashService();
        $parameters = [
            'amount' => '100.00',
            'clientid' => '290300000',
            'encoding' => 'UTF-8',
            'hash' => 'bank-sent-hash',
            'reservedReturnMerchant' => 'do-not-send',
        ];
        $expectedParameters = [
            'amount' => '100.00',
            'clientid' => '290300000',
        ];

        $this->assertSame(
            $this->expectedHash($expectedParameters, 'test-store-key'),
            $service->generateRequestHash($parameters, 'test-store-key'),
        );

        $this->assertSame(['amount', 'clientid'], array_keys($service->hashableParameters($parameters)));
    }

    public function test_throws_when_store_key_is_missing(): void
    {
        $service = new NestPayHashService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('NestPay store key is not configured.');

        $service->generateRequestHash(['amount' => '100.00'], '');
    }

    public function test_throws_when_parameter_is_not_scalar(): void
    {
        $service = new NestPayHashService();

        $this->expectException(InvalidArgumentException::class);

        $service->generateRequestHash(['amount' => ['100.00']], 'test-store-key');
    }

    private function expectedHash(array $parameters, string $storeKey): string
    {
        foreach (['encoding', 'hash', 'reservedReturnMerchant'] as $excluded) {
            unset($parameters[$excluded]);
        }

        uksort($parameters, static function (string $a, string $b): int {
    return strcasecmp($a, $b);
});

        $values = array_map(fn ($value): string => $this->escapeExpected($value), array_values($parameters));
        $values[] = $this->escapeExpected($storeKey);

        return base64_encode(hash('sha512', implode('|', $values), true));
    }

    private function escapeExpected(mixed $value): string
    {
        return str_replace(['\\', '|'], ['\\\\', '\\|'], (string) ($value ?? ''));
    }

    public function test_parameter_order_matches_official_nestpay_case_insensitive_order(): void
{
    $service = new NestPayHashService();

    $parameters = [
        'clientid' => '100200127',
        'amount' => '95.93',
        'okurl' => 'http://localhost/example',
        'failUrl' => 'http://localhost/example',
        'TranType' => 'Auth',
        'Instalment' => '',
        'callbackUrl' => 'http://localhost/callback',
        'currency' => '949',
        'rnd' => '87954458746',
        'storetype' => '3D',
        'lang' => 'tr',
        'hashAlgorithm' => 'ver3',
        'BillToName' => 'name',
        'BillToCompany' => 'billToCompany',
        'refreshtime' => '5',
    ];

    $this->assertSame(
        [
            'amount',
            'BillToCompany',
            'BillToName',
            'callbackUrl',
            'clientid',
            'currency',
            'failUrl',
            'hashAlgorithm',
            'Instalment',
            'lang',
            'okurl',
            'refreshtime',
            'rnd',
            'storetype',
            'TranType',
        ],
        array_keys($service->hashableParameters($parameters))
    );
}
}
