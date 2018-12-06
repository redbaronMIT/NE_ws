<?php
namespace Moto\Application\Fonts; use Moto\Json\Server; use Moto; class Service extends Moto\Service\AbstractStaticService { protected static $_resourceName = 'fonts'; protected static $_resourcePrivilegesMap = array( 'getActiveList' => 'get', ); public static function getActiveList() { $table = new FontsTable(); $table->useResultAsModel(true); $whereActive = array( 'active' => '1' ); $items = $table->getList($whereActive); if (!$items) { throw new Server\Exception(Moto\System\Exception::ERROR_NOT_FOUND_MESSAGE, Moto\System\Exception::ERROR_NOT_FOUND_CODE); } $result = new Moto\Json\Response\Collection($items); return $result; } public static function delete($id) { $id = (int) $id; $table = new FontsTable(); $table->useResultAsModel(true); $record = $table->getById($id); if (!$record) { throw new Server\Exception(Moto\System\Exception::ERROR_NOT_FOUND_MESSAGE, Moto\System\Exception::ERROR_NOT_FOUND_CODE); } if ($record->provider === 'system') { throw new Server\Exception(Moto\System\Exception::ERROR_PERMISSION_DENIED_MESSAGE, Moto\System\Exception::ERROR_PERMISSION_DENIED_CODE, array('reason' => 'SYSTEM')); } if ($record->is_protected) { throw new Server\Exception(Moto\System\Exception::ERROR_PERMISSION_DENIED_MESSAGE, Moto\System\Exception::ERROR_PERMISSION_DENIED_CODE, array('reason' => 'PROTECTED')); } $result = $table->deleteById($id); if ($result) { Moto\System\Style::buildFonts(); } return $result; } public static function save($request = null) { if (null === $request) { $request = static::getRequest()->getParams(); } $isNew = empty($request['id']); if ($isNew) { $filter = new InputFilter\AddFont(); } else { $filter = new InputFilter\UpdateFont(); } $filter->setData($request); if (!$filter->isValid()) { throw new Server\Exception(Moto\System\Exception::ERROR_BAD_REQUEST_MESSAGE, Moto\System\Exception::ERROR_BAD_REQUEST_CODE, $filter->getMessagesKeys()); } $values = $filter->getValues(true); $table = new FontsTable(); $table->useResultAsModel(true); if ($isNew) { $record = new FontModel(); } else { $record = $table->getById($values['id']); if (!$record) { throw new Server\Exception(Moto\System\Exception::ERROR_NOT_FOUND_MESSAGE, Moto\System\Exception::ERROR_NOT_FOUND_CODE); } } $record->setFromArray($values); $record->activate = true; if ($table->save($record)) { Moto\System\Style::buildFonts(); } return $record; } public function getById($id) { $id = (int) $id; $table = new FontsTable(); $table->useResultAsModel(true); $record = $table->getById($id); if (!$record) { throw new Server\Exception(Moto\System\Exception::ERROR_NOT_FOUND_MESSAGE, Moto\System\Exception::ERROR_NOT_FOUND_CODE); } if ($record->provider && $record->provider !== 'system') { $info = Moto\Application\FontsManager\Service::getFontByFamilyAndProvider($record->name, $record->provider); if ($info) { $record->variants = $info->variants; $record->subsets = $info->subsets; $record->version = $info->version; $record->last_modified = $info->last_modified; $table->save($record); } } return $record; } } 