<?php

declare(strict_types=1);

namespace MsgPhp\User\Password;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class GenericPasswordHashing implements PasswordHashing
{
    /**
     * @var PasswordAlgorithm
     */
    private $defaultAlgorithm;

    /**
     * @var bool
     */
    private $deprecateLegacyApi;

    public function __construct(PasswordAlgorithm $defaultAlgorithm = null, bool $deprecateLegacyApi = true)
    {
        $this->defaultAlgorithm = $defaultAlgorithm ?? PasswordAlgorithm::create();
        $this->deprecateLegacyApi = $deprecateLegacyApi;
    }

    public function hash(string $plainPassword, PasswordAlgorithm $algorithm = null): string
    {
        $algorithm = $algorithm ?? $this->defaultAlgorithm;

        if (!$algorithm->legacy) {
            $hash = password_hash($plainPassword, (int) $algorithm->type, $algorithm->options);

            if (\function_exists('sodium_memzero')) {
                sodium_memzero($plainPassword);
            }

            if (!\is_string($hash)) {
                throw new \RuntimeException('Unable to hash password with algorithm "'.$algorithm->type.'".');
            }

            return $hash;
        }

        if ($this->deprecateLegacyApi) {
            @trigger_error('Using PHPs legacy password API is deprecated and should be avoided. Create a non-legacy algorithm using "'.PasswordAlgorithm::class.'::create()" instead.', \E_USER_DEPRECATED);
        }

        $type = (string) $algorithm->type;

        if (null !== $algorithm->salt) {
            if (1 > $algorithm->salt->iterations) {
                throw new \LogicException('No. of password salt iterations must be 1 or higher, got '.$algorithm->salt->iterations.'.');
            }

            if (2 !== ($c = substr_count($algorithm->salt->format, '%s'))) {
                throw new \LogicException('Password salt format should have exactly 2 value placeholders, found '.$c.'.');
            }

            $salted = sprintf($algorithm->salt->format, $plainPassword, $algorithm->salt->token);

            if (\function_exists('sodium_memzero')) {
                sodium_memzero($plainPassword);
            }

            $hash = hash($type, $salted, true);

            for ($i = 1; $i < $algorithm->salt->iterations; ++$i) {
                $hash = hash($type, $hash.$salted, true);
            }
        } else {
            $hash = hash($type, $plainPassword, true);

            if (\function_exists('sodium_memzero')) {
                sodium_memzero($plainPassword);
            }
        }

        return $algorithm->encodeBase64 ? base64_encode($hash) : bin2hex($hash);
    }

    public function isValid(string $hashedPassword, string $plainPassword, PasswordAlgorithm $algorithm = null): bool
    {
        $algorithm = $algorithm ?? $this->defaultAlgorithm;
        $valid = $algorithm->legacy
            ? hash_equals($hashedPassword, $this->hash($plainPassword, $algorithm))
            : password_verify($plainPassword, $hashedPassword);

        if (\function_exists('sodium_memzero')) {
            sodium_memzero($plainPassword);
        }

        return $valid;
    }
}
