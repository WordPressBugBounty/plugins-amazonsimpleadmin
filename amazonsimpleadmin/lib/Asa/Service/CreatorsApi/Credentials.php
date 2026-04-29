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
 * - _asa_creators_api_version (string, e.g. "2.2" or "3.2")
 * - _asa_creators_api_country_code (string, optional override for PA API country code)
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
    const OPTION_VERSION = '_asa_creators_api_version';
    const OPTION_COUNTRY_CODE = '_asa_creators_api_country_code';

    /**
     * Supported credential versions
     * v2.x: Cognito-based OAuth2
     * v3.x: LWA-based OAuth2 (issued to new associates from February 2026)
     */
    const SUPPORTED_VERSIONS = array('2.1', '2.2', '2.3', '3.1', '3.2', '3.3');

    /**
     * Region code → default v2 version (used as locale-based fallback)
     * Kept in sync with Asa_Service_CreatorsApi::$localeVersionMap.
     */
    const DEFAULT_LOCALE_VERSION_MAP = array(
        // Americas
        'US' => '2.1', 'CA' => '2.1', 'MX' => '2.1', 'BR' => '2.1',
        // Europe / MENA / India
        'DE' => '2.2', 'UK' => '2.2', 'FR' => '2.2', 'IT' => '2.2',
        'ES' => '2.2', 'NL' => '2.2', 'PL' => '2.2', 'SE' => '2.2',
        'BE' => '2.2', 'TR' => '2.2', 'EG' => '2.2', 'SA' => '2.2',
        'AE' => '2.2', 'IN' => '2.2',
        // Far East
        'JP' => '2.3', 'AU' => '2.3', 'SG' => '2.3',
    );

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
     * @var string|null Credential version (e.g. "2.2", "3.2")
     */
    private $_version;

    /**
     * @var string|null Country code override (e.g. "DE", "US")
     */
    private $_countryCode;

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

        $version = get_option(self::OPTION_VERSION, '');
        $this->_version = self::isSupportedVersion($version) ? $version : '';

        $this->_countryCode = strtoupper((string) get_option(self::OPTION_COUNTRY_CODE, ''));
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

        if ($this->_version !== null) {
            $result = update_option(self::OPTION_VERSION, $this->_version);
            if (!$result && get_option(self::OPTION_VERSION) !== $this->_version) {
                $allSuccess = false;
            }
        }

        if ($this->_countryCode !== null) {
            $result = update_option(self::OPTION_COUNTRY_CODE, $this->_countryCode);
            if (!$result && get_option(self::OPTION_COUNTRY_CODE) !== $this->_countryCode) {
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
        // OAuth credentials must be preserved verbatim; sanitize_text_field()
        // collapses whitespace and strips angle-bracket sequences, which can
        // silently corrupt opaque secrets/IDs.
        $this->_credentialId = is_string($credentialId) ? trim($credentialId) : '';
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
        // OAuth secrets must be preserved verbatim; sanitize_text_field()
        // collapses whitespace and strips angle-bracket sequences, which can
        // silently corrupt opaque secrets.
        $this->_credentialSecret = is_string($credentialSecret) ? trim($credentialSecret) : '';
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
     * Set credential version
     *
     * Only versions in SUPPORTED_VERSIONS are accepted; otherwise the value is
     * cleared so that the locale-based fallback in Asa_Service_CreatorsApi
     * applies.
     *
     * @param string $version
     * @return $this
     */
    public function setVersion($version)
    {
        $version = is_string($version) ? trim($version) : '';
        $this->_version = self::isSupportedVersion($version) ? $version : '';
        return $this;
    }

    /**
     * Get credential version (empty string when not configured)
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->_version ?: '';
    }

    /**
     * Check if a credential version has been configured by the user
     *
     * @return bool
     */
    public function hasVersion()
    {
        return !empty($this->_version);
    }

    /**
     * Check if a version is supported
     *
     * @param string $version
     * @return bool
     */
    public static function isSupportedVersion($version)
    {
        return in_array($version, self::SUPPORTED_VERSIONS, true);
    }

    /**
     * Get list of supported versions
     *
     * @return array
     */
    public static function getSupportedVersions()
    {
        return self::SUPPORTED_VERSIONS;
    }

    /**
     * Set country code override (validated against Creators API marketplaces)
     *
     * Empty string clears the override so that the PA API country code is used
     * as fallback in Asa_Service_CreatorsApi.
     *
     * @param string $countryCode
     * @return $this
     */
    public function setCountryCode($countryCode)
    {
        $countryCode = is_string($countryCode) ? strtoupper(trim($countryCode)) : '';
        // Defer marketplace validation to the service to avoid loading it here
        // (Credentials must remain Guzzle-free; the service requires Guzzle).
        $this->_countryCode = $countryCode;
        return $this;
    }

    /**
     * Get country code override (empty string when not configured)
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->_countryCode ?: '';
    }

    /**
     * Check if a country code override has been configured
     *
     * @return bool
     */
    public function hasCountryCode()
    {
        return !empty($this->_countryCode);
    }

    /**
     * Get the legacy v2.x default version for a locale (fallback only)
     *
     * v3.x cannot be inferred from a locale because Cognito and LWA cover the
     * same regions; the version depends on which credential type Amazon issued.
     *
     * @param string $locale Country code
     * @return string Default v2.x version (defaults to "2.1")
     */
    public static function getDefaultVersionForLocale($locale)
    {
        return self::DEFAULT_LOCALE_VERSION_MAP[$locale] ?? '2.1';
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
        $success = $success && delete_option(self::OPTION_VERSION);
        $success = $success && delete_option(self::OPTION_COUNTRY_CODE);

        // Clear cached values
        $this->_credentialId = null;
        $this->_credentialSecret = null;
        $this->_enabled = null;
        $this->_trackingId = null;
        $this->_version = null;
        $this->_countryCode = null;

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
            'tracking_id' => $this->getTrackingId(),
            'version' => $this->getVersion(),
            'country_code' => $this->getCountryCode()
        );
    }
}
