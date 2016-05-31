<?php
/**
 * @coversDefaultClass Refactored_Settings_0_5_0
 */
class RefactoredSettingsTest extends WP_UnitTestCase {

    private $obj;
    private $section;
    private $field;

    public function setUp()
    {
        parent::setUp();

        $this->obj = new Refactored_Settings_0_5_0();
        $this->section = new Refactored_Settings_Section_0_5_0();
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
    public function a_settings_object_can_be_newed_up()
    {
        $settings = new $this->obj;

        $this->assertInstanceOf(get_class($this->obj), $settings);
    }
    /**
     * @test
     * @covers ::init
     */
    public function it_has_an_init_method()
    {
        global $new_whitelist_options;

        $section = $this->getMock(get_class($this->section), array('init'));
        $section->expects($this->once())
            ->method('init');

        $this->obj->slug('test_plugin')
            ->addSection($section)
            ->init();

        $this->assertEquals(1,
            did_action('rfs/pre_init:test_plugin')
        );

        $this->assertEquals('test_plugin', $new_whitelist_options['test_plugin'][0]);

        $this->assertEquals(1,
            did_action('rfs/post_init:test_plugin')
        );
    }

    /**
     * @test
     * @coversNothing
     */
    public function it_creates_settings()
    {
        $obj = $this->obj;
        $s = $obj::withSlug('test_plugin')
            ->version('1.0');

        $section = $this->section;
        $field = $this->field;

        $section_one = $section::withSlug('general')
            ->name('Sample Section')
            ->description('A short description.')
            ->addFields(array(
                $field::withSlug('name')
                    ->name('Field Name')
                    ->description('The name of the custom field.')
                    ->type('text')
                    ->defaultValue('custom'),
                $field::withSlug('enabled')
                    ->name('Enabled')
                    ->description('If the feature should be enabled.')
                    ->type('text')
                    ->defaultValue(false),
            ));

        $s->addSection($section_one)->init();

        $this->assertEquals('custom', $s->general->name->getValue());
        $this->assertEquals(false, $s->general->enabled->getValue());
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
        $value = 'setting_slug';

        $this->assertEquals(
            $value,
            $this->obj->slug($value)->getSlug()
        );
    }

    /**
     * @test
     * @covers ::version
     * @covers ::getVersion
     */
    public function it_has_a_version_setter_and_getter()
    {
        $value = '1.0';

        $this->assertEquals(
            $value,
            $this->obj->version($value)->getVersion()
        );
    }

    /**
     * @test
     * @covers ::title
     * @covers ::getTitle
     */
    public function it_has_a_title_setter_and_getter()
    {
        $value = 'Awesome Settings';

        $this->assertEquals(
            $value,
            $this->obj->title($value)->getTitle()
        );
    }

    /**
     * @test
     * @covers ::pluginFile
     * @covers ::getPluginFile
     */
    public function it_has_a_pluginFile_setter_and_getter()
    {
        $value = 'file.php';

        $this->assertEquals(
            $value,
            $this->obj->pluginFile($value)->getPluginFile()
        );
    }

    /**
     * @test
     * @covers ::optionsPage
     */
    public function it_has_an_optionsPage_method()
    {
        $this->obj->slug('test_plugin')
            ->title('Test Plugin');

        $section = $this->section;
        $field = $this->field;

        $section_one = $section::withSlug('general')
            ->name('Sample Section')
            ->description('A short description.')
            ->addFields(array(
                $field::withSlug('name')
                    ->name('Field Name')
                    ->description('The name of the custom field.')
                    ->type('text')
                    ->defaultValue('custom'),
            ));

        $this->obj->addSection($section_one);

        $this->logInAdmin();

        ob_start();

        $this->obj->optionsPage();

        $output = ob_get_contents();
        ob_end_clean();

        $this->assertStringMatchesFormatFile(
            './tests/expected/optionsPage.txt',
            $output . PHP_EOL
        );
    }

    /**
     * @test
     * @covers ::optionsPage
     * @expectedException WPDieException
     */
    public function the_optionsPage_method_blocks_unauthorized_users()
    {
        $this->obj->slug('test_plugin')
            ->title('Test Plugin');

        $this->obj->optionsPage();
    }

    /**
     * @test
     * @covers ::addSection
     * @covers ::addSections
     * @covers ::getSections
     */
    public function it_can_add_and_get_sections()
    {
        $section = $this->section;

        $this->obj->addSection(
            $section::withSlug('one')
        );
        $this->obj->addSections(array(
            $section::withSlug('two'),
            $section::withSlug('three')
        ));

        $this->assertCount(3, $this->obj->getSections());
    }

    /**
     * @test
     * @covers ::doAction
     */
    public function it_has_a_doAction_method()
    {
        $this->obj->slug('plugin_slug');
        $this->invokePrivateMethod('doAction', array('some_action'));

        $this->assertEquals(1, did_action('rfs/some_action:plugin_slug'));
    }

    /**
     * @test
     * @covers ::registerActivationDeactivationHooks
     */
    public function it_has_a_registerActivationDeactivationHooks_method()
    {
        global $wp_filter;

        $this->obj->slug('plugin_slug')->pluginFile('plugin-file.php');

        $this->assertArrayHasKey('activate_plugin-file.php', $wp_filter);
        $this->assertArrayHasKey('deactivate_plugin-file.php', $wp_filter);
    }

    /**
     * @test
     * @covers ::__get
     */
    public function it_has_a_magic_getter_for_sections()
    {
        $this->assertNull($this->obj->sample_section);

        $section = $this->section;

        $this->obj->addSection(
            $section::withSlug('sample_section')
        );

        $this->assertInstanceOf(get_class($this->section), $this->obj->sample_section);
    }

    /**
     * @test
     * @covers ::addOptionsPage
     */
    public function it_has_an_addOptionsPage_method()
    {
        $this->logInAdmin();
        
        $page_hook = $this->obj->slug('test_plugin')->addOptionsPage();

        $this->assertEquals('admin_page_test_plugin', $page_hook);
    }

    /**
     * @test
     * @covers ::pluginActivation
     */
    public function it_has_a_pluginActivation_method()
    {
        $this->obj->slug('test_plugin');

        $this->obj->pluginActivation();

        $this->assertEquals(1, did_action('rfs/activation:test_plugin'));
    }

    /**
     * @test
     * @covers ::pluginDeactivation
     */
    public function it_has_a_pluginDeactivation_method()
    {
        $this->obj->slug('test_plugin');

        $this->obj->pluginDeactivation();

        $this->assertEquals(1, did_action('rfs/deactivation:test_plugin'));
    }

    /**
     * @test
     * @covers ::sanitizeInput
     */
    public function it_sanitizes_input()
    {
        $section = $this->section;
        $field = $this->field;

        $this->obj->slug('test_plugin')
            ->version('1.0')
            ->addSection(
                $section::withSlug('general')
                    ->addFields(array(
                        $field::withSlug('enabled')
                            ->type('checkbox')
                    ))
            );

        $output = $this->obj->sanitizeInput(array(
            'general' => array(
                'enabled' => 'on'
            )
        ));

        $expected = array(
            'version' => '1.0',
            'general' => array(
                'enabled' => true
            )
        );

        $this->assertEquals($expected, $output);
    }
}
