<?php

namespace DeliciousBrains\WP_Offload_S3\Aws3\Aws\Crypto;

use DeliciousBrains\WP_Offload_S3\Aws3\GuzzleHttp\Psr7;
use DeliciousBrains\WP_Offload_S3\Aws3\GuzzleHttp\Psr7\StreamDecoratorTrait;
use DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\StreamInterface;
use RuntimeException;
/**
 * @internal Represents a stream of data to be gcm decrypted.
 */
class AesGcmDecryptingStream implements \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Crypto\AesStreamInterface
{
    use StreamDecoratorTrait;
    private $aad;
    private $initializationVector;
    private $key;
    private $keySize;
    private $cipherText;
    private $tag;
    private $tagLength;
    /**
     * @param StreamInterface $cipherText
     * @param string $key
     * @param string $initializationVector
     * @param string $tag
     * @param string $aad
     * @param int $tagLength
     * @param int $keySize
     */
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\StreamInterface $cipherText, $key, $initializationVector, $tag, $aad = '', $tagLength = 128, $keySize = 256)
    {
        if (version_compare(PHP_VERSION, '7.1', '<')) {
            throw new \RuntimeException('AES-GCM decryption is only supported in PHP 7.1 or greater');
        }
        $this->cipherText = $cipherText;
        $this->key = $key;
        $this->initializationVector = $initializationVector;
        $this->tag = $tag;
        $this->aad = $aad;
        $this->tagLength = $tagLength;
        $this->keySize = $keySize;
    }
    public function getOpenSslName()
    {
        return "aes-{$this->keySize}-gcm";
    }
    public function getAesName()
    {
        return 'AES/GCM/NoPadding';
    }
    public function getCurrentIv()
    {
        return $this->initializationVector;
    }
    public function createStream()
    {
        return \DeliciousBrains\WP_Offload_S3\Aws3\GuzzleHttp\Psr7\stream_for(openssl_decrypt((string) $this->cipherText, $this->getOpenSslName(), $this->key, OPENSSL_RAW_DATA, $this->initializationVector, $this->tag, $this->aad));
    }
    public function isWritable()
    {
        return false;
    }
}
