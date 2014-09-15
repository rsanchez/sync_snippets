<?php

use Illuminate\Console\Command;

class SyncSnippetsCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'sync:snippets';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Sync snippets from files.';

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        ee()->load->add_package_path(PATH_THIRD.'sync_snippets/');
        ee()->load->library('sync_snippets');
        ee()->sync_snippets->sync_snippets();

        $this->info('Snippets synced.');
    }
}
