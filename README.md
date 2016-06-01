[![Build Status](https://travis-ci.org/suth/refactored-settings.svg?branch=master)](https://travis-ci.org/suth/refactored-settings) [![Coverage Status](https://coveralls.io/repos/github/suth/refactored-settings/badge.svg?branch=master)](https://coveralls.io/github/suth/refactored-settings?branch=master)

# Refactored Settings

An easy to use fluent wrapper for the WordPress Settings API.

## Requirements

PHP 5.3+ (5.2 support planned)

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
            ->description('The name of the custom field.')
            ->type('text')
            ->defaultValue('custom'),
        Refactored_Settings_Field_0_5_0::withSlug('enabled')
            ->name('Enabled')
            ->description('If the feature should be enabled.')
            ->type('text')
            ->defaultValue(false),
    ));

$settings->addSection($section);

$settings->init();
```

You should now have a fully functioning settings page with all the options you've specified. You'll also be able to access your settings:

```php
echo $settings->general->enabled->getValue();
```
