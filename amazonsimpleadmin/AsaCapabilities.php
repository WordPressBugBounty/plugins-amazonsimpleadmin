<?php
/**
 * AmazonSimpleAffiliate (ASA1)
 * For more information see http://www.wp-amazon-plugin.com/
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: AsaCapabilities.php 3147741 2024-09-06 18:34:35Z worschtebrot $
 */
class AsaCapabilities
{

    /**
     * @return mixed|void
     */
    private function _getCapabilities()
    {
        return apply_filters('asa2_capabilities', array(
            'core' => array(
                'asa1_edit_setup',
                'asa1_edit_options',
                'asa1_edit_collections',
                'asa1_delete_collections',
                'asa1_edit_cache',
            )
        ));
    }

    /**
     * @return array
     */
    private function _getObsoleteCapabilities()
    {
        return apply_filters('asa1_obsolete_capabilities', array());
    }

    public function install()
    {
        if (did_action('init')) {
            $this->_install();
        } else {
            add_action('init', array($this, '_install'));
        }
    }

    public function _install()
    {
        global $wp_roles;

        if (class_exists('WP_Roles') && ! isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        if (is_object($wp_roles)) {

            // add
            foreach (self::_getCapabilities() as $capGroup) {
                foreach ($capGroup as $cap) {
                    $wp_roles->add_cap('administrator', $cap);
                }
            }

            // remove obsolete caps on all roles
            foreach (self::_getObsoleteCapabilities() as $capGroup) {
                foreach ($capGroup as $cap) {
                    foreach (array_keys($wp_roles->role_names) as $role) {
                        $wp_roles->remove_cap($role, $cap);
                    }
                }
            }
        }
    }

    /**
     * Uninstall all ASA 2 capabilities on plugin delete
     */
    public function uninstall()
    {
        global $wp_roles;

        if (class_exists('WP_Roles') && ! isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        if (is_object($wp_roles)) {

            $capabilities = self::_getCapabilities();

            foreach ($capabilities as $capGroup) {
                foreach ($capGroup as $cap) {
                    $wp_roles->remove_cap('administrator', $cap);
                }
            }
        }
    }
}
