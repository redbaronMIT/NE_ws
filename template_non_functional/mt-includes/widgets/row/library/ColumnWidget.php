<?php
namespace Website\Widgets\Row; use Moto; class ColumnWidget extends Moto\System\Widgets\AbstractContainerWidget { protected $_name = 'row.column'; protected static $_defaultProperties = array( 'size' => 12, 'styles' => null, ); protected $_templateType = 'templates'; protected $_templatePath = '@websiteWidgets/row/templates/column.twig.html'; protected $_widgetId = false; public function getCssClasses() { $result = ''; $result .= ' col-' . $this->_parent->getPropertyValue('grid', 'sm') . '-' . $this->getPropertyValue('size', 12); if ($this->getPropertyValue('styles.background-color', false)) { $result .= ' ' . $this->getClassNameByBgColorName(); } $result .= ' ' . $this->getSpacing('classes'); return $result; } public function getClassNameByBgColorName() { return $this->getCssClassColor($this->getPropertyValue('styles.background-color'), 'moto-bg-'); } public function getStylesValue() { return $this->getPropertyValue('styles'); } public function _getBackgroundVideoCssClass() { return 'moto-background-video moto-absolute-position'; } } 