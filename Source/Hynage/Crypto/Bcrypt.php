<?php
namespace Hynage\Crypto;

class Bcrypt
{
    /**
     * @var int
     */
    const RAND_BYTES_SALT = 16;


    /**
     * @throws \Hynage\Crypto\UnsupportedAlgorithm
     */
    public function __construct()
    {
        if (CRYPT_BLOWFISH != 1) {
            throw new UnsupportedAlgorithmException('bcrypt is not supported on this system.');
        }
    }


    /**
     * Hash the $plain-text password.
     *
     * @throws \Hynage\Crypto\HashingFailedException
     * @param string $plain
     * @param int $rounds
     * @return string|false
     */
    public function hashPassword($plain, $rounds)
    {
        $hash = crypt($plain, $this->generateSalt($rounds));

        if (mb_strlen($hash) != 60) {
            throw new HashingFailedException('Failed to hash the given password.');
        }
        
        return $hash;
    }


    /**
     * @param string $plain
     * @param string $hash
     * @return bool
     */
    public function verifyPassword($plain, $hash)
    {
        return $hash === crypt($plain, $hash);
    }
    

    /**
     * @throws \OutOfBoundsException
     * @param $rounds
     * @return string
     */
    private function generateSalt($rounds)
    {
        if ($rounds < 4 || $rounds > 31) {
            throw new \OutOfBoundsException('The rounds parameter must be between 4 and 31.');
        }

        return sprintf(
            '$2a$%02d$%s',
            $rounds,
            $this->encodeBytes(
                $this->generateRandomBytes(self::RAND_BYTES_SALT)
            )
        );
    }


    /**
     * @param int $numBytes
     * @return string
     */
    private function generateRandomBytes($numBytes)
    {
        $bytes = openssl_random_pseudo_bytes($numBytes);

        if (!$bytes) {
            throw new OpenSSLException('Failed to generate random bytes using openssl_random_pseudo_bytes().');
        }

        return $bytes;
    }
    

    /**
     * @param string $bytes
     * @return string
     */
    private function encodeBytes($bytes)
    {
        return substr(str_replace('+', '.', base64_encode($bytes)), 0, 22);
    }
}