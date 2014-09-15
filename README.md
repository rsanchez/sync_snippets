# Sync Snippets

An ExpressionEngine add-on for saving snippets, global variables and specialty templates as files and syncing them with the database. ExpressionEngine does not natively support saving these types of content as files.

This add-on does *not* automatically sync the files with database. You must manually sync by pressing a button in this module's control panel, or via [eecli](https://github.com/rsanchez/eecli) command. This add-on is primarily intended as a companion to [eecli](https://github.com/rsanchez/eecli).

## Installation

***Requires ExpressionEngine 2.6+***

* Copy the /system/expressionengine/third_party/sync_snippets/ folder to your /system/expressionengine/third_party/ folder
* Install the Sync Snippets module and extension

## Configuration

You must specify the following three paths in your `config.php`:

```php
$config['snippets_path'] = '/path/to/snippets/';
$config['global_variables_path'] = '/path/to/global_variables/';
$config['specialty_templates_path'] = '/path/to/specialty_templates/';
```

Make sure the paths are created and writable on your localhost / server.

## How it works

Sync Snippets will read `.html` files in the directory and will copy the text from the file to the corresponding database table. If a snippet / global variable does *not* exist in the database, it will be added to the database. If a snippet / global variable / specialty template *does* exist in the database, the text will be copied to the existing entry. If a snippet / global variable / specialty template is in the database, and there is no corresponding file, one will be created.

Sync Snippets does not pull text from the database to files (except when creating new files). Do not make edits to your snippets / global variables / specialty templates in the control panel for this reason.

Sync Snippets is only useful if you don't mind manually syncing your files in the control panel, or as part of a post-deployment strategy (git post-receive hook, capistrano, envoy, ansible, etc.) using [eecli](https://github.com/rsanchez/eecli).

## eecli Commands

Sync Snippets adds the following commands to [eecli](https://github.com/rsanchez/eecli).

```
eecli sync:snippets

eecli sync:global_variables

eecli sync:specialty_templates
```
