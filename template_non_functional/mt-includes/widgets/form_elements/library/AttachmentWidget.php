<?php
namespace Website\Widgets\FormElements; use Moto; use Zend; class AttachmentWidget extends AbstractFormInputWidget { protected $_name = 'form_elements.attachment'; protected static $_defaultProperties = array( 'name' => '', 'placeholder' => '', 'type' => 'file', 'validation' => array( 'required' => false, 'fileExtension' => array( 'allowed' => true, 'value' => '' ), 'fileSize' => array( 'allowMax' => true, 'maxValue' => '500kb' ), ), 'buttons' => array(), 'spacing' => array( 'top' => 'auto', 'right' => 'auto', 'bottom' => 'small', 'left' => 'auto', ), 'spaceBetweenControls' => 'small', ); public function getTemplatePath($preset = null) { return '@websiteWidgets/form_elements/templates/attachment.twig.html'; } public function isVisibleInMessage() { return false; } public function createInputFilter($factory) { $specification = array( 'name' => $this->properties['name'], 'type' => 'Moto\InputFilter\FileInput', 'filters' => array( array( 'name' => 'Moto\Application\FileManager\Filter\RenameUpload', 'options' => array( 'target' => Moto\System::getAbsolutePath('@tempUploads'), 'overwrite' => false, 'randomize' => true, 'use_upload_name' => false, ) ), ), ); $specification['required'] = (boolean) $this->getPropertyValue('validation.required'); $validators = array( array( 'name' => 'Zend\Validator\File\UploadFile', ), ); $validator = $this->getPropertyValue('validation.fileSize'); if ($validator) { $options = array(); if (Moto\Util::getValue($validator, 'allowMin')) { $options['min'] = Moto\Util::getValue($validator, 'minValue'); } if (Moto\Util::getValue($validator, 'allowMax')) { $options['max'] = Moto\Util::getValue($validator, 'maxValue'); } if (!empty($options)) { $validators[] = array( 'name' => 'File\Size', 'options' => $options ); } } $validator = $this->getPropertyValue('validation.fileExtension'); if ($validator) { $options = array(); if (Moto\Util::getValue($validator, 'allowed')) { $value = Moto\Util::getValue($validator, 'value', ''); $value = trim($value); $value = str_replace(array('*', '.',), '', $value); $value = str_replace(' ', ',', $value); $value = preg_replace('/[\,]+/', ',', $value); if ($value !== '') { $options['extension'] = $value; } } if (!empty($options)) { $validators[] = array( 'name' => 'File\Extension', 'options' => $options ); } } $specification['validators'] = $validators; return $factory->createInput($specification); } public function getValidationRules() { $rules = parent::getValidationRules(); if (is_array($rules)) { $rule = $this->getPropertyValue('validation.fileSize'); if ($rule) { if (Moto\Util::getValue($rule, 'allowMax')) { $rules['maxFileSize'] = Moto\Util::getValue($rule, 'maxValue'); } } $rule = $this->getPropertyValue('validation.fileExtension'); if ($rule) { if (Moto\Util::getValue($rule, 'allowed')) { $rules['fileExtension'] = Moto\Util::getValue($rule, 'value'); } } } return $rules; } } 