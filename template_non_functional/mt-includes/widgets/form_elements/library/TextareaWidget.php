<?php
namespace Website\Widgets\FormElements; use Moto; class TextareaWidget extends AbstractFormInputWidget { protected $_name = 'form_elements.textarea'; protected static $_defaultProperties = array( 'name' => '', 'placeholder' => '', 'styles' => array( 'desktop' => array( 'base' => array( 'height' => '140px', ), ), 'tablet' => array( 'base' => array( 'height' => '', ), ), 'mobile-v' => array( 'base' => array( 'height' => '', ), ), 'mobile-h' => array( 'base' => array( 'height' => '', ), ) ), 'validation' => array( 'required' => false ), 'spacing' => array( 'top' => 'auto', 'right' => 'auto', 'bottom' => 'small', 'left' => 'auto', ), ); public function getTemplatePath($preset = null) { return '@websiteWidgets/form_elements/templates/textarea.twig.html'; } public function isValueMultiline() { return true; } public function getFieldValue() { $twig = $this->getRenderEngine(); if ($twig) { return twig_escape_filter($twig, $this->_fieldValue); } return htmlspecialchars($this->_fieldValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } } 