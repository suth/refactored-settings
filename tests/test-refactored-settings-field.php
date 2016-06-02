<?php
/**
 * @coversDefaultClass Refactored_Settings_Field_0_5_0
 */
class RefactoredSettingsFieldTest extends WP_UnitTestCase {

    private $obj;

    /** @before */
    public function configureObjects()
    {
        $this->obj = new Refactored_Settings_Field_0_5_0();
    }

    private function invokePrivateMethod($methodName, $args = array())
    {
		$reflector = new ReflectionClass(get_class($this->obj));
		$method = $reflector->getMethod($methodName);
		$method->setAccessible(true);
 
		return $method->invokeArgs($this->obj, $args);
    }

    private function logInAdmin()
    {
        $user = $this->factory->user->create(array(
            'role' => 'administrator'
        ));

        wp_set_current_user($user);
    }

    private function configureExampleField($type = 'text')
    {
        return $this->obj->type($type)
            ->page('page')
            ->section('section')
            ->slug('field')
            ->name('Field Name')
            ->description('Field description.');
    }

    private function getExpectedFieldFile()
    {
        return './tests/expected/fields/' . $this->obj->getType() . '.txt';
    }

    private function writeToExpectedFieldFile($contents)
    {
        file_put_contents($this->getExpectedFieldFile(), $contents);
    }

    private function assertEqualsExpectedFieldFile($html)
    {
        $this->assertStringEqualsFile(
            $this->getExpectedFieldFile(),
            $html,
            'Failed to assert the field matches the expect HTML.'
        );
    }

    /**
     * @test
     * @covers ::init
     */
    public function it_has_an_init_method()
    {
        $this->obj->slug('enabled')
            ->page('field_test_page')
            ->section('field_test_section')
            ->init();

        $this->assertEquals(1,
            did_action('rfs/pre_init:field_test_page.field_test_section.enabled')
        );

        $this->assertEquals(1,
            did_action('rfs/post_init:field_test_page.field_test_section.enabled')
        );
    }

    /**
     * @test
     * @covers ::withSlug
     */
    public function the_withSlug_method_returns_a_new_instance()
    {
        $obj = $this->obj;
        $instance = $obj::withSlug('test_slug');

        $this->assertInstanceOf(get_class($this->obj), $instance);
        $this->assertEquals('test_slug', $instance->getSlug());
    }

    /**
     * @test
     * @covers ::slug
     * @covers ::getSlug
     */
    public function it_has_a_slug_setter_and_getter()
    {
        $value = 'field_slug';

        $this->assertEquals(
            $value,
            $this->obj->slug($value)->getSlug()
        );
    }

    /**
     * @test
     * @covers ::page
     * @covers ::getPage
     */
    public function it_has_a_page_setter_and_getter()
    {
        $value = 'someslug';

        $this->assertEquals(
            $value,
            $this->obj->page($value)->getPage()
        );
    }

    /**
     * @test
     * @covers ::section
     * @covers ::getSection
     */
    public function it_has_a_section_setter_and_getter()
    {
        $value = 'someslug';

        $this->assertEquals(
            $value,
            $this->obj->section($value)->getSection()
        );
    }

    /**
     * @test
     * @covers ::type
     * @covers ::getType
     */
    public function it_has_a_type_setter_and_getter()
    {
        $value = 'text';

        $this->assertEquals(
            $value,
            $this->obj->type($value)->getType()
        );
    }

    /**
     * @test
     * @covers ::validTypes
     */
    public function it_has_a_validTypes_method()
    {
        $valid = array(
            'text',
            'textarea',
            'checkbox',
            'checkboxes',
            'radio',
            'select',
            'post_type',
            'post_types',
            'taxonomy',
            'taxonomies',
        );

        $this->assertEquals(
            $valid,
            $this->invokePrivateMethod('validTypes')
        );
    }

    /**
     * @test
     * @covers ::type
     * @expectedException WPDieException
     */
    public function it_requires_a_valid_type()
    {
        $this->obj->type('invalid_type');
    }

    /**
     * @test
     * @covers ::guardAgainstInvalidType
     * @expectedException WPDieException
     */
    public function it_guards_against_invalid_types()
    {
        $this->invokePrivateMethod('guardAgainstInvalidType', array('text'));
        $this->invokePrivateMethod('guardAgainstInvalidType', array('invalid_type'));
    }

    /**
     * @test
     * @covers ::options
     * @covers ::getOptions
     */
    public function it_has_an_options_setter_and_getter()
    {
        $value = array(
            'one' => 'One option',
            'two' => 'A second option',
        );

        $this->assertEquals(
            $value,
            $this->obj->options($value)->getOptions()
        );
    }

    /**
     * @test
     * @covers ::getOptions
     */
    public function it_appends_post_types_to_options_when_appropriate()
    {
        $post_types = $this->invokePrivateMethod('getPostTypesForOptions');
        $original_options = array('none' => 'None');

        $options = array_merge($original_options, $post_types);

        $this->obj->type('post_type')
            ->options($original_options);

        $this->assertEquals(
            $options,
            $this->obj->getOptions()
        );

        $this->obj->type('post_types')
            ->options($original_options);

        $this->assertEquals(
            $options,
            $this->obj->getOptions()
        );
    }

    /**
     * @test
     * @covers ::getOptions
     */
    public function it_appends_taxonomies_to_options_when_appropriate()
    {
        $taxonomies = $this->invokePrivateMethod('getTaxonomiesForOptions');
        $original_options = array('none' => 'None');

        $options = array_merge($original_options, $taxonomies);

        $this->obj->type('taxonomy')
            ->options($original_options);

        $this->assertEquals(
            $options,
            $this->obj->getOptions()
        );

        $this->obj->type('taxonomies')
            ->options($original_options);

        $this->assertEquals(
            $options,
            $this->obj->getOptions()
        );
    }

    /**
     * @test
     * @covers ::defaultValue
     * @covers ::getDefaultValue
     */
    public function it_has_a_defaultValue_setter_and_getter()
    {
        $value = 'A default value';

        $this->assertEquals(
            $value,
            $this->obj->defaultValue($value)->getDefaultValue()
        );
    }

    /**
     * @test
     * @covers ::getDefaultValue
     */
    public function it_falls_back_to_a_boolean_or_array_as_default_if_none_is_set_on_certain_types()
    {
        $this->obj->type('checkbox');

        $this->assertFalse(
            $this->obj->getDefaultValue()
        );

        $this->obj->type('checkboxes');

        $this->assertEquals(
            array(),
            $this->obj->getDefaultValue()
        );

        $this->obj->type('post_types');

        $this->assertEquals(
            array(),
            $this->obj->getDefaultValue()
        );

        $this->obj->type('taxonomies');

        $this->assertEquals(
            array(),
            $this->obj->getDefaultValue()
        );
    }

    /**
     * @test
     * @covers ::getId
     */
    public function it_has_a_getId_method()
    {
        $this->obj->page('page')->section('section')->slug('field');

        $this->assertEquals(
            'page-section-field',
            $this->invokePrivateMethod('getId')
        );
    }

    /**
     * @test
     * @covers ::name
     * @covers ::getName
     */
    public function it_has_a_name_setter_and_getter()
    {
        $value = 'Awesome Field';

        $this->assertEquals(
            $value,
            $this->obj->name($value)->getName()
        );
    }

    /**
     * @test
     * @covers ::description
     * @covers ::getDescription
     */
    public function it_has_a_description_setter_and_getter()
    {
        $value = 'Setting field for something.';

        $this->assertEquals(
            $value,
            $this->obj->description($value)->getDescription()
        );
    }

    /**
     * @test
     * @covers ::callback
     * @covers ::getCallback
     */
    public function it_has_a_callback_setter_and_getter()
    {
        $this->obj->type('select');

        $this->assertEquals(
            array($this->obj, 'renderSelect'),
            $this->obj->getCallback()
        );

        $value = array('Class', 'method');

        $this->assertEquals(
            $value,
            $this->obj->callback($value)->getCallback()
        );
    }

    /**
     * @test
     * @covers ::getValue
     */
    public function it_has_a_getValue_method()
    {
        add_option('one', array(
            'two' => array(
                'three' => true
            )
        ));

        $this->obj->page('one')->section('two')->slug('three');

        $this->assertTrue(
            $this->obj->getValue()
        );

        $this->obj->slug('four');

        $this->assertNull(
            $this->obj->getValue()
        );

        $this->obj->defaultValue(true);

        $this->assertTrue(
            $this->obj->getValue()
        );
    }

    /**
     * @test
     * @covers ::getEscapedValue
     */
    public function it_has_a_getEscapedValue_method()
    {
        add_option('one', array(
            'two' => array(
                'three' => '<p>Something that needs escaping</p>'
            )
        ));

        $this->obj->page('one')->section('two')->slug('three');

        $this->assertEquals(
            '&lt;p&gt;Something that needs escaping&lt;/p&gt;',
            $this->obj->getEscapedValue()
        );
    }

    /**
     * @test
     * @covers ::isType
     */
    public function it_has_an_isType_method()
    {
        $this->obj->type('text');

        $this->assertTrue(
            $this->obj->isType('text')
        );

        $this->assertFalse(
            $this->obj->isType('checkbox')
        );
    }

    /**
     * @test
     * @covers ::doAction
     */
    public function it_has_a_doAction_method()
    {
        $this->obj->page('plugin_slug')->section('section_slug')->slug('field_slug');
        $this->invokePrivateMethod('doAction', array('some_action'));

        $this->assertEquals(1, did_action('rfs/some_action:plugin_slug.section_slug.field_slug'));
    }

    /**
     * @test
     * @covers ::valueIsBoolean
     */
    public function it_has_a_valueIsBoolean_method()
    {
        $this->assertFalse(
            $this->invokePrivateMethod('valueIsBoolean')
        );

        $this->obj->type('checkbox');

        $this->assertTrue(
            $this->invokePrivateMethod('valueIsBoolean')
        );

        $this->obj->options(array('option'));

        $this->assertFalse(
            $this->invokePrivateMethod('valueIsBoolean')
        );
    }

    /**
     * @test
     * @covers ::valueIsArray
     */
    public function it_has_a_valueIsArray_method()
    {
        $this->assertFalse(
            $this->invokePrivateMethod('valueIsArray')
        );

        $this->obj->type('checkboxes');

        $this->assertTrue(
            $this->invokePrivateMethod('valueIsArray')
        );

        $this->obj->type('post_types');

        $this->assertTrue(
            $this->invokePrivateMethod('valueIsArray')
        );

        $this->obj->type('taxonomies');

        $this->assertTrue(
            $this->invokePrivateMethod('valueIsArray')
        );
    }

    /**
     * @test
     * @covers ::sanitize
     */
    public function it_has_a_sanitize_method()
    {
        $this->assertEquals(
            'input_text',
            $this->obj->sanitize('input_text')
        );

        $this->obj->type('checkbox');

        $this->assertFalse(
            $this->obj->sanitize(null)
        );

        $this->assertTrue(
            $this->obj->sanitize('on')
        );
    }

    /**
     * @test
     * @covers ::getFieldName
     */
    public function it_has_a_getFieldName_method_for_input_name_attribute()
    {
        $this->obj->page('page_set')->section('section_set')->slug('field_set');

        $this->assertEquals(
            'page_set[section_set][field_set]',
            $this->invokePrivateMethod('getFieldName')
        );
    }

    /**
     * @test
     * @covers ::renderTextField
     */
    public function it_renders_a_text_field()
    {
        $this->configureExampleField('text')
            ->defaultValue('Default Value');

        $html = $this->invokePrivateMethod('renderTextField');

        $this->assertEqualsExpectedFieldFile($html);
    }

    /**
     * @test
     * @covers ::renderCheckbox
     */
    public function it_renders_a_checkbox_field()
    {
        $this->configureExampleField('checkbox')
            ->defaultValue(true);

        $html = $this->invokePrivateMethod('renderCheckbox');

        $this->assertEqualsExpectedFieldFile($html);
    }

    /**
     * @test
     * @covers ::renderCheckboxes
     */
    public function it_renders_a_checkboxes_field()
    {
        $this->configureExampleField('checkboxes')
            ->options(array(
                'one' => 'One Option',
                'two' => 'Second Option'
            ))
            ->defaultValue(array('two'));

        $html = $this->invokePrivateMethod('renderCheckboxes');

        $this->assertEqualsExpectedFieldFile($html);
    }

    /**
     * @test
     * @covers ::renderTextarea
     */
    public function it_renders_a_textarea_field()
    {
        $this->configureExampleField('textarea')
            ->defaultValue('A longer text input. It <strong>escapes</strong> HTML.');

        $html = $this->invokePrivateMethod('renderTextarea', array(array('first-class', 'second-class')));

        $this->assertEqualsExpectedFieldFile($html);
    }

    /**
     * @test
     * @covers ::renderRadio
     */
    public function it_renders_a_radio_field()
    {
        $this->configureExampleField('radio')
            ->options(array(
                'one' => 'One Option',
                'two' => 'Second Option'
            ))
            ->defaultValue('two');

        $html = $this->invokePrivateMethod('renderRadio');

        $this->assertEqualsExpectedFieldFile($html);
    }

    /**
     * @test
     * @covers ::renderSelect
     */
    public function it_renders_a_select_field()
    {
        $this->configureExampleField('select')
            ->options(array(
                'one' => 'One Option',
                'two' => 'Second Option'
            ))
            ->defaultValue('two');

        $html = $this->invokePrivateMethod('renderSelect');

        $this->assertEqualsExpectedFieldFile($html);
    }

    /**
     * @test
     * @covers ::getPostTypesForOptions
     */
    public function it_has_a_getPostTypesForOptions_method()
    {
        $expected = array(
            'post' => 'Posts',
            'page' => 'Pages',
            'attachment' => 'Media'
        );

        $this->assertEquals(
            $expected,
            $this->invokePrivateMethod('getPostTypesForOptions')
        );
    }

    /**
     * @test
     * @covers ::getTaxonomiesForOptions
     */
    public function it_has_a_getTaxonomiesForOptions_method()
    {
        $expected = array(
            'category' => 'Categories',
            'post_tag' => 'Tags',
            'link_category' => 'Link Categories'
        );

        $this->assertEquals(
            $expected,
            $this->invokePrivateMethod('getTaxonomiesForOptions')
        );
    }

    /**
     * @test
     * @covers ::getRenderers
     */
    public function it_has_a_getRenderers_method()
    {
        $expected = array(
            'text' => 'renderTextField',
            'textarea' => 'renderTextarea',
            'checkbox' => 'renderCheckbox',
            'checkboxes' => 'renderCheckboxes',
            'radio' => 'renderRadio',
            'select' => 'renderSelect',
            'post_type' => 'renderSelect',
            'post_types' => 'renderCheckboxes',
            'taxonomy' => 'renderSelect',
            'taxonomies' => 'renderCheckboxes',
        );

        $this->assertEquals(
            $expected,
            $this->invokePrivateMethod('getRenderers')
        );
    }

    /**
     * @test
     * @covers ::render
     */
    public function it_has_a_render_method()
    {
        $this->obj->type('checkbox')
            ->callback(function () {
                return 'Rendered.';
            });

        ob_start();

        $this->obj->render();

        $rendered_html = ob_get_contents();
        ob_end_clean();

        $this->assertEquals(
            'Rendered.',
            $rendered_html
        );
    }
}
