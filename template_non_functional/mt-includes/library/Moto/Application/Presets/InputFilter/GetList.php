<?php
namespace Moto\Application\Presets\InputFilter; use Moto; class GetList extends Moto\InputFilter\AbstractInputFilter { protected $_name = 'presets.list'; public function init() { $this->add(array( 'name' => 'widget_name', 'required' => false, 'filters' => array( array('name' => 'StripTags'), array('name' => 'StringTrim'), array('name' => 'StringToLower'), ), 'validators' => array( array( 'name' => 'StringLength', 'options' => array( 'encoding' => 'UTF-8', 'min' => 1, 'max' => 32, ), ), ), )); } }