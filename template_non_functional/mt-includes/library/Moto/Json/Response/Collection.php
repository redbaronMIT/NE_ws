<?php
namespace Moto\Json\Response; class Collection { public $meta; public $records = array(); public function __construct($data = null) { $this->meta = new Collection\Meta(); if (null !== $data) $this->exchangeArray($data); } public function exchangeArray($data) { if (!empty($data['meta'])) $this->meta->exchangeArray($data['meta']); if (!empty($data['records'])) $this->records = $data['records']; } }