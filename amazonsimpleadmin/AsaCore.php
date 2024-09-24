<?php
define('ASA_INCLUDE_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR);
include_once ASA_INCLUDE_DIR . 'asa_helper_functions.php';
include_once ASA_INCLUDE_DIR . 'ifw-php-lib-functions.php';

class AmazonSimpleAdmin {
    
    const DB_COLL         = 'asa_collection';
    const DB_COLL_ITEM    = 'asa_collection_item';

    const VERSION = '1.5.4';

    const CACHE_DEFAULT_LIFETIME = 7200;

    const PA_API_4 = 4;
    const PA_API_5 = 5;

    const NONCE_SAVE_OPTIONS = 'asa1-save-options';
    const NONCE_SAVE_CACHE_OPTIONS = 'asa1-save-cache-options';
    const NONCE_SAVE_SETUP = 'asa1-save-setup';
    const NONCE_SUBMIT_TEST = 'asa1-submit-test';
    const NONCE_ACTIVATE_COLLECTIONS = 'asa1-activate-collections';
    const NONCE_CREATE_COLLECTION = 'asa1-create-collection';
    const NONCE_IMPORT_COLLECTION = 'asa1-import-collection';
    const NONCE_ADD_ITEM_TO_COLLECTION = 'asa1-add-item-to-collection';
    const NONCE_MANAGE_COLLECTION = 'asa1-manage-collection';
    const NONCE_UPDATE_COLLECTION_ITEM = 'asa1-update-collection-item';

    /**
     * this plugins home directory
     */
    protected $plugin_dir = '/wp-content/plugins/amazonsimpleadmin';
    
    protected $plugin_url = 'options-general.php?page=amazonsimpleadmin/amazonsimpleadmin.php';
    
    /**
     * supported amazon country IDs
     */
    protected $_amazon_valid_country_codes = array(
        'AE', 'AU', 'BE', 'BR', 'CA', 'DE', 'EG', 'FR', 'IN', 'JP', 'MX', 'NL', 'PL', 'SA', 'SE', 'SG', 'TR', 'UK', 'US', 'IT', 'ES',
    );
    
    /**
     * the international amazon product page urls
     */
    protected $amazon_url = array(
        'AE'    => 'https://www.amazon.ae/exec/obidos/ASIN/%s/%s',
        'AU'    => 'https://www.amazon.com.au/exec/obidos/ASIN/%s/%s',
        'BE'    => 'https://www.amazon.com.be/exec/obidos/ASIN/%s/%s',
        'BR'    => 'https://www.amazon.com.br/exec/obidos/ASIN/%s/%s',
        'CA'    => 'https://www.amazon.ca/exec/obidos/ASIN/%s/%s',
        'DE'    => 'https://www.amazon.de/exec/obidos/ASIN/%s/%s',
        'EG'    => 'https://www.amazon.eg/exec/obidos/ASIN/%s/%s',
        'FR'    => 'https://www.amazon.fr/exec/obidos/ASIN/%s/%s',
        'JP'    => 'https://www.amazon.jp/exec/obidos/ASIN/%s/%s',
        'MX'    => 'https://www.amazon.com.mx/exec/obidos/ASIN/%s/%s',
        'NL'    => 'https://www.amazon.nl/exec/obidos/ASIN/%s/%s',
        'SA'    => 'https://www.amazon.sa/exec/obidos/ASIN/%s/%s',
        'SG'    => 'https://www.amazon.sg/exec/obidos/ASIN/%s/%s',
        'TR'    => 'https://www.amazon.com.tr/exec/obidos/ASIN/%s/%s',
        'UK'    => 'https://www.amazon.co.uk/exec/obidos/ASIN/%s/%s',
        'US'    => 'https://www.amazon.com/exec/obidos/ASIN/%s/%s',
        'IN'    => 'https://www.amazon.in/exec/obidos/ASIN/%s/%s',
        'IT'    => 'https://www.amazon.it/exec/obidos/ASIN/%s/%s',
        'ES'    => 'https://www.amazon.es/exec/obidos/ASIN/%s/%s',
        'SE'    => 'https://www.amazon.se/exec/obidos/ASIN/%s/%s',
        'PL'    => 'https://www.amazon.pl/exec/obidos/ASIN/%s/%s',
    );

    /**
     * @var string
     */
    protected $amazon_shop_url;

    /**
     * template placeholder prefix
     */
    protected $tpl_prefix = '{$';
    
    /**
     * template placeholder postfix
     */
    protected $tpl_postfix = '}';
    
    /**
     * template dir
     */
    protected $tpl_dir = 'tpl';
    
    /**
     * AmazonSimpleAdmin bb tag regex
     */
    protected $bb_regex = '#\[asa(.[^\]]*|)\]([\w-]+)\[/asa\]#Usi';
    
    /**
     * AmazonSimpleAdmin bb tag regex
     */
    protected $bb_regex_collection = '#\[asa_collection(.[^\]]*|)\]([\w-\s]+)\[/asa_collection\]#Usi';
    
    /**
     * param separator regex
     */
    protected $_regex_param_separator = '/(,)(?=(?:[^"]|"[^"]*")*$)/m';    
    
    /**
     * user's Amazon Access Key ID
     */
    protected $_amazon_api_key;
    
    /**
     * user's Amazon Access Key ID
     * @var string
     */
    protected $_amazon_api_secret_key = '';    
    
    /**
     * user's Amazon Tracking ID
     */
    protected $amazon_tracking_id;
    
    /**
     * selected country code
     */
    protected $_amazon_country_code = 'US';

    /**
     * @var
     */
    protected $_amazon_api_connection_type = 'https';

    /**
     * @var int|null
     */
    protected $_amazon_pa_api_version;

    /**
     * @var bool
     */
    protected $_asa_use_flat_box_default = false;

    /**
     * product preview status
     * @var bool
     */
    protected $_parse_comments = false;

    /**
     * use AJAX
     * @var bool
     */
    protected $_async_load = false;

    /**
     * use only amazon prices for placeholder $AmazonPrice
     * @var bool
     */
    protected $_asa_use_amazon_price_only = false;
    
    /**
     * internal param delimiter
     * @var string
     */
    protected $_internal_param_delimit = '[#asa_param_delim#]';
    
    /**
     * 
     * @var string
     */
    protected $task;
    
    /**
     * wpdb object
     */
    protected $db;
    
    /**
     * collection object
     */
    protected $collection;
    
    protected $error = array();
    protected $success = array();
    
    /**
     * the amazon webservice object
     */
    protected $amazon;

    /**
     * @var null|bool
     */
    protected $_isCache;

    /**
     * the cache object
     * @var null|AsaZend_Cache_Core|AsaZend_Cache_Frontend_File
     */
    protected $cache;

    /**
     * @var Asa_Debugger
     */
    protected $_debugger;

    /**
     * @var debugger error message
     */
    protected $_debugger_error;

    /**
     * @var AsaEmail
     */
    protected $_email;

    protected $_tplCssBuffer = array();



    /**
     * constructor
     */
    public function __construct ($wpdb) 
    {
        //$libdir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib';
        //set_include_path(get_include_path() . PATH_SEPARATOR . $libdir);

        require_once ASA_LIB_DIR . 'AsaZend/Uri/Http.php';
        require_once ASA_LIB_DIR . 'AsaZend/Service/Amazon.php';
        require_once ASA_LIB_DIR . 'AsaZend/Service/Amazon/Accessories.php';
        require_once ASA_LIB_DIR . 'AsaZend/Service/Amazon/EditorialReview.php';
        require_once ASA_LIB_DIR . 'AsaZend/Service/Amazon/Image.php';
        require_once ASA_LIB_DIR . 'AsaZend/Service/Amazon/Item.php';
        require_once ASA_LIB_DIR . 'AsaZend/Service/Amazon/ListmaniaList.php';
        require_once ASA_LIB_DIR . 'AsaZend/Service/Amazon/Offer.php';
        require_once ASA_LIB_DIR . 'AsaZend/Service/Amazon/OfferSet.php';
        require_once ASA_LIB_DIR . 'AsaZend/Service/Amazon/Query.php';
        require_once ASA_LIB_DIR . 'AsaZend/Service/Amazon/ResultSet.php';
        require_once ASA_LIB_DIR . 'AsaZend/Service/Amazon/SimilarProduct.php';
        require_once ASA_LIB_DIR . 'Asa/Util/Buffer.php';
        require_once ASA_LIB_DIR . 'Asa/ItemBuffer.php';
        require_once dirname(__FILE__) . '/AsaWidget.php';

        register_activation_hook( 'amazonsimpleadmin/amazonsimpleadmin.php', array($this, 'onActivation') );
        register_uninstall_hook( 'amazonsimpleadmin/amazonsimpleadmin.php', array('AmazonSimpleAdmin', 'onUninstall') );

        if ($this->isDebug()) {
            $this->_initDebugger();
        }
        
        if (isset($_GET['task'])) {
            $this->task = sanitize_text_field($_GET['task']);
        }
        
        $this->tpl_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->tpl_dir . DIRECTORY_SEPARATOR;
        
        $this->db = $wpdb;
        
        $this->cache = $this->_initCache();

        // init translation
        load_plugin_textdomain('asa1', false, 'amazonsimpleadmin/lang');
                
        // Hook for adding admin menus
        add_action('admin_menu', array($this, 'createAdminMenu'));
        
        // register shortcode handlers
        add_shortcode( 'asa', array($this, 'handleShortcodeAsa'));
        add_shortcode( 'asa_collection', array($this, 'handleShortcodeAsaCollection'));

        // Hooks for adding shortcode support
        add_filter('the_excerpt', 'do_shortcode');
        add_filter('the_excerpt_feed', 'do_shortcode');
        add_filter('the_excerpt_rss', 'do_shortcode');
        add_filter('the_content_feed', 'do_shortcode');
        add_filter('the_content_rss', 'do_shortcode');
        add_filter('widget_text', 'do_shortcode');

        if (!get_option('_asa_hide_meta_link')) {
            add_action('wp_meta', array($this, 'addMetaLink'));
        }
        
        $this->_getAmazonUserData();
        $this->_loadOptions();
                
        if ($this->_parse_comments == true) {
            // Hook for adding content filter for user comments
            // Feature request from Sebastian Steinfort
            //add_filter('comment_text', array($this, 'parseContent'), 1);
            add_filter('comment_text', [$this, 'doCommentShortcode']);
        }
        
        add_filter('upgrader_pre_install', array($this, 'onPreInstall'), 10, 2);
        add_filter('upgrader_post_install', array($this, 'onPostInstall'), 10, 2);
        add_action('in_plugin_update_message-amazonsimpleadmin/amazonsimpleadmin.php', array($this, 'handleUpdateMessage'));
        add_filter('plugin_action_links_amazonsimpleadmin/amazonsimpleadmin.php', array($this, 'addPluginActionLinks'));

        $this->amazon = $this->connect();

        if (get_option('_asa_error_email_notification')) {
            require_once dirname(__FILE__) . '/AsaEmail.php';
            $this->_email = AsaEmail::getInstance();
        }

        $this->_checkPaApiVersion();

        $this->_beforeOutput($this->task);

        $this->_initCallback();
    }

    public function onActivation()
    {
        $firstActivation = get_option('_asa_first_activation');

        if (empty($firstActivation)) {
            // only on first activation
            update_option('_asa_cache_active', 1);
            update_option('_asa_cache_skip_on_admin', 1);

            update_option('_asa_first_activation', self::VERSION);
        }

        // on every activation
        require_once dirname(__FILE__) . '/AsaCapabilities.php';
        $caps = new AsaCapabilities();
        $caps->install();
    }

    public static function onUninstall()
    {
        delete_option('_asa_first_activation');
        delete_option('_asa_cache_active');
        delete_option('_asa_cache_skip_on_admin');
        delete_option('_asa_product_preview');
        delete_option('_asa_use_flat_box_default');
        delete_option('_asa_parse_comments');
        delete_option('_asa_async_load');
        delete_option('_asa_ajax_css_ani');
        delete_option('_asa_hide_meta_link');
        delete_option('_asa_use_short_amazon_links');
        delete_option('_asa_use_amazon_price_only');
        delete_option('_asa_debug');
        delete_option('_asa_get_rating_alternative');
        delete_option('_asa_custom_widget_class');
        delete_option('_asa_replace_empty_main_price');
        delete_option('_asa_disable_prefetch');
        delete_option('_asa_error_handling');
        delete_option('_asa_admin_error_frontend');
        delete_option('_asa_use_error_tpl');
        delete_option('_asa_error_email_notification');
        delete_option('_asa_error_email_notification_bridge_page_id');
        delete_option('_asa_cache_lifetime');
        delete_option('_asa_cache_dir');
        delete_option('_asa_cache_active');
        delete_option('_asa_cache_disable_variable_lifetime');
        delete_option('_asa_amazon_api_key');
        delete_option('_asa_amazon_api_secret_key');
        delete_option('_asa_amazon_tracking_id');
        delete_option('_asa_api_connection_type');
        delete_option('_asa_pa_api_version');
        delete_option('_asa_amazon_country_code');
        delete_option('_asa_donated');
        delete_option('_asa_newsletter');

        require_once dirname(__FILE__) . '/AsaCapabilities.php';
        $caps = new AsaCapabilities();
        $caps->uninstall();
    }

    /**
     *
     */
    public function addPluginActionLinks($links)
    {
        $links[] = '<a href="' . get_admin_url(null, 'options-general.php?page=amazonsimpleadmin/amazonsimpleadmin.php') . '">' . __('Settings', 'asa1') . '</a>';
        return $links;
    }

    protected function _initCallback()
    {
        add_action('init', array($this, 'onWpInit'));
    }

    public function onWpInit()
    {
        if (!is_admin() && $this->_isAsync()) {
            // be sure to have jQuery if AJAX mode is active
            wp_enqueue_script('jquery');
        }
    }

    /**
     * Called before installation / upgrade
     * 
     */
    public function onPreInstall()
    {
        try {
            $this->backupTemplates();
        } catch (Exception $e) {
            
        }
    }
    
    /**
     * Called after installation / upgrade
     * 
     */
    public function onPostInstall()
    {
        try {
            $this->restoreTemplates();
        } catch (Exception $e) {
            
        }
    }
    
    /**
     * Backups the template files
     * 
     */
    public function backupTemplates()
    {
        $dirIt = new DirectoryIterator($this->tpl_dir);
        
        $custom_tpl = array();
        foreach ($dirIt as $fileinfo) {
            
            if ($fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }
            $custom_tpl[] = $fileinfo->getFilename();
        }
        
        if (count($custom_tpl) > 0) {
            $backup_destination = $this->_getBackupDestination();
            mkdir($backup_destination);
            
            foreach($custom_tpl as $tpl_file) {
                
                $tpl_source_file = $this->tpl_dir . $tpl_file;
                $tpl_destination_file = $backup_destination . $tpl_file;
                
                $cp = copy($tpl_source_file, $tpl_destination_file);
                
                if ($cp == false) {
                    $tpl_data = file_get_contents($tpl_source_file);
                    $handle   = fopen($tpl_destination_file, 'w');
                    fwrite($handle, $tpl_data);
                    fclose($handle);
                }                
            }
        }
    }
    
    /**
     * Restores the template files
     * 
     */
    public function restoreTemplates()
    {
        $backup_destination = $this->_getBackupDestination();
        
        if (!is_dir($backup_destination)) {
            return false;
        }
        
        $dirIt = new DirectoryIterator($backup_destination);
        
        $custom_tpl = array();
        foreach ($dirIt as $fileinfo) {
            
            if ($fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }
            $custom_tpl[] = $fileinfo->getFilename();
        }
        
        if (count($custom_tpl) > 0) {
            
            foreach($custom_tpl as $tpl_file) {
                
                $tpl_source_file = $backup_destination . $tpl_file;
                $tpl_destination_file = $this->tpl_dir . $tpl_file;
                
                $cp = copy($tpl_source_file, $tpl_destination_file);
                
                if ($cp == false) {
                    $tpl_data = file_get_contents($tpl_source_file);
                    $handle   = fopen($tpl_destination_file, 'w');
                    fwrite($handle, $data);
                    fclose($handle);
                }

                unlink($tpl_source_file);
            }
        }

        rmdir($backup_destination);
    }    
    
    protected function _getBackupDestination()
    {
        $tmp = get_temp_dir() . 'amazonsimpleadmin_tpl_backup' . DIRECTORY_SEPARATOR;
        return $tmp;
    }
    
    /**
     * trys to connect to the amazon webservice
     * @return Asa_Service_Amazon|null
     */
    protected function connect ()
    {
        require_once ASA_LIB_DIR . 'Asa/Service/Amazon.php';
        
        try {
            $amazon = Asa_Service_Amazon::factory(
                $this->_amazon_api_key, 
                $this->_amazon_api_secret_key, 
                $this->amazon_tracking_id, 
                $this->_amazon_country_code,
                $this->_amazon_api_connection_type
            );

            return $amazon;
                
        } catch (Exception $e) {
            if ($this->isDebug() && $this->_debugger != null) {
                $this->_debugger->write($e->getMessage());
            }
            return null;
        }
    }
    
    /**
     * 
     */
    protected function _initCache ()
    {
        if (!$this->isCache()) {
            return null;
        }
        
        try {    
            
            require_once ASA_LIB_DIR . 'AsaZend/Cache.php';
            
            $_asa_cache_dir = get_option('_asa_cache_dir');
            $current_cache_dir = (!empty($_asa_cache_dir) ? $_asa_cache_dir : 'cache');

            $frontendOptions = array(
               'lifetime' => $this->getCacheLifetime(),
               'automatic_serialization' => true
            );
            
            $backendOptions = array(
                'cache_dir' => dirname(__FILE__) . DIRECTORY_SEPARATOR . $current_cache_dir
            );
            
            // getting a AsaZend_Cache_Core object
            $cache = AsaZend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
            return $cache;

       } catch (Exception $e) {
            return null;
       }
    }
    
    /**
     * Determines if cache is activated and cache dir is writable
     * @return bool
     */
    public function isCache()
    {
        if ($this->_isCache === null) {
            $_asa_cache_dir = get_option('_asa_cache_dir');
            $current_cache_dir = (!empty($_asa_cache_dir) ? $_asa_cache_dir : 'cache');

            if (get_option('_asa_cache_active') &&
                is_writable(dirname(__FILE__) . '/' . $current_cache_dir)) {
                $this->_isCache = true;
            } else {
                $this->_isCache = false;
            }
        }

        return $this->_isCache;
    }

    /**
     * @return AsaZend_Cache_Core|AsaZend_Cache_Frontend|AsaZend_Cache_Frontend_File|null
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return int
     */
    public function getCacheLifetime()
    {
        $_asa_cache_lifetime  = get_option('_asa_cache_lifetime');

        $lifetime = !empty($_asa_cache_lifetime) ? $_asa_cache_lifetime : self::CACHE_DEFAULT_LIFETIME;

        return (int)$lifetime;
    }

    /**
     * @return int
     */
    public function getVariantCacheLifetime()
    {
        $lt = $this->getCacheLifetime();
        $variantRange = floor($lt * 0.1);
        return $lt + rand(1, $variantRange);
    }

    /**
     * @return bool
     */
    public function isVariantCacheLifetime()
    {
        $disableVariantLifetime = get_option('_asa_cache_disable_variable_lifetime');
        return empty($disableVariantLifetime);
    }

    /**
     * @return bool
     * @deprecated
     */
    public function isDebug()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isErrorHandling()
    {
        return get_option('_asa_error_handling');
    }

    public function getDebugger()
    {
        return $this->_debugger;
    }

    /**
     * @return void
     */
    protected function _initDebugger()
    {
        require_once ASA_LIB_DIR . 'Asa/Debugger.php';
        try {
            $this->_debugger = Asa_Debugger::factory();
        } catch (Exception $e) {
            $this->_debugger_error = $e->getMessage();
        }
    }
    
    /**
     * action function for above hook
     *
     */
    public function createAdminMenu () 
    {           
        // Add a new submenu under Options:
        add_options_page('Affiliate Simple Assistent', 'Affiliate Simple Assistent (ASA1)', 'manage_options', 'amazonsimpleadmin/amazonsimpleadmin.php', array($this, 'createOptionsPage'));
        add_action('admin_head', array($this, 'getOptionsHead'));
        wp_enqueue_script( 'listman' );
    }
    
    /**
     * creates the AmazonSimpleAdmin admin page
     *
     */
    public function createOptionsPage () 
    {
        echo '<div id="amazonsimpleadmin-general" class="wrap">';
        echo '<h2>Affiliate Simple Assistent (ASA1) '. self::VERSION .'</h2>';

        $this->_displayPreDispatcher($this->task);
        echo wp_kses_post($this->getTabMenu($this->task));
        echo '<div id="asa_content">';
        $this->_displayDispatcher($this->task);
        echo '</div>';
    }
    
    /**
     * 
     */
    protected function getTabMenu ($task)
    {
        $navItemFormat = '<a href="%s" class="nav-tab %s">%s</a>';

        $nav  = '<h2 class="nav-tab-wrapper">';
        if (current_user_can('asa1_edit_setup') || current_user_can('activate_plugins')) {
            $nav .= sprintf($navItemFormat, $this->plugin_url, (in_array($task, array(null, 'checkDonation'))) ? 'nav-tab-active' : '', __('Setup', 'asa1'));
        }
        if (current_user_can('asa1_edit_options') || current_user_can('activate_plugins')) {
            $nav .= sprintf($navItemFormat, $this->plugin_url . '&task=options', (($task == 'options') ? 'nav-tab-active' : ''), __('Options', 'asa1'));
        }
        if (current_user_can('asa1_edit_collections') || current_user_can('activate_plugins')) {
            $nav .= sprintf($navItemFormat, $this->plugin_url . '&task=collections', (($task == 'collections') ? 'nav-tab-active' : ''), __('Collections', 'asa1'));
        }
        if (current_user_can('asa1_edit_cache') || current_user_can('activate_plugins')) {
            $nav .= sprintf($navItemFormat, $this->plugin_url . '&task=cache', (($task == 'cache') ? 'nav-tab-active' : ''), __('Cache', 'asa1'));
        }
        $nav .= sprintf($navItemFormat, $this->plugin_url.'&task=usage', (($task == 'usage') ? 'nav-tab-active' : ''), __('Usage', 'asa1'));
        $nav .= sprintf($navItemFormat, $this->plugin_url.'&task=faq', (($task == 'faq') ? 'nav-tab-active' : ''), __('FAQ', 'asa1'));
        $nav .= sprintf($navItemFormat, $this->plugin_url.'&task=test', (($task == 'test') ? 'nav-tab-active' : ''), __('Test', 'asa1'));
        if ($this->isErrorHandling()) {
            $nav .= sprintf($navItemFormat, $this->plugin_url.'&task=log', (($task == 'log') ? 'nav-tab-active' : ''), __('Log', 'asa1'));
        }
        $nav .= sprintf($navItemFormat, $this->plugin_url.'&task=credits', (($task == 'credits') ? 'nav-tab-active' : ''), __('Credits', 'asa1'));


        $nav .= '</h2><br />';

        return $nav;
    }

    /**
     * @param string $task
     * @return string
     */
    protected function _getSubMenu ($task)
    {
        $_asa_donated = get_option('_asa_donated');
        
        $nav = '<div style="clear: both"></div>';

        if (empty($task)) {
            ?>
            <div class="asa_info_box asa_info_box_setup">
                <a href="https://getasa2.com/single-image/" target="_blank"><img src="<?php echo asa_plugins_url( 'img/asa2-banner-single-image.png', __FILE__); ?>?v=<?php echo self::VERSION; ?>" width="800" height="120" /></a>
            </div>
            <?php
        }

        if (!$this->isCache()) {
            $nav .= '<div class="error"><p>'. sprintf( __('It is highly recommended to activate the <a href="%s">cache</a>!', 'asa1'), $this->plugin_url .'&task=cache') .'</p></div>';
        }
        if ($this->isDebug()) {
            $nav .= '<div class="asa_box_warning"><p>'. __('Debugging mode is active. Be sure to deactivate it when you do not need it anymore.', 'asa1') .'</p></div>';
        }

        return wp_kses_post( $nav );
    }

    protected function _displayPreDispatcher ($task)
    {
        switch ($task) {
            case 'options':

                if (count($_POST) > 0 && isset($_POST['info_update'])) {

                    if (!wp_verify_nonce($_POST['nonce'], self::NONCE_SAVE_OPTIONS)) {
                        $this->_displayError(__('Invalid access', 'asa1'));
                    } else {
                        $options = array(
                            '_asa_use_flat_box_default',
                            '_asa_parse_comments',
                            '_asa_async_load',
                            '_asa_ajax_css_ani',
                            '_asa_hide_meta_link',
                            '_asa_use_short_amazon_links',
                            '_asa_use_amazon_price_only',
                            '_asa_debug',
                            '_asa_get_rating_alternative',
                            '_asa_custom_widget_class',
                            '_asa_replace_empty_main_price',
                            '_asa_disable_prefetch',
                            '_asa_error_handling',
                            '_asa_admin_error_frontend',
                            '_asa_use_error_tpl',
                            '_asa_error_email_notification',
                            '_asa_error_email_notification_bridge_page_id',
                        );

                        foreach ($options as $opt) {
                            $$opt = isset($_POST[$opt]) ? sanitize_text_field($_POST[$opt]) : null;
                        }

                        update_option('_asa_use_flat_box_default', $_asa_use_flat_box_default);
                        update_option('_asa_parse_comments', $_asa_parse_comments);
                        update_option('_asa_async_load', $_asa_async_load);
                        update_option('_asa_ajax_css_ani', $_asa_ajax_css_ani);
                        update_option('_asa_hide_meta_link', $_asa_hide_meta_link);
                        update_option('_asa_use_short_amazon_links', $_asa_use_short_amazon_links);
                        update_option('_asa_use_amazon_price_only', $_asa_use_amazon_price_only);
                        update_option('_asa_debug', $_asa_debug);
                        update_option('_asa_get_rating_alternative', $_asa_get_rating_alternative);
                        update_option('_asa_custom_widget_class', $_asa_custom_widget_class);
                        update_option('_asa_replace_empty_main_price', $_asa_replace_empty_main_price);
                        update_option('_asa_disable_prefetch', $_asa_disable_prefetch);
                        update_option('_asa_error_handling', $_asa_error_handling);
                        update_option('_asa_admin_error_frontend', $_asa_admin_error_frontend);
                        update_option('_asa_use_error_tpl', $_asa_use_error_tpl);
                        update_option('_asa_error_email_notification', $_asa_error_email_notification);
                        update_option('_asa_error_email_notification_bridge_page_id', $_asa_error_email_notification_bridge_page_id);

                        if ($this->isErrorHandling()) {
                            $this->getLogger()->initTable();
                        }

                        $this->_displaySuccess(__('Settings saved.', 'asa1'));
                    }
                }

                if ($this->isDebug()) {
                    $this->_initDebugger();
                    if (!empty($_POST['_asa_debug_clear'])) {
                        $this->_debugger->clear();
                    }
                }

                break;
        }
    }

    /**
     * @param $task
     */
    protected function _beforeOutput($task)
    {
        switch ($task) {

            case 'collections':

                require_once(dirname(__FILE__) . '/AsaCollection.php');
                $this->collection = new AsaCollection($this->db);

                if (isset($_POST['submit_export_collection'])) {

                    $collection_id = sanitize_text_field($_POST['select_manage_collection']);
                    $collection_label = $this->collection->getLabel($collection_id);

                    if ($collection_label !== null) {
                        $this->collection->export((int)$collection_id, $this->_amazon_country_code);
                    }

                } elseif (isset($_POST['submit_export_all_collections'])) {

                    $collections = $this->collection->getAll();

                    if (!empty($collections)) {
                        $this->collection->export(array_keys($collections), $this->_amazon_country_code);
                    }
                }

                break;
        }
    }
    
    /**
     * the actual options page content
     *
     */
    protected function _displayDispatcher ($task) 
    {
        if (empty($task) && !current_user_can('asa1_edit_setup') && !current_user_can('activate_plugins')) {
            if (current_user_can('asa1_edit_options')) {
                echo '<script>window.location.replace("' . admin_url('options-general.php?page=amazonsimpleadmin/amazonsimpleadmin.php&task=options') . '");</script>';
            } elseif (current_user_can('asa1_edit_collections')) {
                echo '<script>window.location.replace("' . admin_url('options-general.php?page=amazonsimpleadmin/amazonsimpleadmin.php&task=collections') . '");</script>';
            } elseif (current_user_can('asa1_edit_cache')) {
                echo '<script>window.location.replace("' . admin_url('options-general.php?page=amazonsimpleadmin/amazonsimpleadmin.php&task=cache') . '");</script>';
            } else {
                echo '<script>window.location.replace("' . admin_url('options-general.php?page=amazonsimpleadmin/amazonsimpleadmin.php&task=usage') . '");</script>';
            }
        }

        $_asa_donated = get_option('_asa_donated');
        if ($task == 'checkDonation' && empty($_asa_donated)) {
            $this->_checkDonated();
        }
        $_asa_newsletter = get_option('_asa_newsletter');
        if ($task == 'checkNewsletter' && empty($_asa_newsletter)) {
            $this->_checkNewsletter();
        }

        switch ($task) {
                
            case 'collections':
                
                require_once(dirname(__FILE__) . '/AsaCollection.php');
                $this->collection = new AsaCollection($this->db);

                $params = array();
                
                
                
                if (isset($_POST['deleteit_collection_item'])) {

                    /**
                     * Delete collection item(s)
                     */
                    if (!wp_verify_nonce($_POST['nonce'], self::NONCE_MANAGE_COLLECTION)) {
                        $this->error['manage_collection'] = __('Invalid access', 'asa1');
                    } else {
                        // there is no sanitize function for array inputs
                        // please see the sanitize_text_field function inside the foreach loop
                        $delete_items = isset( $_POST['delete_collection_item'] ) ? (array) $_POST['delete_collection_item'] : array();
                        if (count($delete_items) > 0) {
                            foreach ($delete_items as $item) {
                                $this->collection->deleteAsin( sanitize_text_field($item) );
                            }
                        }
                    }
                }
                
                if (isset($_POST['submit_import'])) {

                    /**
                     * Import collection
                     */
                    if (!wp_verify_nonce($_POST['nonce'], self::NONCE_IMPORT_COLLECTION)) {
                        $this->error['submit_new_asin'] = __('Invalid access', 'asa1');
                    } else {

                        require_once(dirname(__FILE__) . '/AsaCollectionImport.php');

                        $file = $_FILES['importfile']['tmp_name'];
                        $import = new AsaCollectionImport($file, $this->collection);
                        $import->import();

                        if ($import->getError() != null) {
                            $this->error['submit_import'] = $import->getError();
                        } else {
                            $importedCollections = $import->getImportedCollections();
                            $this->success['submit_import'] = sprintf(__('Collections imported: %s'), implode(', ', $importedCollections));
                        }
                    }
                }

                if (isset($_POST['submit_new_asin'])) {

                    /**
                     * Add item to collection
                     */

                    if (!wp_verify_nonce($_POST['nonce'], self::NONCE_ADD_ITEM_TO_COLLECTION)) {
                        $this->error['submit_new_asin'] = __('Invalid access', 'asa1');
                    } else {

                        $asin = sanitize_text_field($_POST['new_asin']);
                        $collection_id = sanitize_text_field($_POST['collection']);
                        $item = $this->_getItem($asin);

                        if ($item === null) {
                            // invalid asin
                            $this->error['submit_new_asin'] = __('invalid ASIN', 'asa1');

                        } else if ($this->collection->checkAsin($asin, $collection_id) !== null) {
                            // asin already added to this collection
                            $this->error['submit_new_asin'] = sprintf(
                                __('ASIN already added to collection <strong>%s</strong>', 'asa1'),
                                $this->collection->getLabel($collection_id)
                            );

                        } else {

                            if ($this->collection->addAsin($asin, $collection_id) === true) {
                                $this->success['submit_new_asin'] = sprintf(
                                    __('<strong>%s</strong> added to collection <strong>%s</strong>', 'asa1'),
                                    $item->getTitle(),
                                    $this->collection->getLabel($collection_id)
                                );
                            }
                        }
                    }
                    
                } else if (isset($_POST['submit_manage_collection'])) {
                    
                    $collection_id = sanitize_text_field($_POST['select_manage_collection']);
                    
                    $params['collection_items'] = $this->collection->getItems($collection_id);
                    $params['collection_id']     = $collection_id;

                } else if (isset($_GET['select_manage_collection']) && isset($_GET['update_timestamp'])) {

                    if (!wp_verify_nonce($_GET['nonce'], self::NONCE_UPDATE_COLLECTION_ITEM)) {
                        $this->error['manage_collection'] = __('Invalid access', 'asa1');
                    } else {
                        $item_id = sanitize_text_field($_GET['update_timestamp']);
                        $this->collection->updateItemTimestamp($item_id);

                        $collection_id = sanitize_text_field($_GET['select_manage_collection']);
                        $params['collection_items'] = $this->collection->getItems($collection_id);
                        $params['collection_id']     = $collection_id;
                    }
                    
                } else if (isset($_POST['submit_delete_collection'])) {

                    /**
                     * Delete collection
                     */
                    if (!wp_verify_nonce($_POST['nonce'], self::NONCE_MANAGE_COLLECTION)) {
                        $this->error['manage_collection'] = __('Invalid access', 'asa1');
                    } else {

                        $collection_id = sanitize_text_field($_POST['select_manage_collection']);
                        $collection_label = $this->collection->getLabel($collection_id);

                        if ($collection_label !== null) {
                            $this->collection->delete($collection_id);
                        }

                        $this->success['manage_collection'] = sprintf(
                            __('collection deleted: <strong>%s</strong>', 'asa1'),
                            $collection_label
                        );
                    }

                } else if (isset($_POST['submit_new_collection'])) {

                    /**
                     * Create new collection
                     */

                    if (!wp_verify_nonce($_POST['nonce'], self::NONCE_CREATE_COLLECTION)) {
                        $this->error['submit_new_collection'] = __('Invalid access', 'asa1');
                    } else {
                        $collection_label = str_replace(' ', '_', trim($_POST['new_collection']));
                        $collection_label = preg_replace("/[^a-zA-Z0-9_]+/", "", $collection_label);

                        if (empty($collection_label)) {
                            $this->error['submit_new_collection'] = __('Invalid collection label', 'asa1');
                        } else {
                            if ($this->collection->create($collection_label) == true) {
                                $this->success['submit_new_collection'] = sprintf(
                                    __('New collection <strong>%s</strong> created'),
                                    $collection_label
                                );
                            } else {
                                $this->error['submit_new_collection'] = __('This collection already exists', 'asa1');
                            }
                        }
                    }
                
                } else if (isset($_POST['submit_collection_init']) && 
                    isset($_POST['activate_collections'])) {

                    if (!wp_verify_nonce($_POST['nonce'], self::NONCE_ACTIVATE_COLLECTIONS)) {
                        $this->error['activate_collection'] = __('Invalid access', 'asa1');
                    } else {
                        $this->collection->initDB();
                    }
                }
                
                echo $this->_getSubMenu($task);
                
                if ($this->collection->isEnabled()) {
                    $this->_displayCollectionsPage($params);
                } else {
                    $this->_displayCollectionsSetup();
                }
                break;
                
            case 'usage':

                echo $this->_getSubMenu($task);

                $this->_displayUsagePage();
                break;

            case 'faq':

                echo $this->_getSubMenu($task);

                $this->_displayFaqPage();
                break;

            case 'test':

                echo $this->_getSubMenu($task);

                $this->_displayTestPage();
                break;

            case 'log':

                echo $this->_getSubMenu($task);

                $this->_displayLogPage();
                break;

            case 'credits':

                echo $this->_getSubMenu($task);

                $this->_displayCreditsPage();
                break;
                
            case 'cache':

                if (count($_POST) > 0) {

                    if (!wp_verify_nonce($_POST['nonce'], self::NONCE_SAVE_CACHE_OPTIONS)) {
                        $this->_displayError(__('Invalid access', 'asa1'));
                    } elseif (isset($_POST['clean_cache'])) {

                        if (empty($this->cache)) {
                            $this->error['submit_cache'] = __('Cache not activated!', 'asa1');
                        } else {
                            $this->cache->clean(AsaZend_Cache::CLEANING_MODE_ALL);
                            $this->success['submit_cache'] = __('Cache cleaned up!', 'asa1');
                        }

                    } else {

                        foreach (array(
                                     '_asa_cache_lifetime',
                                     '_asa_cache_dir',
                                     '_asa_cache_active',
                                     '_asa_cache_disable_variable_lifetime',
                                     '_asa_cache_skip_on_admin') as $opt) {
                            $$opt = isset($_POST[$opt]) ? sanitize_text_field($_POST[$opt]) : null;
                        }
                        update_option('_asa_cache_lifetime', intval($_asa_cache_lifetime));
                        update_option('_asa_cache_dir', $_asa_cache_dir);
                        update_option('_asa_cache_active', intval($_asa_cache_active));
                        update_option('_asa_cache_skip_on_admin', intval($_asa_cache_skip_on_admin));
                        update_option('_asa_cache_disable_variable_lifetime', intval($_asa_cache_disable_variable_lifetime));

                        $this->success['submit_cache'] = __('Cache options updated!', 'asa1');
                    }
                }
                
                echo $this->_getSubMenu($task);
                
                $this->_displayCachePage();
                break;

            case 'options':

                echo $this->_getSubMenu($task);

                $this->_displayOptionsPage();
                break;

            default:

                if (count($_POST) > 0 && isset($_POST['setup_update'])) {

                    if (!wp_verify_nonce($_POST['nonce'], self::NONCE_SAVE_SETUP)) {
                        $this->_displayError(__('Invalid access', 'asa1'));
                    } else {
                        $_asa_amazon_api_key = sanitize_text_field($_POST['_asa_amazon_api_key']);
                        $_asa_amazon_api_secret_key = base64_encode(sanitize_text_field($_POST['_asa_amazon_api_secret_key']));
                        $_asa_amazon_tracking_id = sanitize_text_field($_POST['_asa_amazon_tracking_id']);

                        update_option('_asa_amazon_api_key', $_asa_amazon_api_key);
                        update_option('_asa_amazon_api_secret_key', $_asa_amazon_api_secret_key);
                        update_option('_asa_amazon_tracking_id', $_asa_amazon_tracking_id);

                        if (isset($_POST['_asa_amazon_country_code'])) {
                            $_asa_amazon_country_code = sanitize_text_field($_POST['_asa_amazon_country_code']);
                            if (!Asa_Service_Amazon::isSupportedCountryCode($_asa_amazon_country_code)) {
                                $_asa_amazon_country_code = 'US';
                            }
                            update_option('_asa_amazon_country_code', $_asa_amazon_country_code);
                        }

                        $this->_displaySuccess(__('Settings saved.', 'asa1'));
                    }
                }
                
                echo $this->_getSubMenu($task);
                
                $this->_displaySetupPage();
        }
    }
    
    /**
     * check if user wants to hide the donation notice
     */
    protected function _checkDonated () {
        
        if ($_POST['asa_donated'] == '1') {
            update_option('_asa_donated', '1');            
        }
    }

    /**
     * check if user wants to hide the newsletter box
     */
    protected function _checkNewsletter () {

        if ($_POST['asa_check_newsletter'] == '1') {
            update_option('_asa_newsletter', '1');
        }
    }
    
    /**
     * collections asasetup screen
     *
     */
    protected function _displayCollectionsSetup ()
    {
        if (!current_user_can('asa1_edit_collections') && !current_user_can('activate_plugins')) {
            do_action( 'admin_page_access_denied' );
            wp_die( __( 'You do not have sufficient permissions to access this page.' ), 403 );
        }

        ?>
        <div id="asa_collections_setup" class="wrap">
        <fieldset class="options">
        <h2><?php _e('Collections') ?></h2>
        
        <p><?php _e('Do you want to activate the collections feature?', 'asa1'); ?></p>
        <form name="form_collection_init" action="<?php echo esc_attr( $this->plugin_url ) .'&task=collections'; ?>" method="post">
        <label for="activate_collections">yes</label>
        <input type="checkbox" name="activate_collections" id="activate_collections" value="1">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(self::NONCE_ACTIVATE_COLLECTIONS); ?>">
        <p class="submit" style="margin:0; display: inline;">
            <input type="submit" name="submit_collection_init" value="<?php _e('Activate', 'asa1'); ?>" />
        </p>
        </form>
        </fieldset>
        </div>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        
        <?php
    }
    
    /**
     * the actual options page content
     *
     */
    protected function _displayCollectionsPage ($params) 
    {
        if (!current_user_can('asa1_edit_collections') && !current_user_can('activate_plugins')) {
            do_action( 'admin_page_access_denied' );
            wp_die( __( 'You do not have sufficient permissions to access this page.' ), 403 );
        }

        extract($params);
                
        ?>
        <div id="collections_wrap">
        <h2><?php _e('Collections', 'asa1') ?></h2>

            <div class="asa_columns clearfix">
                <div class="asa_content">

                <p><span class="dashicons dashicons-editor-help"></span> <?php printf( __('Check out the <a href="%s" target="_blank">guide</a> if you do not know how to use collections.', 'asa1'), 'https://www.wp-amazon-plugin.com/guide/'); ?></p>

                <h3><?php _e('Create new collection', 'asa1'); ?></h3>
                <?php
                if (isset($this->error['submit_new_collection'])) {
                    $this->_displayError($this->error['submit_new_collection']);
                } else if (isset($this->success['submit_new_collection'])) {
                    $this->_displaySuccess($this->success['submit_new_collection']);
                }
                ?>

                <form name="form_new_collection" action="<?php echo esc_attr( $this->plugin_url ) .'&task=collections'; ?>" method="post">
                    <label for="new_collection">
                        <span class="dashicons dashicons-plus"></span> <?php _e('New collection', 'asa1'); ?>:
                        <input type="text" name="new_collection" id="new_collection" maxlength="190" />
                    </label>
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(self::NONCE_CREATE_COLLECTION); ?>" />

                <p style="margin:0; display: inline;">
                    <input type="submit" name="submit_new_collection" value="<?php _e('Save', 'asa1'); ?>" class="button-primary" />
                </p><br>
                    (<?php _e('Only alpha-numeric characters and underscore allowed', 'asa1'); ?>)
                </form>

                <h3><?php _e('Import collection', 'asa1'); ?></h3>
                <?php
                if (isset($this->error['submit_import'])) {
                    $this->_displayError($this->error['submit_import']);
                } else if (isset($this->success['submit_import'])) {
                    $this->_displaySuccess($this->success['submit_import']);
                }
                ?>
                <form action="<?php echo esc_attr( $this->plugin_url ) .'&task=collections'; ?>" name="form_import_collection" id="form_import_collection" method="post" enctype="multipart/form-data">
                    <label for="importfile"><?php _e('Import file', 'asa1'); ?>:</label>
                    <input type="file" name="importfile" id="importfile" accept="text/xml" />
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(self::NONCE_IMPORT_COLLECTION); ?>" />
                    <input type="submit" name="submit_import" value="<?php _e('Import', 'asa1'); ?>" class="button">
                    <p class="description"><?php _e('Please select a valid .xml file created by the export function.', 'asa1'); ?></p>
                    <br>

                </form>

                <h3><?php _e('Add to collection', 'asa1'); ?></h3>
                <?php
                if (isset($this->error['submit_new_asin'])) {
                    $this->_displayError($this->error['submit_new_asin']);
                } else if (isset($this->success['submit_new_asin'])) {
                    $this->_displaySuccess($this->success['submit_new_asin']);
                }
                ?>
                <form name="form_new_asin" action="<?php echo esc_attr( $this->plugin_url ) .'&task=collections'; ?>" method="post">
                    <label for="new_asin">
                        <span class="dashicons dashicons-plus"></span> <?php _e('Add Amazon item (ASIN)', 'asa1'); ?>:<br>
                        <input type="text" name="new_asin" id="new_asin" placeholder="ASIN" />
                    </label>
<!--                    <br><br>-->
<!--                    <label for="collection">--><?php //_e('to collection', 'asa1'); ?><!--:-->
                    <?php
                    $collection_id = false;
                    if (isset($_POST['collection'])) {
                        $collection_id = trim($_POST['collection']);
                    }
                    echo wp_kses_asa( $this->collection->getSelectField('collection', $collection_id) );
                    ?>
<!--                    </label>-->

                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(self::NONCE_ADD_ITEM_TO_COLLECTION); ?>" />

                    <p style="margin:0; display: inline;">
                        <input type="submit" name="submit_new_asin" value="<?php _e('Save', 'asa1'); ?>" class="button-primary" />
                    </p>
                </form>
            </div>
            <div class="asa_sidebar">
                <?php $this->_displaySidebar(); ?>
            </div>
        </div>

        <div style="clear: both;"></div>
        <a name="manage_collection"></a>
        <h3><?php _e('Manage collections', 'asa1'); ?></h3>
        <?php
        if (isset($this->error['manage_collection'])) {
            $this->_displayError($this->error['manage_collection']);
        } else if (isset($this->success['manage_collection'])) {
            $this->_displaySuccess($this->success['manage_collection']);
        }
        ?>
        <form name="manage_colection" id="manage_colection" action="<?php echo esc_attr( $this->plugin_url ) .'&task=collections'; ?>#manage_collection" method="post">
            <label for="select_manage_collection"><?php _e('Collection', 'asa1'); ?>:</label>

            <?php
            $manage_collection_id = false;
            if (isset($_POST['select_manage_collection'])) {
                $manage_collection_id = trim($_POST['select_manage_collection']);
            }
            echo esc_html_asa( $this->collection->getSelectField('select_manage_collection', $manage_collection_id) );
            ?>

            <p style="margin:0; display: inline;">
                <input type="submit" name="submit_manage_collection" value="<?php _e('Browse', 'asa1'); ?>" class="button-primary" />
            </p>
            <p style="margin:0; display: inline;">
                <input type="submit" name="submit_export_collection" value="<?php _e('Export', 'asa1'); ?>" class="button" />
            </p>
            <p style="margin:0; display: inline;">
                <input type="submit" name="submit_export_all_collections" value="<?php _e('Export all', 'asa1'); ?>" class="button" />
            </p>
            <p style="margin:0; display: inline;">
                <input type="submit" name="submit_delete_collection" value="<?php _e('Delete collection', 'asa1'); ?>" onclick="return asa_deleteCollection();" class="button" />
            </p>
            <p id="asa_collection_shortcode" style="margin:0; display: inline;">
                <?php _e('Shortcode', 'asa1'); ?>: <span class="selectable" style="display: inline-block;"></span>
            </p>
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(self::NONCE_MANAGE_COLLECTION); ?>" />
        </form>

        <?php
        if (isset($collection_items) && !empty($collection_items)) {

            $table = '';
            $table .= '<form id="collection-filter" action="'.$this->plugin_url .'&task=collections" method="post">';
            $table .= '<input type="hidden" name="nonce" value="' . wp_create_nonce(self::NONCE_MANAGE_COLLECTION) . '" />';

            $table .= '<div class="tablenav">
                <div class="alignleft">
                <input type="submit" class="button-secondary delete" name="deleteit_collection_item" value="'. __('Delete selected', 'asa1') .'" onclick="return asa_deleteCollectionItems(\''. __("Delete selected collection items from collection?", "asa1") .'\');"/>
                <input type="hidden" name="submit_manage_collection" value="1" />
                <input type="hidden" name="select_manage_collection" value="'. $collection_id .'" />
                </div>
                <br class="clearfix">
                </div>';

            $table .= '<table class="widefat"><thead><tr>';
            $table .= '<th scope="col" style="text-align: center"><input type="checkbox" onclick="asa_checkAll();"/></th>';
            $table .= '<th scope="col" width="[thumb_width]"></th>';
            $table .= '<th scope="col" width="120">ASIN</th>';
            $table .= '<th scope="col" width="120">'. __('Price', 'asa1') .'</th>';
            $table .= '<th scope="col">'. __('Title', 'asa1') .'</th>';
            $table .= '<th scope="col" width="160">'. __('Timestamp', 'asa1') . '</th>';
            $table .= '<th scope="col"></th>';
            $table .= '</tr></thead>';
            $table .= '<tbody id="the-list">';

            $thumb_max_width = array();

            for ($i=0;$i<count($collection_items);$i++) {

                $row = $collection_items[$i];
                $item = $this->_getItem((string)$row->collection_item_asin);

                if ($item === null) {
                    continue;
                }
                if ($i%2==0) {
                    $tr_class ='';
                } else {
                    $tr_class = ' class="alternate"';
                }

                $title = $item->getTitle();
                if (!empty($title)) {
                    $title = str_replace("'", "\'", $title);
                } else {
                    $title = __('Invalid item', 'asa1');
                }

                $table .= '<tr id="collection_item_'. $row->collection_item_id .'"'.$tr_class.'>';

                $table .= '<th class="check-column" scope="row" style="text-align: center"><input type="checkbox" value="'. $row->collection_item_id .'" name="delete_collection_item[]"/></th>';

                $smallImageUrl = $item->getSmallImageURL();
                if (!empty($smallImageUrl)) {
                    $thumbnail = $smallImageUrl;
                } else {
                    $thumbnail = asa_plugins_url( 'img/no_image.gif', __FILE__ );
                }

                $mainPriceFormatted = $item->getOffersMainPriceFormattedPrice();
                if (empty($mainPriceFormatted)) {
                    $price = '---';
                } else {
                    $price = $mainPriceFormatted;
                }


                $DetailPageURL = $item->getDetailPageURL();
                $table .= '<td width="[thumb_width]"><a href="'. (!empty($DetailPageURL) ? $DetailPageURL : '') .'" target="_blank"><img src="'. $thumbnail .'" /></a></td>';
                $table .= '<td width="120">'. $row->collection_item_asin .'</td>';
                $table .= '<td width="120">'. $price .'</td>';
                $table .= '<td><span id="">'. $title .'</span></td>';
                $table .= '<td width="160">'. date(str_replace(' \<\b\r \/\>', ',', __('Y-m-d \<\b\r \/\> g:i:s a')), $row->timestamp) .'</td>';
                $table .= '<td><a href="'. $this->plugin_url .'&task=collections&update_timestamp='. $row->collection_item_id .'&select_manage_collection='. $manage_collection_id .'&nonce='. wp_create_nonce(self::NONCE_UPDATE_COLLECTION_ITEM).'" class="edit" onclick="return asa_set_latest('. $row->collection_item_id .', \''. sprintf(__('Set timestamp of &quot;%s&quot; to actual time?', 'asa1'), $title) . '\');" title="update timestamp">'. __('latest', 'asa1') .'</a></td>';
                $table .= '</tr>';

                $smallImageWidth = $item->getSmallImageWidth();
                if (!empty($smallImageWidth)) {
                    $thumb_max_width[] = $smallImageWidth;
                }
            }

            rsort($thumb_max_width);

            $table .= '</tbody></table></form>';

            $search = array(
                '/\[thumb_width\]/',
            );

            $replace = array(
                isset($thumb_max_width[0]) ? $thumb_max_width[0] : '',
            );

            echo esc_html_asa( preg_replace($search, $replace, $table) );
            echo '<div id="ajax-response"></div>';

        } else if (isset($collection_id)) {
            echo '<p>' . __('Nothing found. Add some products.', 'asa1') .'</p>';
        }
        ?>

        </div>
        <?php
    }
    
    /**
     * the actual options page content
     *
     */
    protected function _displayUsagePage () 
    {
        ?>
        <div id="usage_wrap">
            <h2><?php _e('Usage', 'asa1') ?></h2>

            <div class="asa_columns clearfix">
                <div class="asa_content">
                    <p><span class="dashicons dashicons-editor-help"></span> <?php printf( __('Please visit the <a href="%s" target="_blank">Usage Guide</a> on the plugin\'s homepage to learn how to use it.', 'asa1'), 'https://www.wp-amazon-plugin.com/usage/' ); ?></a></p>

                    <h3><?php _e('Screencasts', 'asa1'); ?></h3>

                    <div id="asa_screencasts">
                        <dl>
                            <dd><?php _e('Howto: Embed a product', 'asa1'); ?></dd>
                            <dt><a href="https://www.youtube.com/watch?v=oyyNxMlN6lk" target="_blank"><img src="<?php echo asa_plugins_url( 'img/howto_embed_product.png', __FILE__); ?>" width="227" height="57"></a></dt>
                        </dl>
                    </div>

                    <h3><?php _e('Step by Step Guide', 'asa1'); ?></h3>
                    <p><span class="dashicons dashicons-editor-help"></span> <?php printf( __('Please read the <a href="%s" target="_blank">Step by Step Guide</a> if you are new to this plugin.', 'asa1'), 'https://www.wp-amazon-plugin.com/guide/'); ?></p>

                    <h3><?php _e('Available templates', 'asa1'); ?></h3>

                    <p><?php _e('To display a template, use the shortcode option "tpl".', 'asa1'); ?><br>
                        <ul>
                            <li><code>[asa tpl="flat_box_vertical"]ASIN[/asa]</code></li>
                            <li><code>[asa_collection tpl="flat_box_vertical"]collection_name[/asa]</code></li>
                        </ul>
                    </p>
                    <p><?php _e('This is a list of template files, ASA found on your server:', 'asa1') ?></p>
                    <p><span class="dashicons dashicons-editor-help"></span> <?php _e('Please read', 'asa1') ?>: <a href="https://www.wp-amazon-plugin.com/2015/13280/keeping-your-custom-templates-update-safe/" target="_blank">Keeping your custom templates update safe</a></p>
                    <ul id="tpl_list">
                    <?php
                    $templates = $this->getAllTemplates();
                    foreach ($templates as $template) {
                        echo '<li>'. sanitize_text_field( $template ) .'</li>';
                    }
                    ?>
                    </ul>
                </div>
                <div class="asa_sidebar">
                    <?php $this->_displaySidebar(); ?>
                </div>
            </div>
        </div>
        <?php
    }


    /**
     * the actual options page content
     *
     */
    protected function _displayFaqPage ()
    {
        $faqUrl = 'https://www.wp-amazon-plugin.com/faq-remote/';

        $client = new AsaZend_Http_Client($faqUrl);
        $response = $client->request('GET');

        if ($response->isSuccessful()) {
            echo wp_kses_post( $response->getBody() );
        } else {
            echo wp_kses_post( '<p>Could not load FAQ from '. $faqUrl . '</p>' );
        }
    }


    /**
     * the actual options page content
     *
     */
    protected function _displayTestPage ()
    {
        $templates = $this->getAllTemplates();
        $mode = 'tpl';

        if (count($_POST) > 0 && isset($_POST['asin']) && !empty($_POST['asin'])) {

            if (!wp_verify_nonce($_POST['nonce'], self::NONCE_SUBMIT_TEST)) {
                $this->_displayError(__('Invalid access', 'asa1'));
            } else {
                $asin = sanitize_text_field($_POST['asin']);
                if (isset($_POST['tpl'])) {
                    $tpl = sanitize_text_field($_POST['tpl']);
                } else {
                    $tpl = 'demo';
                }
                if (isset($_POST['mode'])) {
                    switch ($_POST['mode']) {
                        case 'ratings':
                            $mode = 'ratings';
                            break;
                        default:
                            $mode = 'tpl';
                    }
                }
                if (isset($_POST['block-log'])) {
                    $blockLog = true;
                }
            }
        }
        ?>

        <h2><?php _e('Test', 'asa1'); ?></h2>

        <div class="asa_columns">
            <div class="asa_content">
                <p><?php printf(__('Insert an ASIN, select a template and press the "%s" button to test the output.', 'asa1'), __('Submit', 'asa1')); ?></p>
                <form method="post" id="asa_test_form">
                    <div class="form-group">
                    <label for="asin">ASIN:</label>
                    <input type="text" name="asin" id="asin" placeholder="ASIN" value="<?php if (isset($asin)): echo esc_attr($asin); endif; ?>">
                </div>
                <div class="form-group">
                    <label for="tpl"><?php _e('Template', 'asa1'); ?>:</label>
                    <select name="tpl" id="tpl">
                        <?php
                        foreach ($templates as $template) {
                            $selected = (isset($tpl) && $template == $tpl) ? 'selected' : '';
                            echo '<option value="'. esc_attr($template) .'" '. $selected .'>'. esc_html($template) .'</li>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <?php _e('Mode', 'asa1'); ?>:
                    <label>
                        <input type="radio" name="mode" id="mode_tpl" value="tpl" <?php echo ($mode == 'tpl') ? 'checked' : ''; ?>>
                        <?php _e('Template', 'asa1'); ?>
                    </label>
                    &nbsp;&nbsp;&nbsp;
                    <label>
                        <input type="radio" name="mode" id="mode_ratings" value="ratings" <?php echo ($mode == 'ratings') ? 'checked' : ''; ?>>
                        <?php _e('Ratings', 'asa1'); ?>
                    </label>
                </div>
                <?php if ($this->isErrorHandling()): ?>
                <div class="form-group">
                    <label>
                    <input type="checkbox" name="block-log" value="1" <?php echo (isset($blockLog)) ? 'checked' : ''; ?>>
                        <?php _e('Disable error log', 'asa1'); ?>
                    </label>
                </div>
                <?php endif; ?>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(self::NONCE_SUBMIT_TEST); ?>">
                <div class="form-group">
                    <input type="submit" name="submit" class="button-primary" value="<?php _e('Submit', 'asa1'); ?>">
                </div>
            </form>
            </div>
            <div class="asa_sidebar">
                <?php //$this->_displaySidebar(); ?>
            </div>
        </div>

        <div class="clearfix"></div>

        <?php

        if (isset($asin) && !empty($asin)) {

            if (isset($blockLog)) {
                $this->getLogger()->setBlock(true);
            }

            echo '<h3>' . __('Result', 'asa1') . ':</h3>';

            if ($mode == 'tpl') {
                if (!isset($tpl) || empty($tpl)) {
                    $tpl = 'demo';
                }
                echo esc_html_asa( $this->getItem($asin, $tpl) );
            } elseif ($mode == 'ratings') {
                $item = $this->_getItem($asin);
                // get the customer rating object
                $customerReviews = $this->getCustomerReviews($item, true);

                if ($customerReviews->isSuccess()) {

                    echo '<p>' . __('Successfully retrieved customer ratings.', 'asa1') . '</p>';
                    echo '<p>' . __('Total reviews:', 'asa1') . ' ' . esc_html( $customerReviews->totalReviews ) . '</p>';
                    echo '<p>' . __('Average rating:', 'asa1') . ' ' . esc_html( $customerReviews->averageRating ) . '</p>';
                    echo '<p>' . __('Image source:', 'asa1') . ' ' . esc_html( $customerReviews->imgSrc ) . '</p>';
                    echo esc_html_asa( $customerReviews->imgTag );


                } else {

                    echo '<p>' . __('Customer ratings could not be retrieved.', 'asa1') . '</p>';
                    echo '<p>Error message: ' . esc_html( $customerReviews->getErrorMessage() ) . '</p>';
                    echo '<pre>';
                }
            }

        } elseif (count($_POST) > 0) {
            _e('Invalid ASIN', 'asa1');
        }
    }

    protected function _displayLogPage()
    {
        require_once dirname(__FILE__) . '/AsaLogListTable.php';

        if (isset($_POST['action']) && $_POST['action'] == 'clear') {
            $this->getLogger()->clear();
            echo '<div class="updated"><p>'. __('All log entries have been deleted.') .'</p></div>';
        }

        $listTable = new AsaLogListTable();
        $listTable->setLogger($this->getLogger());
        $listTable->prepare_items();

        ?>
        <div id="asa_logs" class="wrap">
            <h2><?php _e('Log', 'asa1'); ?></h2>
            <form method="post">
            <?php $listTable->display(); ?>
            </form>
        </div>
        <?php
    }

    protected function _displayCreditsPage()
    {
        ?>
        <div id="asa_logs" class="wrap">
            <h2><?php _e('Credits', 'asa1'); ?></h2>
            <h3><?php _e('Thanks for translations', 'asa1'); ?></h3>
            <ul>
                <li><b>Serbian:</b> Ogi Djuraskovic (<a href="http://firstsiteguide.com/" target="_blank">http://firstsiteguide.com/</a>)</li>
                <li><b>Spanish:</b> Andrew Kurtis (<a href="http://www.webhostinghub.com/" target="_blank">http://www.webhostinghub.com/</a>)</li>
                <li><b>Russian:</b> Ivanka (<a href="http://www.coupofy.com/" target="_blank">http://www.coupofy.com/</a>)</li>
                <li><b>French:</b> Marie-Aude (<a href="http://www.lumieredelune.com/" target="_blank">http://www.lumieredelune.com/</a>)</li>
            </ul>
        </div>
        <?php
    }

    
    /**
     * Load options panel
     *
     */
    protected function _displayOptionsPage()
    {
        if (!current_user_can('asa1_edit_options') && !current_user_can('activate_plugins')) {
            do_action( 'admin_page_access_denied' );
            wp_die( __( 'You do not have sufficient permissions to access this page.' ), 403 );
        }

        $this->_loadOptions();
    ?>
    <h2><?php _e('Options', 'asa1') ?></h2>

    <div class="asa_columns clearfix">
        <div class="asa_content">
            <form method="post">

            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row">
                            <label for="_asa_async_load"><?php _e('Use asynchronous mode (AJAX):', 'asa1') ?></label><br>
                        </th>
                        <td>
                            <input type="checkbox" name="_asa_async_load" id="_asa_async_load" value="1"<?php echo (($this->_async_load == true) ? 'checked="checked"' : '') ?> />
                            <p class="description"><?php _e('Requests to the Amazon webservice will be executed asynchronously. This will improve page load speed.', 'asa1'); ?><br>
                            <?php _e('Activate this only if you have problems with the loading time of your pages in standard mode with activated cache.', 'asa1'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="_asa_async_load"><?php _e('AJAX CSS loading animation:', 'asa1') ?></label><br>
                        </th>
                        <td>
                            <select name="_asa_ajax_css_ani" id="_asa_ajax_css_ani">
                                <option value="0"><?php _e('None', 'asa1'); ?></option>
                                <option value="fb_blocks" <?php echo ((get_option('_asa_ajax_css_ani') == 'fb_blocks') ? 'selected="selected"' : '') ?>><?php _e('Facebook style blocks', 'asa1'); ?></option>
                                <option value="3_circles" <?php echo ((get_option('_asa_ajax_css_ani') == '3_circles') ? 'selected="selected"' : '') ?>><?php _e('3 circles', 'asa1'); ?></option>
                                <option value="floating_bars" <?php echo ((get_option('_asa_ajax_css_ani') == 'floating_bars') ? 'selected="selected"' : '') ?>><?php _e('Floating bars', 'asa1'); ?></option>
                                <option value="circular" <?php echo ((get_option('_asa_ajax_css_ani') == 'circular') ? 'selected="selected"' : '') ?>><?php _e('Circular', 'asa1'); ?></option>
                            </select>
                            <p class="description"><?php _e('Displays an animation until the products are loaded via Ajax.', 'asa1'); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <label for="_asa_use_flat_box_default"><?php _e('Use Flat_box as default template:', 'asa1') ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="_asa_use_flat_box_default" id="_asa_use_flat_box_default" value="1"<?php echo (($this->_asa_use_flat_box_default == true) ? 'checked="checked"' : '') ?> />
                            <p class="description"><?php _e('Use template flat_box_horizontal (since version 1.2 late 2017) as default template.', 'asa1'); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <label for="_asa_parse_comments"><?php _e('Parse comments:', 'asa1') ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="_asa_parse_comments" id="_asa_parse_comments" value="1"<?php echo (($this->_parse_comments == true) ? 'checked="checked"' : '') ?> />
                            <p class="description"><?php _e('[asa] tags in comments will be parsed.', 'asa1'); ?><br><b><?php _e('Note', 'asa1'); ?></b>: <?php _e('ASA2 can replace foreign tracking IDs in URLs with your own.', 'asa1'); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <label for="_asa_hide_meta_link"><?php _e('Hide ASA link:', 'asa1') ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="_asa_hide_meta_link" id="_asa_hide_meta_link" value="1"<?php echo ((get_option('_asa_hide_meta_link') == true) ? 'checked="checked"' : '') ?> />
                            <p class="description"><?php _e('Hides link to ASA homepage from Meta widget. Do not hide it to support this plugin.', 'asa1'); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <label for="_asa_use_short_amazon_links"><?php _e('Use short Amazon links:', 'asa1') ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="_asa_use_short_amazon_links" id="_asa_use_short_amazon_links" value="1"<?php echo ((get_option('_asa_use_short_amazon_links') == true) ? 'checked="checked"' : '') ?> />
                            <p class="description"><?php printf( __('Activates the short version of affiliate links like %s', 'asa1'), 'https://www.amazon.com/exec/obidos/ASIN/123456789/trackingid-12' ); ?><br>
                                <b><?php _e('Note', 'asa1'); ?></b>: <?php _e('ASA2 supports different URL formats including amzn.to', 'asa1'); ?></p>
                        </td>
                    </tr>

<!--                    <tr valign="top">-->
<!--                        <th scope="row">-->
<!--                            <label for="_asa_debug">--><?php //_e('Activate debugging:', 'asa1') ?><!--<br>-->
<!--                                (--><?php //_e('Currently not supported for PA API 5.0', 'asa1') ?><!--)</label>-->
<!--                        </th>-->
<!--                        <td>-->
<!--                            <input type="checkbox" name="_asa_debug" id="_asa_debug" value="1"--><?php //echo ((get_option('_asa_debug') == true) ? 'checked="checked"' : '') ?><!-- />-->
<!--                            <p class="description">--><?php //printf( __('Important: Use debugging only temporarily if you are facing problems with ASA. Ask the <a href="%s" target="_blank">support</a> how to interpret the debugging information.', 'asa1'), 'https://www.wp-amazon-plugin.com/contact/' ); ?><!--</p>-->
<!--                            --><?php //if ($this->isDebug()): ?>
<!--                            --><?php //if ($this->_debugger_error != null): ?>
<!--                                <p><b>--><?php //_e('Debugger error', 'asa1'); ?><!--: </b>--><?php //echo esc_html( $this->_debugger_error ); ?><!--</p>-->
<!--                                --><?php //else:?><!--<br>-->
<!--                                --><?php //_e('Debug output', 'asa1'); ?><!--: (--><?php //if ($this->_debugger instanceof Asa_Debugger && $this->_debugger->getWriter() instanceof Asa_Debugger_Writer_File) echo __('from', 'asa1') . ': ' . esc_html( $this->_debugger->getWriter()->getFilename() ); ?><!-- <small><a href="--><?php //echo esc_attr( $this->plugin_url ); ?><!--&task=options">--><?php //_e('Refresh', 'asa1'); ?><!--</a></small>)-->
<!--                                <br />-->
<!--                                <div id="debug_contents">--><?php //if (!empty($this->_debugger)) echo nl2br(esc_html($this->_debugger->read())); ?><!--</div>-->
<!--                                <br />-->
<!--                                <input type="checkbox" name="_asa_debug_clear" id="_asa_debug_clear" value="1" /><label for="_asa_debug_clear">--><?php //_e('Clear debugging data', 'asa1') ?><!--</label>-->
<!--                                --><?php //endif; ?>
<!--                            --><?php //endif; ?>
<!--                        </td>-->
<!--                    </tr>-->
                    <tr valign="top">
                        <th scope="row">
                            <label for="_asa_get_rating_alternative"><?php _e('Ratings parser alternative:', 'asa1') ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="_asa_get_rating_alternative" id="_asa_get_rating_alternative" value="1"<?php echo ((get_option('_asa_get_rating_alternative') == true) ? 'checked="checked"' : '') ?> />
                            <p class="description"><?php _e('Try this option if you have problems with loading the product ratings', 'asa1'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="_asa_custom_widget_class"><?php _e('Custom widget class:', 'asa1') ?></label>
                        </th>
                        <td>
                            <input type="text" name="_asa_custom_widget_class" id="_asa_custom_widget_class" value="<?php echo (get_option('_asa_custom_widget_class')) != '' ? get_option('_asa_custom_widget_class') : ''; ?>" />
                            <p class="description"><?php _e('Set a custom CSS class for the outer widget container. Default is "AmazonSimpleAdmin_widget" which may get blocked by AdBlockers.', 'asa1'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="_asa_replace_empty_main_price"><?php _e('Empty main price text:', 'asa1') ?></label>
                        </th>
                        <td>
                            <input type="text" name="_asa_replace_empty_main_price" id="_asa_replace_empty_main_price" value="<?php echo (get_option('_asa_replace_empty_main_price')) != '' ? get_option('_asa_replace_empty_main_price') : ''; ?>" />
                            <p class="description"><?php _e('Enter a text which should be displayed for placeholder {$OffersMainPriceFormattedPrice} if the main price is empty. Default is "--".', 'asa1'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="_asa_disable_prefetch"><?php _e('Disable ASIN Prefetch:', 'asa1') ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="_asa_disable_prefetch" id="_asa_disable_prefetch" value="1"<?php echo ((get_option('_asa_disable_prefetch') == true) ? 'checked="checked"' : '') ?> />
                            <p class="description"><?php _e('Turns off ASA\'s Prefetch function, which tries to collect all ASINs on one page and thereby send as few requests as possible to the Amazon API by using batch lookups.', 'asa1'); ?><br><?php _e('Only turn it off if you experience problems loading the products.', 'asa1'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2"><h3><?php _e('Error handling', 'asa1'); ?></h3></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="_asa_error_handling"><?php _e('Error handling:', 'asa1') ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="_asa_error_handling" id="_asa_error_handling" value="1"<?php echo ((get_option('_asa_error_handling') == true) ? 'checked="checked"' : '') ?> />
                            <p class="description"><?php _e('Activates the error handling. Generates log entries e.g. when using invalid ASINs (see tab "Log"). Precondition for all following options.', 'asa1'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="_asa_admin_error_frontend"><?php _e('Admin front-end errors:', 'asa1') ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="_asa_admin_error_frontend" id="_asa_admin_error_frontend" value="1"<?php echo ((get_option('_asa_admin_error_frontend') == true) ? 'checked="checked"' : '') ?> />
                            <p class="description"><?php _e('If an error occures while loading the products, display the error messages instead of an empty product box in the front-end for logged in admins only. Template file <b>error_admin.htm</b> will be used.', 'asa1'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="_asa_use_error_tpl"><?php _e('Error template:', 'asa1') ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="_asa_use_error_tpl" id="_asa_use_error_tpl" value="1"<?php echo ((get_option('_asa_use_error_tpl') == true) ? 'checked="checked"' : '') ?> />
                            <p class="description"><?php _e('If an error occures while loading a product, display the error template instead of an empty product box. Template file <b>error.htm</b> will be used. ', 'asa1'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="_asa_error_email_notification"><?php _e('Email notification:', 'asa1') ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="_asa_error_email_notification" id="_asa_error_email_notification" value="1"<?php echo ((get_option('_asa_error_email_notification') == true) ? 'checked="checked"' : '') ?> />
                            <p class="description"><?php _e('Enables the email notification feature. Enables you to receive notifications about product parsing errors with the same information like in the log entries (invalid ASINs and location where it is used).', 'asa1'); ?><br>
                            <?php printf(__('Read the <a href="%s" target="_blank">documentation</a> about how to setup this feature. <b>Error handling must be activated.</b>', 'asa1'), 'https://www.wp-amazon-plugin.com/email-notification-feature/'); ?>
                            </p>
                        </td>
                    </tr>

                </tbody>
            </table>

            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(self::NONCE_SAVE_OPTIONS); ?>">

            <p class="submit">
                <input type="submit" name="info_update" class="button-primary" value="<?php _e('Update Options', 'asa1') ?> &raquo;" />
            </p>
            </form>
        </div>
        <div class="asa_sidebar">
            <?php $this->_displaySidebar(); ?>
        </div>
    </div>

    <?php
    }

    protected function _displaySidebar()
    {
        ?>
        <div class="asa_widget" id="asa2_widget">
            <h3>Get ASA2!</h3>

        <?php if (false && $this->task != 'collections'): ?>
            <div class="asa_widget_inner">
                <div style="text-align: center;">
                    <img src="<?php echo asa_plugins_url('img/empty_stars.png', __FILE__); ?>" width="260" style="width: 130px;">
                </div>
                <p><?php _e('Having issues with <b>empty ratings stars</b>?', 'asa1'); ?><br>
                    <?php printf(__('Check out ASA2\'s <a%s>advanced ratings mode</a>.', 'asa1'),
                        ' href="https://docs.getasa2.com/ratings.html#advanced-ratings-mode" target="_blank"'); ?>
                </p>
                <a href="https://docs.getasa2.com/ratings.html#advanced-ratings-mode" target="_blank"><img src="<?php echo asa_plugins_url('img/adv_ratings.png', __FILE__); ?>" width="275" style="width: 100%;"></a>
            </div>
        <?php endif; ?>

            <div class="asa_widget_inner">
                <a class="premiumad-button" href="https://getasa2.com/" target="_blank"><?php _e('Go Pro with ASA2', 'asa1'); ?></a>

                <?php if ($this->task == 'collections'): ?>
                    <p><?php _e('Learn more about Collections in ASA2.', 'asa1'); ?></p>
                    <a href="https://getasa2.com/features/#asa2_collections" target="_blank"><img src="<?php echo asa_plugins_url( 'img/asa2-collections.png', __FILE__); ?>" width="100%"></a>

                <?php elseif ($this->task == 'options' || $this->task == 'test'): ?>
                    <p><?php _e('Watch the ASA2 <b>teaser video</b> on YouTube.', 'asa1'); ?></p>
                    <a href="https://www.youtube.com/watch?v=lhKdLgAPELk" target="_blank"><img src="<?php echo asa_plugins_url( 'img/asa2_teaser_video_thumbnail.png', __FILE__); ?>" width="100%"></a>

                <?php elseif ($this->task == 'cache'): ?>
                    <p><?php printf(__('Learn more about <a%s>ASA2\'s caching strategy</a> in the manual.', 'asa1'), ' href="https://docs.getasa2.com/caching.html" target="_blank"'); ?></p>
                    <a href="https://docs.getasa2.com/caching.html" target="_blank"><img src="<?php echo asa_plugins_url( 'img/asa2-cache.png', __FILE__); ?>" width="100%"></a>

                <?php elseif ($this->task == 'usage'): ?>
                    <p><?php _e('With ASA2 you can create your own templates directly in the admin area.', 'asa1'); ?>
                        <?php _e('It has a powerful syntax that supports conditions, loops, functions and placeholder filters.', 'asa1'); ?>
                        <a href="https://docs.getasa2.com/template_syntax.html" target="_blank"><?php _e('Learn more', 'asa1'); ?></a>
                    </p>

                    <a href="https://docs.getasa2.com/template_syntax.html" target="_blank"><img src="<?php echo asa_plugins_url( 'img/asa2_template_editor.png', __FILE__); ?>" width="100%"></a>

                <?php else: ?>
                    <p><?php _e('ASA2 is <b>backwards compatible</b>.', 'asa1'); ?> <?php printf(__('It comes with a <a%s>Migration Wizard</a> for templates and collections.', 'asa1'), ' href="https://docs.getasa2.com/migration_wizard.html" target="_blank"'); ?></p>

                    <p><span class="dashicons dashicons-book"></span> <a href="https://docs.getasa2.com/kickstarter_guide_for_asa2_switchers.html" target="_blank"><?php _e('Kickstarter Guide for ASA2 Switchers', 'asa1'); ?></a></p>
                    <p><b><?php _e('Just some of ASA2\'s amazing new features:', 'asa1'); ?></b></p>
                    <ul>
                        <li><a href="https://docs.getasa2.com/shops.html#using-external-data" target="_blank">CSV Import</li>
                        <li><a href="https://docs.getasa2.com/shop_data_known_shops_awin.html" target="_blank">Awin.com Support</li>
                        <li><a href="https://docs.getasa2.com/shops.html" target="_blank">Shops Feature</li>
                        <li><a href="https://docs.getasa2.com/create_amazon_product_without_api.html" target="_blank"><?php _e('Create Amazon prodcuts without PA API', 'asa1'); ?></a></li>
                        <li><a href="https://docs.getasa2.com/managed_templates.html" target="_blank"></a><?php _e('PA API 5.0 Support', 'asa1'); ?></li>
                        <li><a href="https://docs.getasa2.com/managed_templates.html" target="_blank"><?php _e('Managed Templates', 'asa1'); ?></a></li>
                        <li><?php _e('Customizable Templates (without programming skills)', 'asa1'); ?><br>
                            <?php printf(__('Visit the <a%s>Templates Demo Page</a>.', 'asa1'), ' href="https://www.asa2-demo.de/templates/" target="_blank"'); ?>
                            </li>
                        <li><a href="https://www.youtube.com/watch?v=Bi_KAqCqgks" target="_blank"><?php _e('Product Picker', 'asa1'); ?></a> (<?php _e('Editor button', 'asa1'); ?>)</li>
                        <li><a href="https://docs.getasa2.com/product_picker.html#single-image" target="_blank"><?php _e('Single Image', 'asa1'); ?></a> (<?php _e('Select one from all available images', 'asa1'); ?>)</li>
                        <li><a href="https://docs.getasa2.com/ratings.html" target="_blank"><?php _e('Advanced Ratings Handling', 'asa1'); ?></a></li>
                        <li><a href="https://docs.getasa2.com/repo.html" target="_blank"><?php _e('Product Repository', 'asa1'); ?></a> (<?php _e('speed up your site!', 'asa1'); ?>)</li>
                        <li><?php _e('Parallel use of all Amazon stores', 'asa1'); ?></li>
                        <li><a href="https://docs.getasa2.com/setup.html#associate-id-sets" target="_blank"><?php _e('Manage multiple Associate IDs in sets', 'asa1'); ?></a></li>
                        <li><a href="https://docs.getasa2.com/template_syntax.html" target="_blank"><?php _e('Powerful template syntax', 'asa1'); ?></a> (<?php _e('Conditions', 'asa1'); ?>...)</li>
                        <li><a href="https://docs.getasa2.com/internationalization.html" target="_blank"><?php _e('Internationalization', 'asa1'); ?></a> (<?php _e('Geolocation', 'asa1'); ?>)</li>
                        <li><a href="https://docs.getasa2.com/notifications.html" target="_blank"><?php _e('Email notifications', 'asa1'); ?></a></li>
                        <li><a href="https://docs.getasa2.com/templates_translation.html" target="_blank"><?php _e('Translated Templates', 'asa1'); ?></a></li>
                        <li><a href="https://docs.getasa2.com/caching.html" target="_blank"><?php _e('Multiple caches', 'asa1'); ?></a></li>
                        <li><a href="https://docs.getasa2.com/cronjobs.html" target="_blank"><?php _e('Cronjobs', 'asa1'); ?></a></li>
                        <li><a href="https://docs.getasa2.com/ratings.html" target="_blank"><?php _e('Advanced Ratings handling', 'asa1'); ?></a></li>
                        <li><?php _e('HTTPS ready', 'asa1'); ?></li>
                        <li><?php _e('SEO ready', 'asa1'); ?></li>
                        <li><?php _e('AMP ready', 'asa1'); ?> (Accelerated Mobile Pages)</li>
                        <li><span class="dashicons dashicons-book"></span> <a href="https://docs.getasa2.com/" target="_blank"><?php _e('Extensive documentation', 'asa1'); ?></a></li>
                    </ul>
                    <p><a href="https://getasa2.com/features/" target="_blank"><?php _e('See detailed list of features', 'asa1'); ?></a></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($this->task == null): ?>
        <div class="asa_widget" id="asa_connect">
            <h3>Connect</h3>
            <div class="asa_widget_inner">
                <p><span class="dashicons dashicons-facebook"></span> <a href="https://www.facebook.com/wpasa2/" target="_blank">ASA on Facebook</a></p>
                <p><span class="dashicons dashicons-twitter"></span> <a href="https://twitter.com/ifeelwebde" target="_blank">Timo on Twitter</a></p>
                <p><span class="dashicons dashicons-video-alt3"></span> <a href="https://www.youtube.com/channel/UCjOu3RexM9F4ZEGvWCYMPRg" target="_blank">ASA1 YouTube Channel</a></p>
                <p><span class="dashicons dashicons-video-alt3"></span> <a href="https://www.youtube.com/channel/UCi67kdl2D4hVFNndVEl0uAw" target="_blank">ASA2 YouTube Channel</a></p>
                <p><span class="dashicons dashicons-admin-site"></span> <a href="https://www.wp-amazon-plugin.com/blog/" target="_blank">ASA News</a></p>
                <p><span class="dashicons dashicons-format-chat"></span> <a href="https://www.wp-amazon-plugin.com/contact/" target="_blank"><?php _e('Contact', 'asa'); ?></a></p>
            </div>
        </div>
        <?php endif; ?>

        <?php
        $_asa_newsletter = get_option('_asa_newsletter');
        if (empty($_asa_newsletter) && $this->task != 'collections') :
            ?>

            <div class="asa_widget" id="asa_newsletter">
                <h3><?php _e('Subscribe to the ASA newsletter', 'asa1') ?></h3>
                <div class="asa_widget_inner">
                    <form action="https://wp-amazon-plugin.us7.list-manage.com/subscribe/post?u=a11948220f94721bb8bcddc8b&amp;id=69a6051b59" method="post" target="_blank" novalidate>
                        <div class="mc-field-group">
                            <label for="mce-EMAIL"><?php _e('Email Address (required)', 'asa1'); ?>:</label>
                            <input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL">
                        </div>
                        <input type="submit" value="<?php _e('Subscribe', 'asa1') ?>" name="subscribe" id="mc-embedded-subscribe" class="button">
                    </form>
                    <br>
                    <form action="<?php echo esc_attr( $this->plugin_url ); ?>&task=checkNewsletter" method="post">
                        <label for="asa_check_newsletter">
                            <input type="checkbox" name="asa_check_newsletter" id="asa_check_newsletter" value="1" />
                            <?php _e('I subscribed to the ASA newsletter already. Please hide this box.', 'asa1'); ?>
                        </label>
                        <input type="submit" value="<?php _e('Hide', 'asa1'); ?>" class="button" />
                    </form>
                </div>
            </div>

            <?php
        endif;
        ?>
        <?php
    }

    /**
     * Tests connection
     *
     * @return array
     */
    public function testConnection()
    {
        $success = false;
        $message = '';

        if (!empty($this->_amazon_api_key) && !empty($this->_amazon_api_secret_key)) {
            try {
                $this->amazon = $this->connect();
                if ($this->amazon != null) {
                    $this->amazon->testConnection();
                    $success = true;
                } else {
                    $message = __('Connection to Amazon Webservice failed. Please check the mandatory data.', 'asa1');
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
            }
        }

        return array('success' => $success, 'message' => $message);
    }

    /**
     * Retrieves connections status
     *
     * @return bool
     */
    public function getConnectionStatus()
    {
        $result = $this->testConnection();
        return $result['success'] === true;
    }

    /**
     * Loads setup panel
     *
     */
    protected function _displaySetupPage ()
    {
        if (!current_user_can('asa1_edit_setup') && !current_user_can('activate_plugins')) {
            do_action( 'admin_page_access_denied' );
            wp_die( __( 'You do not have sufficient permissions to access this page.' ), 403 );
        }

        if (!empty($_GET['setup_reset']) && wp_verify_nonce($_GET['nonce'], 'asa-setup-reset')) {
            Asa_Service_Amazon::resetCredentials();
            header('Location: ' . admin_url('options-general.php?page=amazonsimpleadmin/amazonsimpleadmin.php'));
        }

        $_asa_status = false;
        
        $this->_getAmazonUserData();

        $connectionTestResult = $this->testConnection();

        if ($connectionTestResult['success'] === true) {
            $_asa_status = true;
        } else {
            $_asa_error = $connectionTestResult['message'];
        }

        ?>

        <div id="asa_setup">

            <div class="asa_widget">
                <h3><?php _e('Further information', 'asa1'); ?></h3>

                <div class="asa_widget_inner">
                    <span class="dashicons dashicons-info"></span> <a href="<?php echo esc_attr( $this->plugin_url ); ?>&task=faq"><?php _e('FAQ', 'asa1') ?></a> | 
                    <span class="dashicons dashicons-book"></span> <a href="https://www.wp-amazon-plugin.com/documentation/" target="_blank"><?php _e('Online documentation', 'asa1') ?></a> | 
                    <span class="dashicons dashicons-format-chat"></span> <a href="https://www.wp-amazon-plugin.com/forums/" target="_blank"><?php _e('Forums', 'asa1') ?></a> | 
                    <span class="dashicons dashicons-email-alt"></span> <a href="https://www.wp-amazon-plugin.com/contact/" target="_blank"><?php _e('Contact', 'asa1') ?></a> | 
                    <span class="dashicons dashicons-facebook"></span> <a href="https://www.facebook.com/wpasa2/" target="_blank">ASA on Facebook</a> | 
                    <span class="dashicons dashicons-twitter"></span> <a href="https://twitter.com/ifeelwebde" target="_blank">ASA on Twitter</a> | 
                    <span class="dashicons dashicons-video-alt3"></span> <a href="https://www.youtube.com/channel/UCjOu3RexM9F4ZEGvWCYMPRg" target="_blank">ASA1 YouTube Channel</a> |
                    <span class="dashicons dashicons-video-alt3"></span> <a href="https://www.youtube.com/channel/UCi67kdl2D4hVFNndVEl0uAw" target="_blank">ASA2 YouTube Channel</a> |
                    <span class="dashicons dashicons-email-alt"></span> <a href="https://www.wp-amazon-plugin.com/newsletter/" target="_blank"><?php _e('Newsletter', 'asa1') ?></a>
                </div>

                <h3><?php _e('Screencasts', 'asa1'); ?></h3>

                <div id="asa_screencasts">
                    <dl>
                        <dd><?php _e('Howto: Embed a product', 'asa1'); ?></dd>
                        <dt><a href="https://www.youtube.com/watch?v=oyyNxMlN6lk" target="_blank"><img src="<?php echo asa_plugins_url( 'img/howto_embed_product.png', __FILE__); ?>" width="227" height="57"></a></dt>
                    </dl>
                </div>
            </div>

        <div class="asa_columns">
            <div class="asa_content">

                <div class="asa_widget" id="asa_widget_setup">
                    <h3><?php _e('API Setup', 'asa1') ?></h3>

                    <div class="asa_widget_inner">
                        <?php

                        if ($_asa_status == true) {
                            $statusText = __('Connected', 'asa1');
                            $statusClass = 'asa-api-status-connected';
                        } else {
                            $statusText = __('Not connected', 'asa1');
                            $statusClass = 'asa-api-status-not-connected';
                        }

                        if (!empty($_asa_error)) {
                            echo '<div id="message" class="error"><p><strong>'. __('Error', 'asa1') .':</strong> '. esc_html($_asa_error);
                            echo '<br>'. __('Get help at', 'asa1') .' <a href="https://www.wp-amazon-plugin.com/faq/#setup_errors" target="_blank">https://www.wp-amazon-plugin.com/faq/#setup_errors</a></p></div>';
                            echo '<p class="error-message"><strong>'. __('Error', 'asa1') .':</strong> '. esc_html( $_asa_error ) . '</p>';
                        }
                        ?>

                        <p><?php _e('Please fill in your Amazon Product Advertising API credentials.', 'asa1') ?> <?php _e('Fields marked with * are mandatory:', 'asa1') ?></p>

                        <form method="post">
                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row">
                                        <label><?php _e('Status', 'asa1') ?></label>
                                    </th>
                                    <td>
                                        <span class="asa-api-status <?php echo esc_attr($statusClass); ?>" id="api-status"><?php echo esc_html( $statusText ); ?></span>
                                        &nbsp;&nbsp;&nbsp;<?php if ($_asa_status == true): printf(__('Check <a%s>%s</a> to see how ASA works.', 'asa1'), ' href="options-general.php?page=amazonsimpleadmin%2Famazonsimpleadmin.php&task=usage"', __('Usage', 'asa1')); endif; ?>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">
                                        <label for="_asa_amazon_api_key"<?php if (empty($this->_amazon_api_key)) { echo ' class="_asa_error_color"'; } ?>><?php _e('Amazon Access Key ID', 'asa1') ?> *</label><br>
                                        <a href="https://webservices.amazon.com/paapi5/documentation/troubleshooting/sign-up-as-an-associate.html" target="_blank" class="asa_setup_help_link"><?php _e('How do I get one?', 'asa1'); ?></a>
                                    </th>
                                    <td>
                                        <input type="text" name="_asa_amazon_api_key" id="_asa_amazon_api_key" autocomplete="off" value="<?php echo (!empty($this->_amazon_api_key)) ? $this->_amazon_api_key : ''; ?>" />
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">
                                        <label for="_asa_amazon_api_secret_key"<?php if (empty($this->_amazon_api_secret_key)) { echo ' class="_asa_error_color"'; } ?>><?php _e('Secret Access Key', 'asa1'); ?> *</label><br>
                                        <a href="https://www.wp-amazon-plugin.com/register-amazon-affiliate-product-advertising-api/?#13" target="_blank" class="asa_setup_help_link"><?php _e('What is this?', 'asa1'); ?></a>
                                    </th>
                                    <td>
                                        <input type="password" name="_asa_amazon_api_secret_key" id="_asa_amazon_api_secret_key" autocomplete="off" value="<?php echo (!empty($this->_amazon_api_secret_key)) ? $this->_amazon_api_secret_key : ''; ?>" />

                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">
                                        <label for="_asa_amazon_tracking_id"<?php if (empty($this->amazon_tracking_id)) { echo ' class="_asa_error_color"'; } ?>><?php _e('Amazon Tracking ID', 'asa1') ?> *</label><br>
                                        <a href="https://www.wp-amazon-plugin.com/finding-amazon-tracking-id/" target="_blank" class="asa_setup_help_link"><?php _e('Where do I get one?', 'asa1'); ?></a>
                                    </th>
                                    <td>
                                        <input type="text" name="_asa_amazon_tracking_id" id="_asa_amazon_tracking_id" autocomplete="off" value="<?php echo (!empty($this->amazon_tracking_id)) ? $this->amazon_tracking_id : ''; ?>" />
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">
                                        <label for="_asa_amazon_country_code"<?php if (empty($this->_amazon_country_code)) { echo ' class="_asa_status_not_ready"'; } ?>><?php _e('Amazon Country Code', 'asa1') ?> *</label>
                                    </th>
                                    <td>
                                        <select name="_asa_amazon_country_code">
                                            <?php
                                            foreach (Asa_Service_Amazon::getCountryCodes() as $code) {
                                                if ($code == $this->_amazon_country_code) {
                                                    $selected = ' selected="selected"';
                                                } else {
                                                    $selected = '';
                                                }
                                                echo '<option value="'. esc_attr($code) .'"'.$selected.'>' . esc_html($code) . '</option>';
                                            }
                                            ?>
                                        </select> <img src="<?php echo asa_plugins_url( 'img/amazon_'. esc_attr($this->_amazon_country_code) .'_small.gif', __FILE__); ?>" id="selected_store" /> (<?php _e('Default', 'asa1'); ?>: US)
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"><span class="asa_setup_help_text"><span class="dashicons dashicons-info"></span> <?php _e('ASA2 supports use of multiple countries.', 'asa1'); ?> <a href="https://docs.getasa2.com/shortcodes_asa2.html#country-code"><?php _e('Read more', 'asa1'); ?></a></span></td>
                                </tr>


                            </tbody>
                        </table>


                        <p class="submit">
                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(self::NONCE_SAVE_SETUP); ?>">
                        <input type="submit" name="setup_update" class="button-primary" value="<?php _e('Update Options', 'asa1') ?>" />
                        <a href="<?php echo admin_url('options-general.php?page=amazonsimpleadmin/amazonsimpleadmin.php&setup_reset=1&nonce=' . wp_create_nonce('asa-setup-reset')); ?>" id="setup_reset" class="button"><?php _e('Reset', 'asa1') ?></a>
                        <!-- <input type="submit" name="info_clear" value="<?php _e('Clear Settings', 'asa1') ?> &raquo;" /> -->
                            <br /><br />
                            <b><span class="dashicons dashicons-info"></span> <?php _e('Notice', 'asa1'); ?>:</b><br />
                            <?php _e('If your status is ready but your implemented Amazon product boxes do not show any data, check the FAQ panel for more information (first entry).', 'asa1'); ?><br />
                        </p>

                        </form>
                    </div>
                </div>

                <div class="asa_widget">
                    <h3><?php _e('ASA News', 'asa1') ?></h3>
                    <div id="asa_feed_box" class="asa_widget_inner">
                        <img src="/wp-includes/js/tinymce/skins/lightgray/img/loader.gif">
                    </div>
                </div>
            </div>
            <div class="asa_sidebar">
                <?php $this->_displaySidebar(); ?>
            </div>
        </div>
        </div>
        <?php
    }    
    
    /**
     * the cache options page content
     *
     */
    protected function _displayCachePage () 
    {
        if (!current_user_can('asa1_edit_cache') && !current_user_can('activate_plugins')) {
            do_action( 'admin_page_access_denied' );
            wp_die( __( 'You do not have sufficient permissions to access this page.' ), 403 );
        }

        $_asa_cache_lifetime      = get_option('_asa_cache_lifetime');
        $_asa_cache_dir           = get_option('_asa_cache_dir');
        $_asa_cache_active        = get_option('_asa_cache_active');
        $_asa_cache_skip_on_admin = get_option('_asa_cache_skip_on_admin');
        $_asa_cache_disable_variable_lifetime = get_option('_asa_cache_disable_variable_lifetime');
        $current_cache_dir        = (!empty($_asa_cache_dir) ? $_asa_cache_dir : 'cache');

        ?>
        <div id="cache_wrap">
        <h2><?php _e('Cache') ?></h2>

            <div class="asa_columns clearfix">
            <div class="asa_content">
                <form method="post">

            <?php
            if (isset($this->error['submit_cache'])) {
                $this->_displayError($this->error['submit_cache']);
            } else if (isset($this->success['submit_cache'])) {
                $this->_displaySuccess($this->success['submit_cache']);
            }
            ?>

            <p><span class="dashicons dashicons-editor-help"></span> <a href="javascript:void();" onclick="jQuery('#asa-cache-help').toggle();"><?php _e('What is the cache doing?', 'asa1'); ?></a></p>
            <div id="asa-cache-help" style="display: none;">
                <?php _e('The cache stores the result that the Amazon API returns for the configured lifetime. This means that no new requests for a product must be sent to the API within this period.', 'asa1'); ?>
                <?php _e('This saves valuable time when building up your pages.', 'asa1'); ?>
            </div>

            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row">
                        <label for="_asa_cache_active"><?php _e('Activate cache:', 'asa1') ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="_asa_cache_active" id="_asa_cache_active" value="1" <?php echo (!empty($_asa_cache_active)) ? 'checked="checked"' : ''; ?> />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="_asa_cache_skip_on_admin"><?php _e('Do not use cache when logged in as admin:', 'asa1') ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="_asa_cache_skip_on_admin" id="_asa_cache_skip_on_admin" value="1" <?php echo (!empty($_asa_cache_skip_on_admin)) ? 'checked="checked"' : ''; ?> />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="_asa_cache_lifetime"><?php _e('Cache Lifetime (in seconds):', 'asa1') ?></label>
                    </th>
                    <td>
                        <input type="number" name="_asa_cache_lifetime" id="_asa_cache_lifetime" value="<?php echo (!empty($_asa_cache_lifetime)) ? esc_attr($_asa_cache_lifetime) : self::CACHE_DEFAULT_LIFETIME; ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="_asa_cache_skip_on_admin"><?php _e('Disable variable lifetime:', 'asa1') ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="_asa_cache_disable_variable_lifetime" id="_asa_cache_disable_variable_lifetime" value="1" <?php echo (!empty($_asa_cache_disable_variable_lifetime)) ? 'checked="checked"' : ''; ?> /><br>
                        <p class="description"><?php _e('By default, ASA adds a random value of seconds to the cache lifetime of each product (between 0 and 10% of the lifetime value) so that cached products never lose their validity at the same time.', 'asa1'); ?><br><?php _e('Turn it off only if you have problems with the cache.', 'asa1'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="_asa_cache_dir"><?php _e('Cache directory:', 'asa1') ?></label>
                    </th>
                    <td>
                        <input type="text" name="_asa_cache_dir" id="_asa_cache_dir" value="<?php echo esc_attr($current_cache_dir); ?>" /> (<?php _e('within asa plugin directory / default = "cache" / must be <strong>writable</strong>!', 'asa1'); ?>)
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div style="border: 1px solid #EDEDED; padding: 4px; background: #F8F8F8;">
                            <?php
                            echo dirname(__FILE__) . DIRECTORY_SEPARATOR . esc_html($current_cache_dir) . ' ' . __('is', 'asa1') . ' ';
                            if (is_writable(dirname(__FILE__) . '/' . $current_cache_dir)) {
                                echo '<strong style="color:#177B31">'. __('writable', 'asa1') . '</strong>';
                            } else {
                                echo '<strong style="color:#B41216">'. __('not writable', 'asa1') . '</strong>';
                            }
                            ?>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>

            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(self::NONCE_SAVE_CACHE_OPTIONS); ?>"

            <p class="submit">
            <input type="submit" name="info_update" class="button-primary" value="<?php _e('Update Options', 'asa1') ?>" />
            <input type="submit" name="clean_cache" value="<?php _e('Clear Cache', 'asa1') ?>" class="button" />
            </p>

            </form>

            </div>
            <div class="asa_sidebar">
                <?php $this->_displaySidebar(); ?>
            </div>
        </div>
        </div>
        <?php
    }    
    
    /**
     * 
     */
    protected function _displayError ($error) 
    {
        echo '<div class="error"><p>'. __('Error', 'asa1') .': '. esc_html( $error ) .'</p></div>';
    }
    
    /**
     * 
     */
    protected function _displaySuccess ($success) 
    {
        echo '<div class="updated"><p>'. __('Success', 'asa1') .': '. esc_html( $success ) .'</p></div>';
    }    
    
    /**
     * parses post content
     *
     * @param string $content post content
     * @return string parsed content
     * @deprecated
     */
    public function parseContent ($content)
    {
        return $content;
    }

    /**
     * Retrieves default template name
     */
    public function getDefaultTplName()
    {
        if ($this->_asa_use_flat_box_default == true) {
            $tpl_file_name = 'flat_box_horizontal';
        } else {
            $tpl_file_name = 'default';
        }

        return $tpl_file_name;
    }
    
    /**
     * Retrieves all existing template files
     */
    public function getAllTemplates()
    {
        $availableTemplates = array();

        foreach($this->getTplLocations() as $loc) {

            if (!is_dir($loc)) {
                continue;
            }
            $dirIt = new DirectoryIterator($loc);

            foreach ($dirIt as $fileinfo) {

                $filename = $fileinfo->getFilename();

                if ($fileinfo->isDir() || $fileinfo->isDot()) {
                    continue;
                }

                $filePathinfo = pathinfo($filename);

                if (!in_array($filePathinfo['extension'], $this->getTplExtensions())) {
                    continue;
                }

                array_push($availableTemplates, $filePathinfo['filename']);
            }
        }

        $availableTemplates = array_unique($availableTemplates);
        sort($availableTemplates);

        return $availableTemplates;
    }

    /**
     * Retrieves template file to use
     * @param $tpl_file
     * @param bool $default
     * @return bool|string
     */
    public function getTpl($tpl_file, $default = false, $skipCss = false)
    {
        if (!empty($tpl_file)) {

            if (!Asa_Util_Buffer::exists($tpl_file, 'tpl_source')) {
                foreach ($this->getTplLocations() as $loc) {
                    if (!is_dir($loc)) {
                        continue;
                    }
                    foreach ($this->getTplExtensions() as $ext) {
                        $tplPath = $loc . $tpl_file . '.' . $ext;
                        if (file_exists($tplPath)) {

                            if (!Asa_Util_Buffer::exists($tpl_file, 'tpl_css')) {
                                if (strpos($tpl_file, 'flat_box_horizontal') !== false) {
                                    $tplCssPath = $loc . 'flat_box_horizontal.css';
                                } elseif (strpos($tpl_file, 'flat_box_vertical') !== false) {
                                    $tplCssPath = $loc . 'flat_box_vertical.css';
                                } else {
                                    $tplCssPath = $loc . $tpl_file . '.css';
                                }
                                $tplCss = $this->_getTplCss($tplCssPath);
                                if (!empty($tplCss)) {
                                    Asa_Util_Buffer::set($tpl_file, $tplCss, 'tpl_css');
                                }
                            }
                            $tpl = '';
                            $tpl .= asa_compress_html(trim(file_get_contents($tplPath)));
                        }
                    }
                    if (isset($tpl)) {
                        Asa_Util_Buffer::set($tpl_file, $tpl, 'tpl_source');
                        break;
                    }
                }
            }
        }

        if (Asa_Util_Buffer::exists($tpl_file, 'tpl_source')) {
            // template found
            $tpl = '';
            if (!$skipCss && Asa_Util_Buffer::exists($tpl_file, 'tpl_css') && !Asa_Util_Buffer::exists($tpl_file, 'tpl_css_added')) {
                $tpl .= $this->getCssStyleTag(Asa_Util_Buffer::get($tpl_file, 'tpl_css'));
                Asa_Util_Buffer::set($tpl_file, true, 'tpl_css_added');
            }
            $tpl .= Asa_Util_Buffer::get($tpl_file, 'tpl_source');

        } else {

            if ($default === true) {
                // get default template
                $tpl = $this->getTpl($this->getDefaultTplName(), false, $skipCss);
            } elseif (is_string($default)) {
                // take provided string
                $tpl = $default;
            } else {
                $tpl = false;
            }
        }

        return wp_kses_asa( $tpl );
    }

    /**
     * @param $tplCssPath
     * @return mixed|string
     */
    protected function _getTplCss($tplCssPath)
    {
        if (!array_key_exists($tplCssPath, $this->_tplCssBuffer) && file_exists($tplCssPath)) {
            $this->_tplCssBuffer[$tplCssPath] = asa_compress_css(trim(file_get_contents($tplCssPath)));
            return $this->_tplCssBuffer[$tplCssPath];
        }

        return '';
    }

    /**
     * @param $css
     * @return string
     */
    public function getCssStyleTag($css)
    {
        return sprintf('<style type="text/css">%s</style>', $css);
    }

    /**
     * @return mixed|void
     */
    public function getTplLocations()
    {
        $tplLocations = array(
            get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'asa' . DIRECTORY_SEPARATOR,
            dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR,
            dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR . 'built-in' . DIRECTORY_SEPARATOR
        );
        return apply_filters('asa_tpl_locations', $tplLocations);
    }

    /**
     * @return mixed|void
     */
    public function getTplExtensions()
    {
        $tplExtensions = array('htm', 'html');
        return apply_filters('asa_tpl_extensions', $tplExtensions);
    }


    /**
     * parses the chosen template
     *
     * @param $asin
     * @param $tpl
     * @param null $parse_params
     * @param null $tpl_file
     * @internal param \amazon $string asin
     * @internal param \the $string template contents
     *
     * @return     string        the parsed template
     */
    public function parseTpl ($asin, $tpl, $parse_params=null, $tpl_file=null)
    {
        if (($this->_isAsync() && !defined('ASA_ASYNC_REQUEST') && !isset($parse_params['no_ajax'])) ||
            (!$this->_isAsync() && isset($parse_params['force_ajax']))) {
            // on AJAX request
            return $this->_getAsyncContent($asin, $tpl_file, $parse_params);
        }

        // get the item data
        /**
         * @var Asa_Service_Amazon_Item $item
         */
        $item = $this->_getItem($asin);

        if ($item instanceof Asa_Service_Amazon_Item) {

            //$search = $this->_getTplPlaceholders($this->tpl_placeholder, true);

            $alignCss = 'float:left;';
            if (isset($parse_params['align'])) {
                switch ($parse_params['align']) {
                    case 'center':
                        $alignCss = 'float:none;display:block;margin:0 auto 10px;';
                        break;
                    case 'right':
                        $alignCss = 'float:right;margin-left:15px;margin-right:0;';
                        break;
                }
            }


            // get the customer rating object
            $customerReviews = $this->getCustomerReviews($item);



            $lowestOfferPrice = null;

            $LowestUsedPrice = $item->getOffersLowestUsedPriceAmount();
            $LowestNewPrice = $item->getOffersLowestNewPriceAmount();

            if (!empty($LowestUsedPrice) && !empty($LowestNewPrice)) {

                $lowestOfferPrice = ($LowestUsedPrice < $LowestNewPrice) ? $LowestUsedPrice : $LowestNewPrice;
                $lowestOfferCurrency = ($LowestUsedPrice < $LowestNewPrice) ? $item->getOffersLowestUsedPriceCurrencyCode() : $item->getOffersLowestNewPriceCurrencyCode();
                $lowestOfferFormattedPrice = ($LowestUsedPrice < $LowestNewPrice) ? $item->getOffersLowestUsedPriceFormattedPrice() : $item->getOffersLowestNewPriceFormattedPrice();

            } else if (isset($LowestNewPrice)) {

                $lowestOfferPrice          = $LowestNewPrice;
                $lowestOfferCurrency       = $item->getOffersLowestNewPriceCurrencyCode();
                $lowestOfferFormattedPrice = $item->getOffersLowestNewPriceFormattedPrice();

            } else if (!empty($LowestUsedPrice)) {

                $lowestOfferPrice          = $LowestUsedPrice;
                $lowestOfferCurrency       = $item->getOffersLowestUsedPriceCurrencyCode();
                $lowestOfferFormattedPrice = $item->getOffersLowestUsedPriceFormattedPrice();
            }

            $lowestOfferPrice = $this->_formatPrice($lowestOfferPrice);



            $offerMainPriceAmount = $item->getOffersMainPriceAmount();

            if (!empty($offerMainPriceAmount)) {

                $salePriceAmount = $item->getOffersSalePriceAmount();

                if (!empty($salePriceAmount)) {
                    // set main price to sale price
                    $offerMainPriceAmount = $this->_formatPrice((string)$salePriceAmount);
                    $offerMainPriceCurrencyCode = $item->getOffersSalePriceCurrencyCode();
                    $offerMainPriceFormatted = $item->getOffersSalePriceFormattedPrice();
                } else {
                    $offerMainPriceAmount = $this->_formatPrice((string)$offerMainPriceAmount);
                    $offerMainPriceCurrencyCode = $item->getOffersMainPriceCurrencyCode();
                    $offerMainPriceFormatted = $item->getOffersMainPriceFormattedPrice();
                }

            } else {
                // empty main price
                $emptyMainPriceText = get_option('_asa_replace_empty_main_price');
                $offerMainPriceCurrencyCode = '';
                if (!empty($emptyMainPriceText)) {
                    $offerMainPriceFormatted = $emptyMainPriceText;
                    $offerMainPriceAmount = $emptyMainPriceText;
                } else {
                    $offerMainPriceFormatted = '--';
                    $offerMainPriceAmount = '--';
                }
            }

            $totalOffers = 0;
            foreach (array($item->getOffersTotalNew(), $item->getOffersTotalUsed(), $item->getOffersTotalCollectible(), $item->getOffersTotalRefurbished()) as $totalValue) {
                $totalOffers += $totalValue;
            }


            $amazonPrice = $this->getAmazonPrice($item);
            $amazonPriceFormatted = $this->getAmazonPrice($item, true);

            $no_img_url = asa_plugins_url( 'img/no_image.gif', __FILE__ );

            $features = $item->getFeatures();
            if (is_array($features)) {
                $features = sprintf('<ul><li>%s</li></ul>', implode('</li><li>', $features));
            }

            $languages = $item->getLanguages();
            if (is_array($languages)) {
                $langOutput = [];
                foreach ($languages as $lang) {
                    $langOutputTmp = '';
                    if (!empty($lang['Name'])) {
                        $langOutputTmp .= $lang['Name'];
                    }
                    if (!empty($lang['Type'])) {
                        $langOutputTmp .= ' (' . esc_html($lang['Type']) . ')';
                    }
                    array_push($langOutput, $langOutputTmp);
                }
                $languages = sprintf('<ul><li>%s</li></ul>', implode('</li><li>', $langOutput));
            }

            $placeholderStack = array(
                'Features' => $features,
                'ASIN' => $item->getAsin(),
                'SmallImageUrl' => ($item->getSmallImageURL() != null) ? $item->getSmallImageURL() : $no_img_url,
                'SmallImageWidth' => ($item->getSmallImageWidth() != null) ? $item->getSmallImageWidth() : 60,
                'SmallImageHeight' => ($item->getSmallImageHeight() != null) ? $item->getSmallImageHeight() : 60,
                'MediumImageUrl' => ($item->getMediumImageURL() != null) ? $item->getMediumImageURL() : $no_img_url,
                'MediumImageWidth' => ($item->getMediumImageWidth() != null) ? $item->getMediumImageWidth() : 60,
                'MediumImageHeight' => ($item->getMediumImageHeight() != null) ? $item->getMediumImageHeight() : 60,
                'LargeImageUrl' => ($item->getLargeImageURL() != null) ? $item->getLargeImageURL() : $no_img_url,
                'LargeImageWidth' => ($item->getLargeImageWidth() != null) ? $item->getLargeImageWidth() : 60,
                'LargeImageHeight' => ($item->getLargeImageHeight() != null) ? $item->getLargeImageHeight() : 60,
                'Label' => $item->getBrand(),
                'Manufacturer' => $item->getManufacturer(),
                'Studio' => $item->getManufacturer(),
                'Title' => $item->getTitle(),
                'AmazonUrl' => $item->getDetailPageURL(),
                'AmazonLogoSmallUrl' => asa_plugins_url( 'img/amazon_' . (empty($this->_amazon_country_code) ? 'US' : $this->_amazon_country_code) .'_small.gif', __FILE__ ),
                'AmazonLogoLargeUrl' => asa_plugins_url( 'img/amazon_' . (empty($this->_amazon_country_code) ? 'US' : $this->_amazon_country_code) .'.gif', __FILE__ ),
                'DetailPageURL' => $item->getDetailPageURL(),
                'ISBN' => $item->getISBN(),
                'EAN' => $item->getEAN(),
                'NumberOfPages' => $item->getPagesCount(),
                'ReleaseDate' => $this->getLocalizedDate($item->getReleaseDate()),
                'Binding' => $item->getBinding(),
                'Author' => is_array($item->getAuthor()) ? implode(', ', $item->getAuthor()) : $item->getAuthor(),
                'Creator' => $item->getCreator(),
                'Edition' => $item->getEdition(),
                'Director' => $item->getDirector(),
                'Actors' => is_array($item->getActor()) ? implode(', ', $item->getActor()) : $item->getActor(),
                'Format' => $item->getFormat(),
                'CustomRating' => !empty($parse_params['custom_rating']) ? '<img src="' . asa_plugins_url( 'img/stars-'. $parse_params['custom_rating'] .'.gif', __FILE__ ) .'" class="asa_rating_stars" />' : '',
                'ProductDescription' => $item->getEditorialReviews(),
                'AmazonDescription' => $item->getEditorialReviews(),
                'EditorialReview' => $item->getEditorialReviews(),
                'Artist' => $item->getArtist(),
                'Comment' => !empty($parse_params['comment']) ? $parse_params['comment'] : '',
                'TrackingId' => $this->getTrackingId(),
                'AmazonShopURL' => $this->getAmazonShopUrl(),
                'Prime' => $item->getOfferIsPrime() ? 'AmazonPrime' : '',
                'PrimePic' => $item->getOfferIsPrime() ? '<img src="'. asa_plugins_url( 'img/amazon_prime.png', __FILE__ )  .'" class="asa_prime_pic" />' : '',
                'Class' => !empty($parse_params['class']) ? $parse_params['class'] : '',
                'AlignCss' => $alignCss,
                'ProductReviewsURL' => $this->getAmazonShopUrl() . 'product-reviews/' . $item->getAsin() . '/&tag=' . $this->getTrackingId(),
                'ProductGroup' => $item->getProductGroup(),
                'Language' => $languages,

                'AverageRating' => $customerReviews->averageRating,
                'TotalReviews' => ($customerReviews->totalReviews != null) ? $customerReviews->totalReviews : 0,
                'RatingStars' => ($customerReviews->imgTag != null) ? $customerReviews->imgTag : '<img src="'. asa_plugins_url( 'img/stars-0.gif', __FILE__ ) .'" class="asa_rating_stars" />',
                'RatingStarsSrc' => ($customerReviews->imgSrc != null) ? $customerReviews->imgSrc : asa_plugins_url( 'img/stars-0.gif', __FILE__ ),

                'LowestNewPrice' => empty($item->getOffersLowestNewPriceAmount()) ? '---' : $this->_formatPrice($item->getOffersLowestNewPriceAmount()),
                'LowestNewOfferFormattedPrice' => str_replace('$', '\$', $item->getOffersLowestNewPriceFormattedPrice()),
                'LowestUsedPrice' => empty($item->getOffersLowestUsedPriceAmount()) ? '---' : $this->_formatPrice($item->getOffersLowestUsedPriceAmount()),
                'LowestUsedOfferFormattedPrice' => str_replace('$', '\$', $item->getOffersLowestUsedPriceFormattedPrice()),
                'ListPriceFormatted' => empty($item->getOffersListPriceFormattedPrice()) ? '---' : str_replace('$', '\$', $item->getOffersListPriceFormattedPrice()),
                'SalePriceAmount' => !empty($item->getOffersSalePriceAmount()) ? $item->getOffersSalePriceAmount() : '',
                'SalePriceCurrencyCode' => $item->getOffersSalePriceCurrencyCode(),
                'SalePriceFormatted' => $item->getOffersSalePriceFormattedPrice(),
                'PercentageSaved' => $item->getOfferPercentageSaved(),
                'AmazonPrice' => empty($amazonPrice) ? '---' : str_replace('$', '\$', $this->_formatPrice($amazonPrice)),
                'AmazonPriceFormatted' => empty($amazonPriceFormatted) ? '---' : str_replace('$', '\$', $amazonPriceFormatted),
                'OffersMainPriceAmount' => $offerMainPriceAmount,
                'OffersMainPriceCurrencyCode' => $offerMainPriceCurrencyCode,
                'OffersMainPriceFormattedPrice' => $offerMainPriceFormatted,
                'LowestOfferPrice' => empty($lowestOfferPrice) ? '---' : $lowestOfferPrice,
                'LowestOfferCurrency' => isset($lowestOfferCurrency) ? $lowestOfferCurrency : '',
                'LowestOfferFormattedPrice' => isset($lowestOfferFormattedPrice) ? str_replace('$', '\$', $lowestOfferFormattedPrice) : '',

                'Publisher' => '', // deprecated with PA API 5.0
                'Platform' => '', // deprecated with PA API 5.0
                'RunningTime' => '',  // deprecated with PA API 5.0

                'TotalOffers' => empty($totalOffers) ? '0' : $totalOffers,

                'AmazonCurrency' => $item->getOffersMainPriceCurrencyCode(),
                'AmazonAvailability' => $item->getOfferAvailabilityMessage(),

                'text1' => isset($parse_params['text1']) ? $parse_params['text1'] : '',
                'text2' => isset($parse_params['text2']) ? $parse_params['text2'] : '',

            );

            $search = $this->_getTplPlaceholders(array_keys($placeholderStack), true);
            $replace = array_values($placeholderStack);

            $result = preg_replace($search, $replace, $tpl);

            // check for unresolved
            preg_match_all('/\{\$([a-z0-9\-\>]*)\}/i', $result, $matches);
            
            $unresolved = $matches[1];
            
            if (count($unresolved) > 0) {

                $unresolved_names        = $matches[1];
                $unresolved_placeholders = $matches[0];
                
                $unresolved_search  = array();
                $unresolved_replace = array();
                
                
                for ($i=0; $i<count($unresolved_names);$i++) {

                    if (isset($unresolved_names[$i]) && property_exists($item, $unresolved_names[$i])) {
                        $value = $item->{$unresolved_names[$i]};
                    } else {
                        $value = '';
                    }

                    if (strstr($value, '$')) {
                        $value = str_replace('$', '\$', $value);
                    }
                    
                    $unresolved_search[]  = $this->TplPlaceholderToRegex($unresolved_placeholders[$i]);
                    $unresolved_replace[] = $value;                    
                }
                if (count($unresolved_search) > 0) {
                    $result = preg_replace($unresolved_search, $unresolved_replace, $result);
                }
            }

            return do_shortcode($result);

        } elseif ($this->isErrorHandling() && $item instanceof Asa_Service_Amazon_Error &&
            get_option('_asa_admin_error_frontend') && is_super_admin()) {

            // show admin error
            $errors = $item->getErrors();
            $error = array_shift($errors);

            // load error_admin.htm
            $search = $this->_getTplPlaceholders(array('Error', 'Message', 'ASIN'), true);
            $replace = array($error['Code'], $error['Message'], $error['ASIN']);
            $output = preg_replace($search, $replace, $this->getTpl('error_admin'));

            echo esc_html_asa( $output );

        } elseif ($item instanceof Asa_Service_Amazon_Error && get_option('_asa_use_error_tpl')) {

            $errors = $item->getErrors();
            $error = array_shift($errors);

            // load error.htm
            $search = $this->_getTplPlaceholders(array('Error', 'Message', 'ASIN'), true);
            $replace = array($error['Code'], $error['Message'], $error['ASIN']);
            $output = preg_replace($search, $replace, $this->getTpl('error'));

            echo esc_html_asa( $output );

        } elseif ($item === null && $this->isErrorHandling()) {

            // general error
            $message = __('Error while loading product data.', 'asa1');
            $search = $this->_getTplPlaceholders(array('Error', 'Message', 'ASIN'), true);
            $replace = array('General error', $message, $asin);

            if (get_option('_asa_admin_error_frontend') && is_super_admin()) {
                $output = preg_replace($search, $replace, $this->getTpl('error_admin'));
            } else {
                $output = preg_replace($search, $replace, $this->getTpl('error'));
            }

            echo esc_html_asa( $output );

        } else {

            return '';
        }
    }

    /**
     * get item information from amazon webservice or cache
     * 
     * @param string ASIN
     * @return Asa_Service_Amazon_Item
     */
    protected function _getItem ($asin)
    {
        try {
            if (Asa_ItemBuffer::hasItem($asin)) {
                // check for prefetched or cached item
                $item = Asa_ItemBuffer::getItem($asin);

            } else {

                if ($this->cache == null || $this->_useCache() === false) {
                    // if cache could not be initialized
                    $item = $this->getItemLookup($asin);

                } else {

                    // try to load item from cache
                    if (asa_is_pa_api_5()) {
                        require_once __DIR__ . '/lib/Asa/Service/Amazon/Item/PaApi5.php';
                    }
                    $item = $this->cache->load($asin);
                    if ($item === false || !($item instanceof Asa_Service_Amazon_Item)) {
                        // item could not be loaded from cache or is not an item object
                        //asa_debug('could not load from cache: ' . $asin);

                        $item = $this->getItemLookup($asin);

                        if (!($item instanceof Asa_Service_Amazon_Error)) {
                            // put asin in cache if it is not an error response
                            if ($this->isVariantCacheLifetime()) {
                                $this->cache->save($item, $asin, array(), $this->getVariantCacheLifetime());
                            } else {
                                $this->cache->save($item, $asin);
                            }
                        }

                    } else {
                        // asin could be loaded from cache

                        // debug
                        //asa_debug('loaded from cache: ' . $asin);
                        //asa_debug($item);
                    }

                }
            }

            return $item;
            
        } catch (Exception $e) {

            if ($this->isErrorHandling()) {

                $message = "Error while trying to load item data: %s\n\nASIN: %s";
                $error = array();
                $error['Message'] = sprintf($message, $e->getMessage(), $asin);
                $error['ASIN'] = $asin;
                $error['Code'] = 'General error';

                $this->getLogger()->logError($error);
            }

            return null;
        }
    }

    /**
     * Public alias for self::_getItem($asin)
     *
     * @param $asin
     * @return object
     */
    public function getItemObject($asin)
    {
        return $this->_getItem($asin);
    }

    /**
     * get item information from amazon webservice
     *
     * @param       string      ASIN
     * @return      object      AsaZend_Service_Amazon_Item object
     * @throws Asa_Exception
     */
    public function getItemLookup ($asin)
    {
        if (Asa_ItemBuffer::hasItem($asin)) {
            return Asa_ItemBuffer::getItem($asin);
        }

        if ($this->amazon instanceof Asa_Service_Amazon_Interface) {
            $result = $this->amazon->itemLookup($asin, array(
                'ResponseGroup' => 'ItemAttributes,Images,Offers,OfferListings,Reviews,EditorialReview,Tracks'));

            if ($result instanceof Asa_Service_Amazon_Error) {
                // handle errors
                if ($this->isErrorHandling()) {
                    $this->getLogger()->logError($result);
                }
            }

            return $result;

        } else {
            throw new Asa_Exception('Could not connect to the API.');
        }
    }

    /**
     * gets options from database options table
     */
    protected function _getAmazonUserData ()
    {
        $this->_amazon_api_key = get_option('_asa_amazon_api_key');
        $this->_amazon_api_secret_key = base64_decode(get_option('_asa_amazon_api_secret_key'));
        $this->amazon_tracking_id = get_option('_asa_amazon_tracking_id');
        $this->_amazon_api_connection_type = ifw_filter_scalar(get_option('_asa_api_connection_type'), array('http', 'https'), 'http');
        $this->_amazon_pa_api_version = ifw_filter_scalar((int)get_option('_asa_pa_api_version'), array(self::PA_API_4, self::PA_API_5), self::PA_API_5);

        $amazon_country_code = get_option('_asa_amazon_country_code');
        if (!empty($amazon_country_code)) {
            $this->_amazon_country_code = $amazon_country_code;
        }
    }

    /**
     * Loads options
     */
    protected function _loadOptions()
    {
        $_asa_use_flat_box_default = get_option('_asa_use_flat_box_default');
        if (empty($_asa_use_flat_box_default)) {
            $this->_asa_use_flat_box_default = false;
        } else {
            $this->_asa_use_flat_box_default = true;
        }

        $_asa_parse_comments = get_option('_asa_parse_comments');
        if (empty($_asa_parse_comments)) {
            $this->_parse_comments = false;
        } else {
            $this->_parse_comments = true;
        }

        $_asa_async_load = get_option('_asa_async_load');
        if (empty($_asa_async_load)) {
            $this->_async_load = false;
        } else {
            $this->_async_load = true;
        }

        $_asa_use_amazon_price_only = get_option('_asa_use_amazon_price_only');
        if (empty($_asa_use_amazon_price_only)) {
            $this->_asa_use_amazon_price_only = false;
        } else {
            $this->_asa_use_amazon_price_only = true;
        }
    }

    /**
     * generates right placeholder format and returns them as array
     * optionally prepared for use as regex
     *
     * @param         bool        true for regex prepared
     * @return array
     */
    protected function _getTplPlaceholders ($placeholders, $regex=false)
    {
        $result = array();
        foreach ($placeholders as $ph) {
            $result[] = $this->tpl_prefix . $ph . $this->tpl_postfix;
        }
        if ($regex == true) {
            return array_map(array($this, 'TplPlaceholderToRegex'), $result);
        }
        return $result;
    }
    
    /**
     * excapes placeholder for regex usage
     * 
     * @param         string        placehoder
     * @return         string        escaped placeholder
     */
    public function TplPlaceholderToRegex ($ph)
    {
        $search = array(
            '{',
            '}',
            '$',
            '-',
            '>'
        );
        
        $replace = array(
            '\{',
            '\}',
            '\$',
            '\-',
            '\>'
        );
        
        $ph = str_replace($search, $replace, $ph);
        
        return '/'. $ph .'/';
    }
    
    /**
     * formats the price value from amazon webservice
     * 
     * @param         string        price
     * @return         mixed        price (float, int for JP)
     */
    protected function _formatPrice ($price)
    {
        if ($price === null || empty($price)) {
            return $price;
        }
        
        if ($this->_amazon_country_code != 'JP') {
            $price = (float) substr_replace($price, '.', (strlen($price)-2), -2);
        } else {
            $price = intval($price);
        }    
        
        $dec_point         = '.';
        $thousands_sep     = ',';
        
        if ($this->_amazon_country_code == 'DE' ||
            $this->_amazon_country_code == 'FR') {
            // taken the amazon websites as example
            $dec_point         = ',';
            $thousands_sep     = '.';
        }
        
        if ($this->_amazon_country_code != 'JP') {
            $price = number_format($price, 2, $dec_point, $thousands_sep);
        } else {
            $price = number_format($price, 0, $dec_point, $thousands_sep);
        }
        return $price;
    }
    
    /**
     * includes the css file for admin page
     */
    public function getOptionsHead ()
    {
        echo '<link rel="stylesheet" type="text/css" media="screen" href="' . asa_plugins_url( 'css/options.css?v='. self::VERSION , __FILE__ ) .'" />';
    }
    
    /**
     * Adds the meta link
     */
    public function addMetaLink() 
    {
        echo '<li>Powered by <a href="https://www.wp-amazon-plugin.com/" target="_blank" title="Open Affiliate Simple Assistent homepage">Affiliate Simple Assistent</a></li>';
    }    
    
    /**
     * @param $label
     * @param bool $type
     * @param bool $tpl
     * @return string
     */
    public function getCollection ($label, $type=false, $tpl=false)
    {    
        $collection_html = '';
        
        $sql = '
            SELECT a.collection_item_asin as asin
            FROM `'. $this->db->prefix . self::DB_COLL_ITEM .'` a
            INNER JOIN `'. $this->db->prefix . self::DB_COLL .'` b USING(collection_id)
            WHERE b.collection_label = "'. esc_sql($label) .'"
            ORDER by a.collection_item_timestamp DESC
        ';
        
        $result = $this->db->get_results($sql);
        
        if (count($result) == 0) {
            return $collection_html;    
        }
        
        if ($tpl == false) {
            $tpl = 'collection_sidebar_default';    
        }
        if ($type == false) {
            $type = 'all';    
        }

        $tpl_src = $this->getTpl($tpl);
        
        switch ($type) {
            
            case 'latest':
                $collection_html .= $this->parseTpl($result[0]->asin, $tpl_src, null, $tpl);
                break;
            
            case 'all':
            default:
                foreach ($result as $row) {
                    $collection_html .= $this->parseTpl($row->asin, $tpl_src, null, $tpl);
                }
        }
        
        return $collection_html;
    }

    /**
     * @param $asin
     * @param null $tpl
     * @param array $options
     * @return string
     */
    public function getItem ($asin, $tpl = null, $options = array())
    {   
        $item_html = '';
        
        if (empty($tpl)) {
            $tpl = 'sidebar_item';
        }

        $tpl_src = $this->getTpl($tpl);
        
        $item_html .= $this->parseTpl(trim($asin), $tpl_src, $options, $tpl);
        
        return $item_html;
    }

    /**
     * @return bool
     */
    protected function _isAsync()
    {
        return $this->_async_load === true;
    }

    /**
     * @param $asin
     * @param $tpl
     * @param $parse_params
     * @param $match
     * @return string
     */
    protected function _getAsyncContent($asin, $tpl, $parse_params)
    {
        $containerID = 'asa-' . md5(uniqid(mt_rand()));

        if ($this->getLogger()->isBlock()) {
            $parse_params['asa-block-errorlog'] = true;
        }
        if (isset($parse_params['force_ajax'])) {
            unset($parse_params['force_ajax']);
        }

        $params = str_replace("'", "\'", json_encode($parse_params));
        $nonce = wp_create_nonce('amazonsimpleadmin');
        $ajax_url = admin_url( 'admin-ajax.php' );

        if (empty($tpl)) {
            $tpl = $this->getDefaultTplName();
        }

        $output = '';

        $loadingAniHtml = '';
        $loadingAniStyle = get_option('_asa_ajax_css_ani');
        if (!empty($loadingAniStyle)) {
            require_once ASA_LIB_DIR . '/Asa/CssLoading.php';
            $loadingAniHtml = Asa_CssLoading::getInstance()->getHtml($loadingAniStyle);
            $loadingAniCss = Asa_CssLoading::getInstance()->getCss($loadingAniStyle);
            if (!empty($loadingAniCss)) {
                $output .= $this->getCssStyleTag($loadingAniCss);
            }
        }

        $output .= '<div id="'. $containerID .'" class="asa_async_container asa_async_container_'. $tpl .'">'. $loadingAniHtml .'</div>';
        $output .= "<script type='text/javascript'>jQuery(document).ready(function($){var data={action:'asa_async_load',asin:'$asin',tpl:'$tpl',params:'$params',nonce:'$nonce'};if(typeof ajaxurl=='undefined'){var ajaxurl='$ajax_url'}$.post(ajaxurl,data,function(response){jQuery('#$containerID').html(response)})});</script>";
        return $output;
    }

    /**
     * @param Asa_Service_Amazon_Item $item
     * @param bool $formatted
     * @return mixed|null
     */
    public function getAmazonPrice($item, $formatted=false)
    {
        $result = null;

        if ($item instanceof Asa_Service_Amazon_Item) {

            $salePriceAmount = $item->getOffersSalePriceAmount();
            $mainPriceAmount = $item->getOffersMainPriceAmount();
            $lowestNewPriceAmount = $item->getOffersLowestNewPriceAmount();
            $lowestUsedPriceAmount = $item->getOffersLowestUsedPriceAmount();

            if (!empty($salePriceAmount)) {
                if ($formatted) {
                    $result = $item->getOffersSalePriceFormattedPrice();
                } else {
                    $result = $salePriceAmount;
                }
            } elseif (!empty($mainPriceAmount)) {
                if ($formatted) {
                    $result = $item->getOffersMainPriceFormattedPrice();
                } else {
                    $result = $mainPriceAmount;
                }
            } elseif (!empty($lowestNewPriceAmount)) {
                if ($formatted) {
                    $result = $item->getOffersLowestNewPriceFormattedPrice();
                } else {
                    $result = $lowestNewPriceAmount;
                }
            } elseif (!empty($lowestUsedPriceAmount)) {
                if ($formatted) {
                    $result = $item->getOffersLowestUsedPriceFormattedPrice();
                } else {
                    $result = $lowestUsedPriceAmount;
                }
            }

//            if (isset($item->Offers->SalePriceAmount) && $item->Offers->SalePriceAmount != null) {
//                if ($formatted === false) {
//                    $result = $this->_formatPrice($item->Offers->SalePriceAmount);
//                } else {
//                    $result = $item->Offers->SalePriceFormatted;
//                }
//            } elseif (isset($item->Offers->Offers[0]->Price) && $item->Offers->Offers[0]->Price != null) {
//                if ($formatted === false) {
//                    $result = $this->_formatPrice($item->Offers->Offers[0]->Price);
//                } else {
//                    $result = $item->Offers->Offers[0]->FormattedPrice;
//                }
//            } elseif (isset($item->Offers->LowestNewPrice) && !empty($item->Offers->LowestNewPrice)) {
//                if ($formatted === false) {
//                    $result = $this->_formatPrice($item->Offers->LowestNewPrice);
//                } else {
//                    $result = $item->Offers->LowestNewPriceFormattedPrice;
//                }
//            } elseif (isset($item->Offers->LowestUsedPrice) && !empty($item->Offers->LowestUsedPrice)) {
//                if ($formatted === false) {
//                    $result = $this->_formatPrice($item->Offers->LowestUsedPrice);
//                } else {
//                    $result = $item->Offers->LowestUsedPriceFormattedPrice;
//                }
//            }
        }

        return $result;
    }

    /**
     * Retrieve the customer reviews object
     *
     * @param Asa_Service_Amazon_Item $item
     * @param bool $uncached
     * @return AsaCustomerReviews|null
     */
    public function getCustomerReviews($item, $uncached = false)
    {
        require_once(dirname(__FILE__) . '/AsaCustomerReviews.php');

        $iframeUrl = isset($item->CustomerReviewsIFrameURL) ? $item->CustomerReviewsIFrameURL : '';

        if ($uncached) {
            $cache = null;
        } else {
            $cache = $this->cache;
        }

        $reviews = new AsaCustomerReviews($item->getAsin(), $iframeUrl, $cache);
        if (get_option('_asa_get_rating_alternative')) {
            $reviews->setFindMethod(AsaCustomerReviews::FIND_METHOD_DOM);
        }
        $reviews->load();
        return $reviews;
    }

    /**
     * @param Asa_Service_Amazon_Item $item
     * @return string
     */
    public function getItemUrl($item)
    {
        if (get_option('_asa_use_short_amazon_links')) {
            $url = sprintf($this->amazon_url[$this->_amazon_country_code],
                $item->getAsin(), $this->amazon_tracking_id);
        } else {
            $url = $item->getDetailPageURL();
        }

        return $this->_handleItemUrl($url);
    }

    /**
     * @param $url
     * @return string
     */
    protected function _handleItemUrl($url)
    {
        $url = urldecode($url);

        $url = strtr($url, array(
            '%' => '%25'
        ));

        return $url;
    }

    /**
     * @param $date
     * @return bool|string
     */
    public function getLocalizedDate($date)
    {
        if (!empty($date)) {
            $dt = new DateTime($date);

            $format = get_option('date_format');

            $date = date($format, $dt->format('U'));
        }

        return $date;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->_amazon_country_code;
    }

    /**
     * @return mixed
     */
    public function getAmazonShopUrl()
    {
        if ($this->amazon_shop_url == null) {
            $url = $this->amazon_url[$this->getCountryCode()];
            $this->amazon_shop_url = current(explode('exec', $url));
        }
        return $this->amazon_shop_url;
    }

    /**
     * @return mixed
     */
    public function getTrackingId()
    {
        return $this->amazon_tracking_id;
    }

    /**
     * @return bool
     */
    protected function _useCache()
    {
        if ((int)get_option('_asa_cache_skip_on_admin') === 1 && current_user_can('install_plugins')) {
            return false;
        }
        return true;
    }

    /**
     * @return AsaLogger
     */
    public function getLogger()
    {
        require_once dirname(__FILE__) . '/AsaLogger.php';
        return AsaLogger::getInstance($this->db);
    }

    /**
     * @param $plugin_data
     * @param $meta_data
     */
    public function handleUpdateMessage($plugin_data = null, $meta_data = null)
    {
        printf('<div style="border: 1px dashed #C9381A; padding: 4px; margin-top: 5px;"><span class="dashicons dashicons-info" style="color: #C9381A;"></span> %s <a href="https://www.wp-amazon-plugin.com/2015/13280/keeping-your-custom-templates-update-safe/" target="_blank">%s</a>.</div>',
            __('Remember to <b>backup your custom template files</b> before updating!', 'asa1'),
            __('Read more', 'asa1')
        );
    }

    /**
     * @param $options
     * @param string $content
     * @param string $code
     * @return string
     */
    public function handleShortcodeAsa($options, $content = '', $code = '')
    {
        $output = '';
        $asin = $content;

        if (!empty($asin)) {

            $options = array_map('asa_sanitize_shortcode_option_value', asa_var_to_array($options));

            if (isset($options['comment'])) {
                $options['comment'] = html_entity_decode($options['comment']);
            }
            if (isset($options['class'])) {
                $options['class'] = html_entity_decode($options['class']);
            }

            $tplName = asa_get_tpl_name_from_options($options);

            $tplSrc = $this->getTpl($tplName, true);

            $output = $this->parseTpl($asin, $tplSrc, $options, $tplName);
        }

        return $output;
    }

    /**
     * @param $options
     * @param string $content
     * @param string $code
     * @return string
     */
    public function handleShortcodeAsaCollection($options, $content = '', $code = '')
    {
        $output = '';

        require_once(dirname(__FILE__) . '/AsaCollection.php');
        $this->collection = new AsaCollection($this->db);

        $collection_id = $this->collection->getId(trim($content));
        $coll_items = $this->collection->getItems($collection_id);

        if (count($coll_items) == 0) {
            // not items found, return empty output
            return $output;
        }

        $options = array_map('asa_sanitize_shortcode_option_value', asa_var_to_array($options));

        $tplName = asa_get_tpl_name_from_options($options);

        // random
        if (isset($options['type'])) {
            if ($options['type'] == 'random') {
                shuffle($coll_items);
            } elseif ($options['type'] == 'latest') {
                $coll_items = array_slice($coll_items, 0, 1);
            }
        }

        // limit results
        if (isset($options['limit']) && is_numeric($options['limit'])) {
            $limit = (int)$options['limit'];
        } elseif (isset($options['items']) && is_numeric($options['items'])) {
            $limit = (int)$options['items'];
        }

        if (isset($limit) && $limit > 0) {
            $coll_items = array_slice($coll_items, 0, $limit);
        }

        $tplSrc = $this->getTpl($tplName, true, true);
        if (Asa_Util_Buffer::exists($tplName, 'tpl_css')) {
            $output .= $this->getCssStyleTag(Asa_Util_Buffer::get($tplName, 'tpl_css'));
        } elseif (Asa_Util_Buffer::exists($this->getDefaultTplName(), 'tpl_css')) {
            $output .= $this->getCssStyleTag(Asa_Util_Buffer::get($this->getDefaultTplName(), 'tpl_css'));
        }

        $coll_items_counter = 1;
        foreach ($coll_items as $row) {
            $output .= $this->parseTpl($row->collection_item_asin, $tplSrc, $options, $tplName);
            $coll_items_counter++;
            if (isset($coll_items_limit) && $coll_items_counter > $coll_items_limit) {
                break;
            }
        }

        return $output;
    }

    protected function _checkPaApiVersion()
    {
        if (isset($_GET['page']) && strpos($_GET['page'], 'amazonsimpleadmin') === 0 && !asa_is_pa_api_5()) {
            add_action('admin_notices', [$this, 'showPaApi5Notice']);
        }
    }

    public function showPaApi5Notice()
    {
        $class = 'notice notice-error';
        $message =  sprintf('<b>%s!</b> %s ', __('Action required', 'asa1'), __('AmazonSimpleAdmin is not yet set up for PA API 5.0. This is mandatory from March 9, 2020!', 'asa1')) . ' ' .
        sprintf('<a href="%s" target="_blank">%s</a>', 'https://www.wp-amazon-plugin.com/2019/15403/important-upcoming-asa2-update-amazon-pa-api-version-4-will-no-longer-be-supported-from-november-2019/', __('More info', 'asa1')) . '<br>' .
        sprintf(__('If the requirements are met, you can switch to PA API version 5.0 in the <a%s>Setup</a>.', 'asa1'), ' href="'. admin_url('options-general.php?page=amazonsimpleadmin%2Famazonsimpleadmin.php') .'"');

        printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
    }

    public function doCommentShortcode($content)
    {
        $pattern = '/\[asa(?:\s+[^]]*)?\](.*?)\[\/asa\]/s';

        return preg_replace_callback(
            $pattern,
            function ($match) {
                return do_shortcode('[asa' . (isset($match[1]) ? ']' . $match[1] . '[/asa]' : ']'));
            },
            $content
        );
    }

}

global $wpdb;
$asa = new AmazonSimpleAdmin($wpdb);

include_once ASA_INCLUDE_DIR . 'asa_pointers.php';
include_once ASA_INCLUDE_DIR . 'asa_php_functions.php';
include_once ASA_INCLUDE_DIR . 'asa_ajax_callback.php';
include_once ASA_INCLUDE_DIR . 'asa_actions.php';
