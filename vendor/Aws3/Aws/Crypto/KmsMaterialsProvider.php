<?php

namespace DeliciousBrains\WP_Offload_S3\Aws3\Aws\Crypto;

use DeliciousBrains\WP_Offload_S3\Aws3\Aws\Kms\KmsClient;
/**
 * Uses KMS to supply materials for encrypting and decrypting data.
 */
class KmsMaterialsProvider extends \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Crypto\MaterialsProvider
{
    private $kmsClient;
    private $kmsKeyId;
    /**
     * @param KmsClient $kmsClient A KMS Client for use encrypting and
     *                             decrypting keys.
     * @param string $kmsKeyId The private KMS key id to be used for encrypting
     *                         and decrypting keys.
     */
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws3\Aws\Kms\KmsClient $kmsClient, $kmsKeyId = null)
    {
        $this->kmsClient = $kmsClient;
        $this->kmsKeyId = $kmsKeyId;
    }
    public function fromDecryptionEnvelope(\DeliciousBrains\WP_Offload_S3\Aws3\Aws\Crypto\MetadataEnvelope $envelope)
    {
        if (empty($envelope[\DeliciousBrains\WP_Offload_S3\Aws3\Aws\Crypto\MetadataEnvelope::MATERIALS_DESCRIPTION_HEADER])) {
            throw new \RuntimeException('Not able to detect kms_cmk_id from an' . ' empty materials description.');
        }
        $materialsDescription = json_decode($envelope[\DeliciousBrains\WP_Offload_S3\Aws3\Aws\Crypto\MetadataEnvelope::MATERIALS_DESCRIPTION_HEADER], true);
        if (empty($materialsDescription['kms_cmk_id'])) {
            throw new \RuntimeException('Not able to detect kms_cmk_id from kms' . ' materials description.');
        }
        return new \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Crypto\KmsMaterialsProvider($this->kmsClient, $materialsDescription['kms_cmk_id']);
    }
    /**
     * The KMS key id for use in matching this Provider to its keys,
     * consistently with other SDKs as 'kms_cmk_id'.
     *
     * @return array
     */
    public function getMaterialsDescription()
    {
        return ['kms_cmk_id' => $this->kmsKeyId];
    }
    public function getWrapAlgorithmName()
    {
        return 'kms';
    }
    /**
     * Takes a content encryption key (CEK) and description to return an encrypted
     * key by using KMS' Encrypt API.
     *
     * @param string $unencryptedCek Key for use in encrypting other data
     *                               that itself needs to be encrypted by the
     *                               Provider.
     * @param string $materialDescription Material Description for use in
     *                                    encrypting the $cek.
     *
     * @return string
     */
    public function encryptCek($unencryptedCek, $materialDescription)
    {
        $encryptedDataKey = $this->kmsClient->encrypt(['Plaintext' => $unencryptedCek, 'KeyId' => $this->kmsKeyId, 'EncryptionContext' => $materialDescription]);
        return base64_encode($encryptedDataKey['CiphertextBlob']);
    }
    /**
     * Takes an encrypted content encryption key (CEK) and material description
     * for use decrypting the key by using KMS' Decrypt API.
     *
     * @param string $encryptedCek Encrypted key to be decrypted by the Provider
     *                             for use decrypting other data.
     * @param string $materialDescription Material Description for use in
     *                                    encrypting the $cek.
     *
     * @return string
     */
    public function decryptCek($encryptedCek, $materialDescription)
    {
        $result = $this->kmsClient->decrypt(['CiphertextBlob' => $encryptedCek, 'EncryptionContext' => $materialDescription]);
        return $result['Plaintext'];
    }
}
