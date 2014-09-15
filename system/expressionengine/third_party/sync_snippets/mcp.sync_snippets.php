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
 * Sync Snippets Module Control Panel File
 *
 * @package    ExpressionEngine
 * @subpackage Addons
 * @category   Module
 * @author     Rob Sanchez
 * @link       https://github.com/rsanchez
 */
class Sync_snippets_mcp
{
    public function __construct()
    {
        ee()->load->library('sync_snippets');
    }

	public function index()
	{
        if (isset(ee()->view)) {
            ee()->view->cp_page_title = 'Sync Snippets';
        }

        $right_nav = array();

        $snippets_path = ee()->config->slash_item('snippets_path');
        $global_variables_path = ee()->config->slash_item('global_variables_path');
        $specialty_templates_path = ee()->config->slash_item('specialty_templates_path');

        $output = '';

        if ( ! $snippets_path)
        {
            $output .= '<p class="error">'.lang('missing_snippets_path').'</p>';
        }
        elseif ( ! is_dir($snippets_path) && ! mkdir($snippets_path, 0777))
        {
            $output .= '<p class="error">'.lang('invalid_snippets_path').'</p>';
        }
        else
        {
            $right_nav['sync_snippets'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sync_snippets'.AMP.'method=sync_snippets';
        }

        if ( ! $global_variables_path)
        {
            $output .= '<p class="error">'.lang('missing_global_variables_path').'</p>';
        }
        elseif ( ! is_dir($global_variables_path) && ! mkdir($global_variables_path, 0777))
        {
            $output .= '<p class="error">'.lang('invalid_global_variables_path').'</p>';
        }
        else
        {
            $right_nav['sync_global_variables'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sync_snippets'.AMP.'method=sync_global_variables';
        }

        if ( ! $specialty_templates_path)
        {
            $output .= '<p class="error">'.lang('missing_specialty_templates_path').'</p>';
        }
        elseif ( ! is_dir($specialty_templates_path) && ! mkdir($specialty_templates_path, 0777))
        {
            $output .= '<p class="error">'.lang('invalid_specialty_templates_path').'</p>';
        }
        else
        {
            $right_nav['sync_specialty_templates'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sync_snippets'.AMP.'method=sync_specialty_templates';
        }

        if ($right_nav)
        {
            ee()->cp->set_right_nav($right_nav);

            $output .= '<p>'.lang('use_buttons_to_right').'</p>';
        }

        return $output;
	}

    public function sync_snippets()
    {
        try
        {
            ee()->sync_snippets->sync_snippets();

            ee()->session->set_flashdata('message_success', lang('snippets_synced'));
        }
        catch (Exception $e)
        {
            ee()->session->set_flashdata('message_failure', $e->getMessage());
        }

        ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sync_snippets');
    }

    public function sync_global_variables()
    {
        try
        {
            ee()->sync_snippets->sync_global_variables();

            ee()->session->set_flashdata('message_success', lang('global_variables_synced'));
        }
        catch (Exception $e)
        {
            ee()->session->set_flashdata('message_failure', $e->getMessage());
        }

        ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sync_snippets');
    }

    public function sync_specialty_templates()
    {
        try
        {
            ee()->sync_snippets->sync_specialty_templates();

            ee()->session->set_flashdata('message_success', lang('specialty_templates_synced'));
        }
        catch (Exception $e)
        {
            ee()->session->set_flashdata('message_failure', $e->getMessage());
        }

        ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sync_snippets');
    }
}
/* End of file mcp.sync_snippets.php */
/* Location: /system/expressionengine/third_party/sync_snippets/mcp.sync_snippets.php */