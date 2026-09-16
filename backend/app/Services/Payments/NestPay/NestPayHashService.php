<?php

namespace App\Services\Payments\NestPay;

use InvalidArgumentException;

class NestPayHashService
{
    private const EXCLUDED_PARAMETERS = [
        'encoding',
        'hash',
        'HASH',
        'reservedReturnMerchant',
    ];

    public function generateRequestHash(array $parameters, ?string $storeKey = null): string
    {
        $resolvedStoreKey = $storeKey ?? config('nestpay.store_key');

        if (! is_string($resolvedStoreKey) || trim($resolvedStoreKey) === '') {
            throw new InvalidArgumentException('NestPay store key is not configured.');
        }

        return base64_encode(hash('sha512', $this->plainText($parameters, $resolvedStoreKey), true));
    }

    public function validateResponseHash(array $parameters, ?string $storeKey = null): bool
    {
        $receivedHash = $this->receivedHash($parameters);

        if ($receivedHash === null || $receivedHash === '') {
            return false;
        }

        return hash_equals($receivedHash, $this->generateRequestHash($parameters, $storeKey));
    }

    public function hashableParameters(array $parameters): array
    {
        $filtered = [];

        foreach ($parameters as $name => $value) {
            $name = (string) $name;

            if ($this->isExcludedParameter($name)) {
                continue;
            }

            if (! is_scalar($value) && $value !== null) {
                throw new InvalidArgumentException(
                    "NestPay parameter [{$name}] must be scalar or null."
                );
            }

            $filtered[$name] = $value;
        }

        uksort($filtered, static fn (string $a, string $b): int => strcasecmp($a, $b));

        return $filtered;
    }


    private function plainText(array $parameters, string $storeKey): string
    {
        $values = array_map(
            fn ($value): string => $this->escapeValue($value),
            array_values($this->hashableParameters($parameters)),
        );

        $values[] = $this->escapeValue($storeKey);

        return implode('|', $values);
    }

    private function escapeValue(mixed $value): string
    {
        return str_replace(['\\', '|'], ['\\\\', '\\|'], (string) ($value ?? ''));
    }

    private function isExcludedParameter(string $name): bool
    {
        foreach (self::EXCLUDED_PARAMETERS as $excluded) {
            if (strcasecmp($name, $excluded) === 0) {
                return true;
            }
        }

        return false;
    }

    private function receivedHash(array $parameters): ?string
    {
        foreach ($parameters as $name => $value) {
            if (strcasecmp((string) $name, 'hash') === 0) {
                return is_scalar($value) ? (string) $value : null;
            }
        }

        return null;
    }
}
