<?php
/*  Copyright 2016 Sutherland Boswell  (email : hello@sutherlandboswell.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !class_exists( 'Refactored_Settings_Field_0_5_0' ) ) :

class Refactored_Settings_Field_0_5_0 {

	protected $page;
	protected $section;
	protected $name;
	protected $slug;
	protected $description;
    protected $defaultValue;
    protected $callback;
    protected $type;
    protected $options;

    /**
     * Construct a new instance
     *
     * @param string $slug
     * @return Refactored_Settings_Field
     */
    public static function withSlug($slug)
    {
        $obj = new self;
        $obj->slug($slug);

        return $obj;
    }

    /**
     * Set the field's slug
     *
     * @param string $slug
     * @return $this
     */
    public function slug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get the field's slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set the settings page the field belongs to
     *
     * @param string $page
     * @return $this
     */
    public function page($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get the settings page the field belongs to
     *
     * @return $this
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set the section the field belongs to
     *
     * @param string $section
     * @return $this
     */
    public function section($section)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * Get the section the field belongs to
     *
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Set the display name for the field
     *
     * @param string $name
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the display name for the field
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the description of the field
     *
     * @param string $description
     * @return $this
     */
    public function description($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the description of the field
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get the valid field types
     *
     * @return array
     */
    protected function validTypes()
    {
        return array(
            'text',
            'textarea',
            'checkbox',
            'radio',
            'select',
            'post_type',
            'taxonomy',
        );
    }

    private function guardAgainstInvalidType($type)
    {
        if ( ! in_array($type, $this->validTypes())) wp_die('Refactored Settings Error: "' . $type . '" is not a valid field type.');
    }

    /**
     * Set the type of field
     *
     * @param string $type
     * @return $this
     */
    public function type($type)
    {
        $this->guardAgainstInvalidType($type);

        $this->type = $type;

        return $this;
    }

    /**
     * Get the type of field
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the available options for multiple choice fields
     *
     * @param array $options
     * @return $this
     */
    public function options($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get the available options for multiple choice fields
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the default value of the field
     *
     * @param mixed $defaultValue
     * @return mixed
     */
    public function defaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * Get the default value of the field
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        $defaultValue = $this->defaultValue;

        if (is_null($defaultValue)) {
            if ($this->valueIsBoolean()) {
                $defaultValue = false;
            }
            if ($this->valueIsArray()) {
                $defaultValue = array();
            }
        }

        return $defaultValue;
    }

    /**
     * Get an identifier for the field
     *
     * @return string
     */
    private function getId()
    {
        return $this->getPage() . '-' . $this->getSection() . '-' . $this->getSlug();
    }

    /**
     * Set a custom callback that renders the field
     *
     * @param mixed $callback
     * @return $this
     */
    public function callback($callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Get the callback that renders the field
     *
     * @return mixed
     */
    public function getCallback()
    {
        if ($this->callback) {
            return $this->callback;
        }

        return array(&$this, 'render');
    }

    /**
     * Calls the WP do_action function
     * Uses action name with the format "rfs/$tag:page_slug.section_slug.field_slug"
     *
     * @param string $tag
     */
    private function doAction($tag)
    {
        do_action('rfs/' . $tag . ':' . $this->getPage() . '.' . $this->getSection() . '.' . $this->getSlug(), $this);
    }

    /**
     * Initialize the field
     */
    public function init()
    {
        $this->doAction('pre_init');

        add_settings_field(
            $this->getId(),
            $this->getName(),
            $this->getCallback(),
            $this->getPage(),
            $this->getSection()
        );

        $this->doAction('post_init');
    }

    /**
     * Gets the settings value for this field
     *
     * @return mixed
     */
    public function getValue()
    {
        $value = null;

        $options = get_option( $this->getPage() );

        if (isset($options[$this->getSection()][$this->getSlug()])) {
            $value = $options[$this->getSection()][$this->getSlug()];
        }

        if (is_null($value)) {
            $value = $this->getDefaultValue();
        }

        return $value;
    }

    /**
     * Get the field's escaped value
     *
     * @return string
     */
    public function getEscapedValue()
    {
        return htmlspecialchars($this->getValue());
    }

    /**
     * Test if the field is a given type
     *
     * @param string $type
     * @return boolean
     */
    public function isType($type)
    {
        return $type == $this->getType();
    }

    /**
     * Check if this field's value should be a boolean
     *
     * @return boolean
     */
    private function valueIsBoolean()
    {
        return $this->isType('checkbox') && ! $this->getOptions();
    }

    /**
     * Check if this field's value should be an array
     *
     * @return boolean
     */
    private function valueIsArray()
    {
        if ($this->isType('checkbox') && $this->getOptions()) {
            return true;
        }

        if ($this->isType('post_type') && is_array($this->getDefaultValue())) {
            return true;
        }

        return false;
    }

    /**
     * Convert the input value to something we want to store.
     *
     * @param mixed $value
     * @return mixed
     */
    public function sanitize($value)
    {
        if ($this->valueIsBoolean()) {
            $value = !!$value;
        }

        return $value;
    }

    /**
     * Gets a value to use for the HTML input's name attribute
     *
     * @return string
     */
    private function getFieldName()
    {
        return $this->getPage() . '[' . $this->getSection() . '][' . $this->getSlug() . ']';
    }

    private function renderTextField()
    {
        $html = '<input type="text" id="' . $this->getId() . '" name="' . $this->getFieldName() . '" value="' . $this->getEscapedValue() . '"/>';
        $html .= ' <label for="' . $this->getId() . '">' . $this->getDescription() . '</label>';

        return $html;
    }

    private function renderCheckbox()
    {
        $html = '';

        if (is_array($this->getOptions())) {
            $html .= $this->renderMultiCheckbox();
        } else {
            $html .= $this->renderSingleCheckbox();
        }

        return $html;
    }

    private function renderSingleCheckbox()
    {
        $html = '';
        $html .= '<label for="' . $this->getId() . '">';
        $html .= '<input type="checkbox" id="' . $this->getId() . '" name="' . $this->getFieldName() . '" ' . checked($this->getValue(), true, false) . '/>';
        $html .= ' ' . $this->getDescription() . '</label>';

        return $html;
    }

    private function renderMultiCheckbox()
    {
        $checkboxes = array();

        foreach ($this->getOptions() as $key => $value) {
            $checkbox = '<label for="' . $this->getId() . '-' . $key . '">';
            $checkbox .= '<input type="checkbox" id="' . $this->getId() . '-' . $key . '" name="' . $this->getFieldName() . '[]" value="' . $key . '" ' . checked(in_array($key, $this->getValue()), true, false) . '/>';
            $checkbox .= ' ' . $value . '</label>';

            $checkboxes[] = $checkbox;
        }

        $html = '<p>' . $this->getDescription() . '</p>';
        $html .= implode('<br>', $checkboxes);

        return $html;
    }

    private function renderTextarea($class = false)
    {
        if ( is_array( $class ) ) {
            $class = implode( ' ', $class );
        }
        $html = '<textarea id="' . $this->getId() . '"' . ( $class ? ' class="' . $class . '"' : '' ) . ' name="' . $this->getFieldName() . '" style="width:420px;height:200px;">' . $this->getEscapedValue() . '</textarea>';
        if ($this->getDescription()) $html .= '<p>' . $this->getDescription() . '</p>';

        return $html;
    }

    private function renderRadio()
    {
        $html = '';

        if ($this->getDescription()) $html .= '<p>' . $this->getDescription() . '</p>';

        $radios = array();
        foreach ($this->getOptions() as $key => $value) {
            $radio = '<label for="' . $this->getId() . '-' . $key . '">';
            $radio .= '<input type="radio" id="' . $this->getId() . '-' . $key . '" name="' . $this->getFieldName() . '" value="' . $key . '" ' . checked($key, $this->getValue(), false) . '/>';
            $radio .= ' ' . $value . '</label>';

            $radios[] = $radio;
        }

        $html .= implode('<br>', $radios);

        return $html;
    }

    public function renderSelect()
    {
        $html = '<select id="' . $this->getId() . '" name="' . $this->getFieldName() . '">';
        foreach ($this->getOptions() as $key => $value) {
            $html .= '<option value="' . $key . '" ' . selected($key, $this->getValue(), false) . '>' . $value . '</option>';
        }
        $html .= '</select>';
        $html .= ' <label for="' . $this->getId() . '"> ' . $this->getDescription() . '</label>';

        return $html;
    }

    public function render()
    {
		$html = '';
		switch ($this->getType()) {
			case 'text':
                $html .= $this->renderTextField();
				break;

			case 'textarea':
                $html .= $this->renderTextarea();
				break;

			case 'checkbox':
                $html .= $this->renderCheckbox();
				break;

			case 'radio':
                $html .= $this->renderRadio();
				break;
			
			case 'select':
                $html .= $this->renderSelect();
				break;

			case 'post_type':
				$post_types = get_post_types( array( 'public' => true ), 'objects' );
                $options = array();
                foreach ($post_types as $post_type) {
                    $options[$post_type->name] = $post_type->labels->name;
                }
                $this->options($options);
                if ($this->valueIsArray()) {
                    $html .= $this->renderCheckbox();
                } else {
                    $html .= $this->renderSelect();
                }
				break;
			
			case 'taxonomy':
				if ( $args['description'] ) $html .= $args['description'] . '<br>';
				$taxonomies = $this->get_taxonomies_array( false );
				$current_taxonomies = $this->options[$args['group']][$args['slug']];
				if ( !is_array( $current_taxonomies ) ) {
					$current_taxonomies = array();
				}
				$i = 0;
				foreach ( $taxonomies as $taxonomy => $taxonomy_display_name ) {
					$i++;
					$html .= '<label for="' . $this->slug . '-' . $args['slug'] . '-' . $taxonomy . '">';
					$html .= '<input type="checkbox" id="' . $this->slug . '-' . $args['slug'] . '-' . $taxonomy . '" name="' . $this->slug . '[' . $args['group'] . '][' . $args['slug'] . '][]" value="' . $taxonomy . '" ' . ( in_array( $taxonomy, $current_taxonomies ) ? 'checked="checked"' : '' ) . '/>';
					$html .= ' ' . $taxonomy_display_name . '</label>';
					if ( $i != count( $taxonomies ) ) $html .= '<br>';
				}
				break;
			
			default:
				# code...
				break;
		}
		echo $html;
    }
}

endif;
