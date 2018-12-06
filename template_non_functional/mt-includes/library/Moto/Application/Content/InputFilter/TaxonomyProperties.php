<?php
namespace Moto\Application\Content\InputFilter; use Moto; class TaxonomyProperties extends Moto\InputFilter\AbstractInputFilter { protected $_name = 'content.taxonomy:properties'; public function init() { $meta = new Moto\Application\Pages\InputFilter\PagePropertiesMeta(); $meta->add(array( 'name' => 'title', 'required' => false, 'filters' => array( array('name' => 'StripTags'), array('name' => 'StringTrim'), ), 'validators' => array( array( 'name' => 'StringLength', 'options' => array( 'encoding' => 'UTF-8', 'max' => 200, ), ), ), )); $this->add($meta, 'meta'); } } 