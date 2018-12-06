<?php
namespace Moto\Application\Settings\InputFilter; use Moto\InputFilter\AbstractInputFilter; use Moto; class LiveChatSettings extends AbstractInputFilter { protected $_name = 'settingsWebsite.LiveChatSettings'; public function init() { $this->add(array( 'name' => 'provider', 'required' => true, 'allow_empty' => true, 'filters' => array( array('name' => 'StripTags'), array('name' => 'StringTrim'), ), 'continue_if_empty' => true, 'validators' => array( array( 'name' => 'InArray', 'options' => array( 'haystack' => array('', 'none', 'LiveChatInc') ) ), ), )); $options = new AbstractInputFilter(); $visibility = new AbstractInputFilter(); $visibility->add(array( 'name' => 'notFoundPage', 'required' => false, 'filters' => array( array('name' => 'Moto\Filter\IntValue'), array('name' => 'Boolean'), ) )); $visibility->add(array( 'name' => 'underConstructionPage', 'required' => false, 'filters' => array( array('name' => 'Moto\Filter\IntValue'), array('name' => 'Boolean'), ) )); $options->add($visibility, 'visibility'); $this->add($options, 'options'); $providers = new AbstractInputFilter(); $LiveChatInc = new AbstractInputFilter(); $LiveChatInc->add(array( 'name' => 'licenceNumber', 'required' => false, 'filters' => array( array('name' => 'StripTags'), array('name' => 'StringTrim'), ), 'validators' => array( array( 'name' => 'StringLength', 'options' => array( 'encoding' => 'UTF-8', 'min' => 1, 'max' => 512, ), ), ), )); $LiveChatInc->add(array( 'name' => 'showOnlyAgentsAreAvailable', 'required' => false, 'filters' => array( array('name' => 'Moto\Filter\IntValue'), array('name' => 'Boolean'), ), )); $providers->add($LiveChatInc, 'LiveChatInc'); $this->add($providers, 'providers'); } } 