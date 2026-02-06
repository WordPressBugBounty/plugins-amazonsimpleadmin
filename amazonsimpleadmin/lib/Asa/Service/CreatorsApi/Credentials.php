<?php
/**
 * AmazonSimpleAdmin (ASA1)
 *
 * Amazon Creators API Credentials Management
 *
 * Manages Credential ID, Credential Secret, and Enabled flag for Creators API.
 * For ASA1 (free version), only a single set of credentials is supported.
 *
 * Storage options:
 * - _asa_creators_api_credential_id (plain)
 * - _asa_creators_api_credential_secret (base64 encoded)
 * - _asa_creators_api_enabled (boolean)
 * - _asa_creators_api_tracking_id (plain, optional override for PA API tracking ID)
 *
 * @author Timo Reith
 */
class Asa_Service_CreatorsApi_Credentials
{
    /**
     * WordPress option keys
     */
    const OPTION_CREDENTIAL_ID = '_asa_creators_api_credential_id';
    const OPTION_CREDENTIAL_SECRET = '_asa_creators_api_credential_secret';
    const OPTION_ENABLED = '_asa_creators_api_enabled';
    const OPTION_TRACKING_ID = '_asa_creators_api_tracking_id';

    /**
     * Minimum PHP version required for Creators API
     */
    const MIN_PHP_VERSION = '8.1.0';

    /**
     * @var Asa_Service_CreatorsApi_Credentials Singleton instance
     */
    private static $_instance;

    /**
     * @var string|null Credential ID
     */
    private $_credentialId;

    /**
     * @var string|null Credential Secret (decoded)
     */
    private $_credentialSecret;

    /**
     * @var bool|null Enabled flag
     */
    private $_enabled;

    /**
     * @var string|null Tracking ID (optional override)
     */
    private $_trackingId;

    /**
     * Get singleton instance
     *
     * @return Asa_Service_CreatorsApi_Credentials
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Private constructor for singleton
     */
    private function __construct()
    {
        $this->_load();
    }

    /**
     * Load credentials from WordPress options
     *
     * @return void
     */
    private function _load()
    {
        $this->_credentialId = get_option(self::OPTION_CREDENTIAL_ID, '');

        $encodedSecret = get_option(self::OPTION_CREDENTIAL_SECRET, '');
        $this->_credentialSecret = !empty($encodedSecret) ? base64_decode($encodedSecret) : '';

        $this->_enabled = (bool) get_option(self::OPTION_ENABLED, false);

        $this->_trackingId = get_option(self::OPTION_TRACKING_ID, '');
    }

    /**
     * Save credentials to WordPress options
     *
     * Note: update_option() returns false both on failure AND when the value
     * is unchanged. We call each update independently to avoid short-circuit
     * evaluation that would skip updates when a previous value was unchanged.
     *
     * @return bool Success (all options were saved or unchanged)
     */
    public function save()
    {
        $allSuccess = true;

        if ($this->_credentialId !== null) {
            // update_option returns false if value unchanged, so we also check if current value matches
            $result = update_option(self::OPTION_CREDENTIAL_ID, $this->_credentialId);
            if (!$result && get_option(self::OPTION_CREDENTIAL_ID) !== $this->_credentialId) {
                $allSuccess = false;
            }
        }

        if ($this->_credentialSecret !== null) {
            $encoded = base64_encode($this->_credentialSecret);
            $result = update_option(self::OPTION_CREDENTIAL_SECRET, $encoded);
            if (!$result && get_option(self::OPTION_CREDENTIAL_SECRET) !== $encoded) {
                $allSuccess = false;
            }
        }

        if ($this->_enabled !== null) {
            $enabledValue = $this->_enabled ? '1' : '0';
            $result = update_option(self::OPTION_ENABLED, $enabledValue);
            if (!$result && get_option(self::OPTION_ENABLED) !== $enabledValue) {
                $allSuccess = false;
            }
        }

        if ($this->_trackingId !== null) {
            $result = update_option(self::OPTION_TRACKING_ID, $this->_trackingId);
            if (!$result && get_option(self::OPTION_TRACKING_ID) !== $this->_trackingId) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    /**
     * Set credential ID
     *
     * @param string $credentialId
     * @return $this
     */
    public function setCredentialId($credentialId)
    {
        $this->_credentialId = sanitize_text_field($credentialId);
        return $this;
    }

    /**
     * Get credential ID
     *
     * @return string
     */
    public function getCredentialId()
    {
        return $this->_credentialId ?: '';
    }

    /**
     * Check if credential ID is set
     *
     * @return bool
     */
    public function hasCredentialId()
    {
        return !empty($this->_credentialId);
    }

    /**
     * Set credential secret
     *
     * @param string $credentialSecret
     * @return $this
     */
    public function setCredentialSecret($credentialSecret)
    {
        $this->_credentialSecret = sanitize_text_field($credentialSecret);
        return $this;
    }

    /**
     * Get credential secret (decoded)
     *
     * @return string
     */
    public function getCredentialSecret()
    {
        return $this->_credentialSecret ?: '';
    }

    /**
     * Check if credential secret is set
     *
     * @return bool
     */
    public function hasCredentialSecret()
    {
        return !empty($this->_credentialSecret);
    }

    /**
     * Set enabled flag
     *
     * @param bool $enabled
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->_enabled = (bool) $enabled;
        return $this;
    }

    /**
     * Check if Creators API is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_enabled === true;
    }

    /**
     * Set tracking ID (optional override for PA API tracking ID)
     *
     * @param string $trackingId
     * @return $this
     */
    public function setTrackingId($trackingId)
    {
        $this->_trackingId = sanitize_text_field($trackingId);
        return $this;
    }

    /**
     * Get tracking ID
     *
     * @return string
     */
    public function getTrackingId()
    {
        return $this->_trackingId ?: '';
    }

    /**
     * Check if tracking ID is set
     *
     * @return bool
     */
    public function hasTrackingId()
    {
        return !empty($this->_trackingId);
    }

    /**
     * Check if valid credentials are configured
     *
     * @return bool
     */
    public function hasValidCredentials()
    {
        return $this->hasCredentialId() && $this->hasCredentialSecret();
    }

    /**
     * Check if Creators API is usable (enabled + valid credentials + PHP version)
     *
     * @return bool
     */
    public function isUsable()
    {
        return $this->isEnabled()
            && $this->hasValidCredentials()
            && self::isPhpVersionSupported();
    }

    /**
     * Check if PHP version meets minimum requirement
     *
     * @return bool
     */
    public static function isPhpVersionSupported()
    {
        return version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '>=');
    }

    /**
     * Get minimum PHP version required
     *
     * @return string
     */
    public static function getMinPhpVersion()
    {
        return self::MIN_PHP_VERSION;
    }

    /**
     * Remove all credentials from WordPress options
     *
     * @return bool Success
     */
    public function remove()
    {
        $success = delete_option(self::OPTION_CREDENTIAL_ID);
        $success = $success && delete_option(self::OPTION_CREDENTIAL_SECRET);
        $success = $success && delete_option(self::OPTION_ENABLED);
        $success = $success && delete_option(self::OPTION_TRACKING_ID);

        // Clear cached values
        $this->_credentialId = null;
        $this->_credentialSecret = null;
        $this->_enabled = null;
        $this->_trackingId = null;

        return $success;
    }

    /**
     * Reset singleton instance (for testing)
     *
     * @return void
     */
    public static function resetInstance()
    {
        self::$_instance = null;
    }

    /**
     * Get credentials as array (for debugging, without secret)
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'credential_id' => $this->getCredentialId(),
            'has_credential_secret' => $this->hasCredentialSecret(),
            'enabled' => $this->isEnabled(),
            'is_usable' => $this->isUsable(),
            'php_version_supported' => self::isPhpVersionSupported(),
            'tracking_id' => $this->getTrackingId()
        );
    }
}
