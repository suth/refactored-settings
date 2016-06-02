<?php
/**
 * @coversDefaultClass Refactored_Settings_Section_0_5_0
 */
class RefactoredSettingsSectionTest extends WP_UnitTestCase {

    private $obj;
    private $field;

    /** @before */
    public function configureObjects()
    {
        $this->obj = new Refactored_Settings_Section_0_5_0();
        $this->field = new Refactored_Settings_Field_0_5_0();
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
     * @covers ::__construct
     */
    public function a_section_object_can_be_newed_up()
    {
        $section = new $this->obj;

        $this->assertInstanceOf(get_class($this->obj), $section);
    }

    /**
     * @test
     * @covers ::init
     */
    public function it_has_an_init_method()
    {
        $field = $this->getMock(get_class($this->field), array('init'));
        $field->expects($this->once())
            ->method('init');

        $this->obj->key('test_section')
            ->page('another_plugin')
            ->addField($field)
            ->init();

        $this->assertEquals(1,
            did_action('rfs/pre_init:another_plugin.test_section')
        );

        $this->assertEquals(1,
            did_action('rfs/post_init:another_plugin.test_section')
        );
    }

    /**
     * @test
     * @covers ::withKey
     */
    public function the_withKey_method_returns_a_new_instance()
    {
        $obj = $this->obj;
        $instance = $obj::withKey('test_slug');

        $this->assertInstanceOf(get_class($this->obj), $instance);
        $this->assertEquals('test_slug', $instance->getKey());
    }

    /**
     * @test
     * @covers ::key
     * @covers ::getKey
     */
    public function it_has_a_key_setter_and_getter()
    {
        $value = 'section_slug';

        $this->assertEquals(
            $value,
            $this->obj->key($value)->getKey()
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

        $field = $this->getMock(get_class($this->field), array('page'));

        $this->obj->addField($field);

        $field->expects($this->once())
            ->method('page');

        $this->assertEquals(
            $value,
            $this->obj->page($value)->getPage()
        );
    }

    /**
     * @test
     * @covers ::name
     * @covers ::getName
     */
    public function it_has_a_name_setter_and_getter()
    {
        $value = 'Awesome Section';

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
        $value = 'Settings that are useful for something.';

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
     * @covers ::addField
     * @covers ::addFields
     * @covers ::getFields
     */
    public function it_can_add_and_get_fields()
    {
        $field = $this->field;

        $this->obj->addField(
            $field::withKey('one')
        );
        $this->obj->addFields(array(
            $field::withKey('two'),
            $field::withKey('three')
        ));

        $this->assertCount(3, $this->obj->getFields());
    }

    /**
     * @test
     * @covers ::doAction
     */
    public function it_has_a_doAction_method()
    {
        $this->obj->page('plugin_slug')->key('section_slug');
        $this->invokePrivateMethod('doAction', array('some_action'));

        $this->assertEquals(1, did_action('rfs/some_action:plugin_slug.section_slug'));
    }

    /**
     * @test
     * @covers ::sanitize
     */
    public function it_has_a_sanitize_method()
    {
        $field = $this->getMock(get_class($this->field), array('sanitize'));

        $field->expects($this->once())
            ->method('sanitize')
            ->willReturn(true);

        $field->key('field');

        $this->obj->key('section')
            ->addField($field);
        
        $data = array(
            'field' => 'on'
        );

        $this->assertEquals(
            array('field' => true),
            $this->obj->sanitize($data)
        );
    }

    /**
     * @test
     * @covers ::__get
     */
    public function it_has_a_magic_getter_for_fields()
    {
        $this->assertNull($this->obj->sample_field);

        $field = $this->field;

        $this->obj->addField(
            $field::withKey('sample_field')
        );

        $this->assertInstanceOf(get_class($this->field), $this->obj->sample_field);
    }

    /**
     * @test
     * @covers ::render
     */
    public function it_has_a_render_method()
    {
        $this->obj->description('Hello World');

        ob_start();

        $this->obj->render();

        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals(
            '<p>Hello World</p>',
            $output
        );
    }
}
