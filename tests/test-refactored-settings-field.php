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
            'radio',
            'select',
            'post_type',
            'taxonomy',
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

        $this->obj->type('checkbox')->options(array('one'));

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
        $this->assertEquals(
            array($this->obj, 'render'),
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
}
