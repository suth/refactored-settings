[![Build Status](https://travis-ci.org/suth/refactored-settings.svg?branch=master)](https://travis-ci.org/suth/refactored-settings) [![Coverage Status](https://coveralls.io/repos/github/suth/refactored-settings/badge.svg?branch=master)](https://coveralls.io/github/suth/refactored-settings?branch=master)

# Refactored Settings

An easy to use fluent wrapper for the WordPress Settings API.

## Requirements

PHP 5.3+

## Example

Below is an example.

```php
$settings = Refactored_Settings_0_5_0::withSlug('my_settings')
    ->version('1.0');

$section = Refactored_Settings_Section_0_5_0::withSlug('general')
    ->name('Sample Section')
    ->description('A short description.')
    ->addFields(array(
        Refactored_Settings_Field_0_5_0::withSlug('name')
            ->name('Field Name')
            ->description('The name of a custom field.')
            ->type('text')
            ->defaultValue('custom'),
        Refactored_Settings_Field_0_5_0::withSlug('enabled')
            ->name('Enabled')
            ->description('If the feature should be enabled.')
            ->type('checkbox')
            ->defaultValue(false),
    ));

$settings->addSection($section);

$settings->init();
```

You should now have a fully functioning settings page with all the options you've specified. You'll also be able to access your settings:

```php
$settings->general->enabled->getValue();
```

## Versioning

To avoid possible conflicts with plugins using different versions, class names have the version number appended.

Please be aware this project is still in development and may rapidly change.

## Hooks

Various action hooks are provided for your convenience.

### Settings Hooks

These hooks are related to the main settings class which will be passed as an argument. Replace `{$setting}` with your chosen slug.

#### rfs/pre_init:{$setting}

Fires before the setting is initialized

#### rfs/post_init:{$setting}

Fires after the setting is initialized

#### rfs/before:{$setting}

Print to settings page before the form

#### rfs/after:{$setting}

Print to settings page after the form

#### rfs/activation:{$setting}

If a plugin file is specified, this is fired upon activation

#### rfs/deactivation:{$setting}

If a plugin file is specified, this is fired upon deactivation

### Section Hooks

These hooks are related to the settings section class which will be passed as an argument. Replace `{$setting}` and `{$section}` with their relative slugs.

#### rfs/pre_init:{$setting}.{$section}

Fires before the section is initialized

#### rfs/post_init:{$setting}.{$section}

Fires after the section is initialized

### Field Hooks

These hooks are related to the settings field class which will be passed as an argument. Replace `{$setting}`, `{$section}`, and `{$field}` with their relative slugs.

#### rfs/pre_init:{$setting}.{$section}.{$field}

Fires before the field is initialized

#### rfs/post_init:{$setting}.{$section}.{$field}

Fires after the field is initialized
