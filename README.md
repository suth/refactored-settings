# Refactored Settings

A fast and easy way to include settings in your WordPress plugins.

First you'll need to construct an array of arguments.

```php
$settings_args = array(
	'file'    => __FILE__,
	'version' => '0.1',
	'name'    => 'Refactored Settings Example Plugin',
	'slug'    => 'refactored_settings_example',
	'options' => array(
		'general' => array(
			'name'        => 'General Settings',
			'description' => 'These settings are general.',
			'fields'      => array(
				'color' => array(
					'name'        => 'Color',
					'type'        => 'text',
					'default'     => '',
					'description' => 'Your favorite color'
				),
				'milkshake' => array(
					'name'        => 'Milkshake',
					'type'        => 'checkbox',
					'default'     => '1',
					'description' => 'Would you like a milkshake with that?'
				)
			)
		),
		'advanced' => array(
			'name'        => 'Advanced Settings',
			'description' => 'The settings are advanced.',
			'fields'      => array(
				'candy' => array(
					'name'        => 'Candy',
					'type'        => 'dropdown',
					'default'     => 'mms',
					'options'     => array(
						'gummy-bears' => 'Gummy Bears',
						'skittles'    => 'Skittles',
						'mms'         => 'M&Ms'
					),
					'description' => 'Your favorite candy'
				),
				'pizza' => array(
					'name'        => 'Pizza Toppings',
					'type'        => 'multicheckbox',
					'default'     => array(
						'pepperoni'
					),
					'options'     => array(
						'extracheese' => 'Extra Cheese',
						'pepperoni'   => 'Pepperoni',
						'sausage'     => 'Sausage',
						'mushroom'    => 'Mushroom'
					),
					'description' => 'Select your favorite pizza toppings'
				),
				'drink' => array(
					'name'        => 'Drink',
					'type'        => 'radio',
					'default'     => 'coke',
					'options'     => array(
						'coke'      => 'Coke',
						'dr-pepper' => 'Dr. Pepper',
						'sprite'    => 'Sprite'
					),
					'description' => 'What would you like to drink?'
				)
			)
		)
	)
);
```

Now we just need to pass our array to the settings instace we'll be creating:

```php
$example_plugin_settings = new Refactored_Settings( $settings_args );
```

You should now have a fully functioning settings page with all the options you've specified. You'll also be able to access your settings:

```php
echo $example_plugin_settings->options['general']['color'];
```