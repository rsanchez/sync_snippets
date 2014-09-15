<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package     ExpressionEngine
 * @author      ExpressionEngine Dev Team
 * @copyright   Copyright (c) 2003 - 2014, EllisLab, Inc.
 * @license     http://expressionengine.com/user_guide/license.html
 * @link        http://expressionengine.com
 * @since       Version 2.0
 * @filesource
 */

/**
 * Sync Snippets Extension
 *
 * @package    ExpressionEngine
 * @subpackage Addons
 * @category   Extension
 * @author     Rob Sanchez
 * @link       https://github.com/rsanchez
 */
class Sync_snippets_ext
{
    public $settings       = array();
    public $description    = 'Sync snippets and global variable files.';
    public $docs_url       = '';
    public $name           = 'Sync Snippets';
    public $settings_exist = 'n';
    public $version        = '1.0.0';

    /**
     * Constructor
     *
     * @param   mixed Settings array or empty string if none exist.
     */
    public function __construct($settings = '')
    {
        $this->settings = $settings;
    }

    /**
     * Activate Extension
     *
     * This function enters the extension into the exp_extensions table
     *
     * @see http://codeigniter.com/user_guide/database/index.html for
     * more information on the db class.
     *
     * @return void
     */
    public function activate_extension()
    {
        // Setup custom settings in this array.
        $this->settings = array();

        ee()->db->insert_batch('extensions', array(
            array(
                'class' => __CLASS__,
                'method' => 'eecli_add_commands',
                'hook' => 'eecli_add_commands',
                'settings' => serialize($this->settings),
                'version' => $this->version,
                'enabled' => 'y',
            ),
            array(
                'class' => __CLASS__,
                'method' => 'eecli_create_snippet',
                'hook' => 'eecli_create_snippet',
                'settings' => serialize($this->settings),
                'version' => $this->version,
                'enabled' => 'y',
            ),
            array(
                'class' => __CLASS__,
                'method' => 'eecli_create_global_variable',
                'hook' => 'eecli_create_global_variable',
                'settings' => serialize($this->settings),
                'version' => $this->version,
                'enabled' => 'y',
            ),
            array(
                'class' => __CLASS__,
                'method' => 'eecli_delete_snippet',
                'hook' => 'eecli_delete_snippet',
                'settings' => serialize($this->settings),
                'version' => $this->version,
                'enabled' => 'y',
            ),
            array(
                'class' => __CLASS__,
                'method' => 'eecli_delete_global_variable',
                'hook' => 'eecli_delete_global_variable',
                'settings' => serialize($this->settings),
                'version' => $this->version,
                'enabled' => 'y',
            ),
        ));
    }
    /**
     * eecli_add_commands Hook
     *
     * @param
     * @return
     */
    public function eecli_add_commands()
    {
        if (ee()->extensions->last_call !== FALSE)
        {
            $commands = ee()->extensions->last_call;
        }

        require_once PATH_THIRD.'sync_snippets/src/SyncSnippetsCommand.php';
        require_once PATH_THIRD.'sync_snippets/src/SyncGlobalVariablesCommand.php';
        require_once PATH_THIRD.'sync_snippets/src/SyncSpecialtyTemplatesCommand.php';

        $commands[] = new SyncSnippetsCommand();
        $commands[] = new SyncGlobalVariablesCommand();
        $commands[] = new SyncSpecialtyTemplatesCommand();

        return $commands;
    }

    public function eecli_delete_snippet($snippet_id, $snippet_name, $site_id, $site_name)
    {
        ee()->load->library('sync_snippets');
        ee()->sync_snippets->delete_snippet_file($site_name, $snippet_name);
    }

    public function eecli_delete_global_variable($variable_id, $variable_name, $site_id, $site_name)
    {
        ee()->load->library('sync_snippets');
        ee()->sync_snippets->delete_global_variable_file($site_name, $variable_name);
    }

    public function eecli_create_snippet($snippet_id, $snippet_name, $snippet_contents, $site_id, $site_name)
    {
        ee()->load->library('sync_snippets');
        ee()->sync_snippets->create_snippet_file($site_name, $snippet_name, $snippet_contents);
    }

    public function eecli_create_global_variable($variable_id, $variable_name, $variable_data, $site_id, $site_name)
    {
        ee()->load->library('sync_snippets');
        ee()->sync_snippets->create_global_variable_file($site_name, $variable_name, $variable_data);
    }

    /**
     * Disable Extension
     *
     * This method removes information from the exp_extensions table
     *
     * @return void
     */
    public function disable_extension()
    {
        ee()->db->delete('extensions', array('class' => __CLASS__));
    }

    /**
     * Update Extension
     *
     * This function performs any necessary db updates when the extension
     * page is visited
     *
     * @return  mixed void on update / false if none
     */
    public function update_extension($current = '')
    {
        if ($current == '' OR $current == $this->version)
        {
            return FALSE;
        }

        ee()->db->update('extensions', array('version' => $this->version), array('class' => __CLASS__));
    }
}

/* End of file ext.sync_snippets.php */
/* Location: /system/expressionengine/third_party/sync_snippets/ext.sync_snippets.php */