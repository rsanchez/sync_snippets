<?php

use Illuminate\Console\Command;

class SyncGlobalVariablesCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'sync:global_variables';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Sync global variables from files.';

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        ee()->load->add_package_path(PATH_THIRD.'sync_snippets/');
        ee()->load->library('sync_snippets');
        ee()->sync_snippets->sync_global_variables();

        $this->info('Global variables synced.');
    }
}
