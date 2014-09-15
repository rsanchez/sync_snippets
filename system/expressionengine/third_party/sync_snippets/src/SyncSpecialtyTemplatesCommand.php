<?php

use Illuminate\Console\Command;

class SyncSpecialtyTemplatesCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'sync:specialty_templates';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Sync specialty templates from files.';

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        ee()->load->add_package_path(PATH_THIRD.'sync_snippets/');
        ee()->load->library('sync_snippets');
        ee()->sync_snippets->sync_specialty_templates();

        $this->info('Specialty templates synced.');
    }
}
