<?php

class Sync_snippets
{
    protected $snippets_path;

    protected $global_variables_path;

    public function __construct()
    {
        ee()->lang->loadfile('sync_snippets', 'sync_snippets');

        $this->snippets_path = ee()->config->slash_item('snippets_path');

        $this->global_variables_path = ee()->config->slash_item('global_variables_path');

        $this->specialty_templates_path = ee()->config->slash_item('specialty_templates_path');
    }

    public function sync_snippets()
    {
        if ( ! $this->snippets_path)
        {
            throw new Exception(lang('missing_snippets_path'));
        }

        ee()->load->helper('file');

        if ( ! is_dir($this->snippets_path))
        {
            if ( ! mkdir($this->snippets_path, 0777))
            {
                throw new Exception(lang('invalid_snippets_path'));
            }

            if ( ! is_really_writable($this->snippets_path))
            {
                throw new Exception(lang('snippets_path_not_writable'));
            }
        }

        $sites = array('0' => 'global_snippets');

        $query = ee()->db->select('site_name, site_id')
            ->get('sites');

        foreach ($query->result() as $row)
        {
            $sites[$row->site_id] = $row->site_name;
        }

        $query->free_result();

        foreach ($sites as $site_id => $site_name)
        {
            $site_snippets_path = $this->snippets_path.$site_name.'/';

            if ( ! is_dir($site_snippets_path))
            {
                mkdir($site_snippets_path, 0777);
            }

            $iterator = new DirectoryIterator($site_snippets_path);

            $ids = array();

            foreach ($iterator as $file)
            {
                if ($file->isDot() || $file->isDir() || $file->getExtension() !== 'html')
                {
                    continue;
                }

                $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);

                $contents = file_get_contents($file->getRealPath());

                $query = ee()->db->select('snippet_id')
                    ->where('snippet_name', $name)
                    ->where('site_id', $site_id)
                    ->get('snippets');

                if ($query->num_rows() === 0)
                {
                    ee()->db->insert('snippets', array(
                        'snippet_name' => $name,
                        'snippet_contents' => $contents,
                        'site_id' => $site_id,
                    ));

                    $ids[] = ee()->db->insert_id();
                }
                else
                {
                    ee()->db->update('snippets', array(
                        'snippet_contents' => $contents,
                    ), array(
                        'snippet_id' => $query->row('snippet_id'),
                    ));

                    $ids[] = $query->row('snippet_id');
                }

                $query->free_result();
            }

            if ($ids)
            {
                ee()->db->where_not_in('snippet_id', $ids);
            }

            $query = ee()->db->select('snippet_name, snippet_contents')
                ->where('site_id', $site_id)
                ->get('snippets');

            foreach ($query->result() as $row)
            {
                $this->create_snippet_file($site_name, $row->snippet_name, $row->snippet_contents);
            }

            $query->free_result();
        }
    }

    public function sync_global_variables()
    {
        $this->global_variables_path = ee()->config->slash_item('global_variables_path');

        if ( ! $this->global_variables_path)
        {
            throw new Exception(lang('missing_global_variables_path'));
        }

        ee()->load->helper('file');

        if ( ! is_dir($this->global_variables_path))
        {
            if ( ! mkdir($this->global_variables_path, 0777))
            {
                throw new Exception(lang('invalid_global_variables_path'));
            }

            if ( ! is_really_writable($this->global_variables_path))
            {
                throw new Exception(lang('global_variables_path_not_writable'));
            }
        }

        $sites = array();

        $query = ee()->db->select('site_name, site_id')
            ->get('sites');

        foreach ($query->result() as $row)
        {
            $sites[$row->site_id] = $row->site_name;
        }

        $query->free_result();

        foreach ($sites as $site_id => $site_name)
        {
            $site_global_variables_path = $this->global_variables_path.$site_name.'/';

            if ( ! is_dir($site_global_variables_path))
            {
                mkdir($site_global_variables_path, 0777);
            }

            $iterator = new DirectoryIterator($site_global_variables_path);

            $ids = array();

            foreach ($iterator as $file)
            {
                if ($file->isDot() || $file->isDir() || $file->getExtension() !== 'html')
                {
                    continue;
                }

                $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);

                $contents = file_get_contents($file->getRealPath());

                $query = ee()->db->select('variable_id')
                    ->where('variable_name', $name)
                    ->where('site_id', $site_id)
                    ->get('global_variables');

                if ($query->num_rows() === 0)
                {
                    ee()->db->insert('global_variables', array(
                        'variable_name' => $name,
                        'variable_data' => $contents,
                        'site_id' => $site_id,
                    ));

                    $ids[] = ee()->db->insert_id();
                }
                else
                {
                    ee()->db->update('global_variables', array(
                        'variable_data' => $contents,
                    ), array(
                        'variable_id' => $query->row('variable_id'),
                    ));

                    $ids[] = $query->row('variable_id');
                }

                $query->free_result();
            }

            $has_low_variables = array_key_exists('Low_variables_ext', ee()->extensions->version_numbers);

            if ($ids)
            {
                ee()->db->where_not_in('variable_id', $ids);
            }

            $query = ee()->db->select('variable_id, variable_name, variable_data')
                ->where('site_id', $site_id)
                ->get('global_variables');

            foreach ($query->result() as $row)
            {
                // don't sync low variables, which have their own file handling
                if ($has_low_variables && ee()->db->where('variable_id', $row->variable_id)->count_all_results('low_variables') > 0)
                {
                    continue;
                }

                $this->create_global_variable_file($site_name, $row->variable_name, $row->variable_data);
            }

            $query->free_result();
        }
    }

    public function sync_specialty_templates()
    {
        if ( ! $this->specialty_templates_path)
        {
            throw new Exception(lang('missing_specialty_templates_path'));
        }

        ee()->load->helper('file');

        if ( ! is_dir($this->specialty_templates_path))
        {
            if ( ! mkdir($this->specialty_templates_path, 0777))
            {
                throw new Exception(lang('invalid_specialty_templates_path'));
            }

            if ( ! is_really_writable($this->specialty_templates_path))
            {
                throw new Exception(lang('specialty_templates_path_not_writable'));
            }
        }

        $sites = array();

        $query = ee()->db->select('site_name, site_id')
            ->get('sites');

        foreach ($query->result() as $row)
        {
            $sites[$row->site_id] = $row->site_name;
        }

        $query->free_result();

        foreach ($sites as $site_id => $site_name)
        {
            $site_specialty_templates_path = $this->specialty_templates_path.$site_name.'/';

            if ( ! is_dir($site_specialty_templates_path))
            {
                mkdir($site_specialty_templates_path, 0777);
            }

            $iterator = new DirectoryIterator($site_specialty_templates_path);

            $ids = array();

            foreach ($iterator as $file)
            {
                if ($file->isDot() || $file->isDir() || $file->getExtension() !== 'html')
                {
                    continue;
                }

                $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);

                $contents = file_get_contents($file->getRealPath());

                $query = ee()->db->select('template_id')
                    ->where('template_name', $name)
                    ->where('site_id', $site_id)
                    ->get('specialty_templates');

                if ($query->num_rows() > 0)
                {
                    ee()->db->update('specialty_templates', array(
                        'template_data' => $contents,
                    ), array(
                        'template_id' => $query->row('template_id'),
                    ));

                    $ids[] = $query->row('template_id');
                }

                $query->free_result();
            }

            if ($ids)
            {
                ee()->db->where_not_in('template_id', $ids);
            }

            $query = ee()->db->select('template_name, template_data')
                ->where('site_id', $site_id)
                ->get('specialty_templates');

            foreach ($query->result() as $row)
            {
                $this->create_specialty_template_file($site_name, $row->template_name, $row->template_data);
            }

            $query->free_result();
        }
    }

    public function delete_snippet_file($site_name, $snippet_name)
    {
        $path = $this->snippets_path.$site_name.'/'.$snippet_name.'.html';

        if (file_exists($path))
        {
            unlink($path);
        }
    }

    public function delete_global_variable_file($site_name, $variable_name)
    {
        $path = $this->global_variables_path.$site_name.'/'.$variable_name.'.html';

        if (file_exists($path))
        {
            unlink($path);
        }
    }

    public function create_snippet_file($site_name, $snippet_name, $snippet_contents = '')
    {
        if ( ! is_dir($this->snippets_path) && ! mkdir($this->snippets_path, 0777))
        {
            throw new \Exception(lang('invalid_snippets_path'));
        }

        if ( ! is_dir($this->snippets_path.$site_name) && ! mkdir($this->snippets_path.$site_name, 0777))
        {
            throw new \Exception(lang('invalid_snippets_path'));
        }

        $handle = fopen($this->snippets_path.$site_name.'/'.$snippet_name.'.html', 'w');

        fwrite($handle, $snippet_contents);

        fclose($handle);
    }

    public function create_global_variable_file($site_name, $variable_name, $variable_data = '')
    {
        if ( ! is_dir($this->global_variables_path) && ! mkdir($this->global_variables_path, 0777))
        {
            throw new \Exception(lang('invalid_global_variables_path'));
        }

        if ( ! is_dir($this->global_variables_path.$site_name) && ! mkdir($this->global_variables_path.$site_name, 0777))
        {
            throw new \Exception(lang('invalid_global_variables_path'));
        }

        $handle = fopen($this->global_variables_path.$site_name.'/'.$variable_name.'.html', 'w');

        fwrite($handle, $variable_data);

        fclose($handle);
    }

    public function create_specialty_template_file($site_name, $template_name, $template_data)
    {
        if ( ! is_dir($this->specialty_templates_path) && ! mkdir($this->specialty_templates_path, 0777))
        {
            throw new \Exception(lang('invalid_specialty_templates_path'));
        }

        if ( ! is_dir($this->specialty_templates_path.$site_name) && ! mkdir($this->specialty_templates_path.$site_name, 0777))
        {
            throw new \Exception(lang('invalid_specialty_templates_path'));
        }

        $handle = fopen($this->specialty_templates_path.$site_name.'/'.$template_name.'.html', 'w');

        fwrite($handle, $template_data);

        fclose($handle);
    }
}