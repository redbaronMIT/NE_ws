<?php
namespace Moto; use Moto\Application\MediaLibrary\MediaItemsTable; use Moto; use Zend; use Traversable; class Util { const DIR_PERMISSION = 0775; const FILE_PERMISSION = 0775; protected static $_lastUniqueId = ''; public static function arrayValuesAssoc($array, $key) { $result = array(); foreach ($array as $value) { if (array_key_exists($key, $value)) { $result[] = $value[$key]; } } return $result; } public static function arrayToTree(array $elements, $parentId = 0, $idKey = 'id', $parentKey = 'parent_id', $childrenKey = 'children') { $branch = array(); foreach ($elements as $i => $element) { $elementParentId = is_object($element) ? $element->$parentKey : $element[$parentKey]; $elementId = is_object($element) ? $element->$idKey : $element[$idKey]; if ($elementParentId == $parentId) { $children = static::arrayToTree($elements, $elementId, $idKey, $parentKey, $childrenKey); if ($children) { if (is_object($element)) { $element->$childrenKey = $children; } else { $element[$childrenKey] = $children; } } $branch[$elementId] = $element; unset($elements[$i]); } } $branch = array_values($branch); return $branch; } public static function createDir($path, $mode = null, $recursive = true) { if ($mode === null) { $mode = static::DIR_PERMISSION; } if (file_exists($path)) { $result = is_dir($path); } else { $result = @mkdir($path, $mode, $recursive); @chmod($path, $mode); } return $result; } static public function filePutContents($filename, $data, $flag = null, $context = null) { $dir = dirname($filename); if (!is_dir($dir)) { static::createDir($dir); } $result = file_put_contents($filename, $data, $flag, $context); static::fixFilePermission($filename); return $result; } public static function getFilePermission($file, $dec = false) { if (!file_exists($file)) { return null; } $permission = substr(sprintf('%o', fileperms($file)), -4); if (!$dec) { $permission = octdec($permission); } return $permission; } public static function fixFilePermission($path, $mode = null) { if ($mode == null) { $mode = (is_dir($path) ? static::DIR_PERMISSION : (static::FILE_PERMISSION & ~umask())); } $permission = static::getFilePermission($path); if ($permission != null && $permission < $mode) { @chmod($path, $mode); } return true; } public static function getFullPath($path, $realPath = false) { if (!is_string($path)) { return null; } $path = trim($path); if (empty($path)) { return null; } $rootPath = Moto\Config::get('rootPath'); if (strpos($path, $rootPath) !== 0 && substr($path, 0, 1) !== '/' && !preg_match('/^[a-z]:\//i', strtolower(substr($path, 0, 3)))) { $path = $rootPath . '/' . $path; } $path = preg_replace('/[\/\\\]+/', '/', $path); if ($realPath) { $path = realpath($path); } return $path; } public static function deleteDir($src, $killSrc = true) { if (!is_dir($src)) { return false; } $dir = opendir($src); if (!$dir) { return false; } while (false !== ($file = readdir($dir))) { if (($file !== '.') && ($file !== '..')) { if (is_dir($src . '/' . $file) && !is_link($src . '/' . $file)) { static::deleteDir($src . '/' . $file, true); } else { @unlink($src . '/' . $file); } } } closedir($dir); if ($killSrc) { rmdir($src); } return true; } public static function emptyDir($src) { return static::deleteDir($src, false); } public static function scanDir($root, $dir = '', $options = array(), $result = array()) { if (!is_array($options)) { $options = array(); } $options = array_merge(array( 'addDir' => false, 'compareFunction' => null, 'skipThisPathFunction' => null, ), $options); if (is_callable($options['skipThisPathFunction']) && $options['skipThisPathFunction']($root, $dir)) { return $result; } $compareFunction = null; if (is_callable($options['compareFunction'])) { $compareFunction = $options['compareFunction']; } $scanSubDir = true; if (array_key_exists('maxLevel', $options)) { $scanSubDir = ($options['maxLevel'] > 0); $options['maxLevel']--; } $path = $root . '/' . $dir; if (!is_dir($path)) { return $result; } $list = scandir($path); if (!$list) { return $result; } for ($i = 0, $count = count($list); $i < $count; $i++) { $file = $list[$i]; if ($file === '.' || $file === '..') { continue; } if (is_dir($path . '/' . $file)) { if ($options['addDir']) { if ($compareFunction === null || $compareFunction($dir, $file, $root, 'dir')) { $result[] = ltrim($dir . '/' . $file, '/'); } } if ($scanSubDir) { $result = static::scanDir($root, $dir . '/' . $file, $options, $result); } } else { if ($compareFunction === null || $compareFunction($dir, $file, $root, 'file')) { $result[] = ltrim($dir . '/' . $file, '/'); } } } return $result; } public static function copyFile($from, $to, $rewrite = true) { if (!is_file($from)) { return false; } if (is_file($to)) { if ($rewrite) { unlink($to); } else { return false; } } if (!is_dir(dirname($to))) { static::createDir(dirname($to)); } $result = copy($from, $to); static::fixFilePermission($to); return $result; } public static function moveFile($from, $to, $rewrite = true) { if (!is_file($from)) { return false; } if (is_file($to)) { if ($rewrite) { unlink($to); } else { return false; } } if (!is_dir(dirname($to))) { static::createDir(dirname($to)); } $result = rename($from, $to); static::fixFilePermission($to); return $result; } public static function copyFiles($files, $fromDir, $toDir, $rewrite = true) { if (!is_dir($fromDir)) { return false; } if (!is_dir($toDir)) { static::createDir($toDir); } if (!is_dir($toDir)) { return false; } for ($i = 0, $count = count($files); $i < $count; $i++) { $from = $fromDir . '/' . $files[$i]; $to = $toDir . '/' . $files[$i]; if (is_dir($from)) { static::createDir($to); } else { static::copyFile($from, $to, $rewrite); } } return true; } public static function moveFiles($files, $fromDir, $toDir, $rewrite = true) { if (!is_dir($fromDir)) { return false; } if (!is_dir($toDir)) { static::createDir($toDir); } if (!is_dir($toDir)) { return false; } for ($i = 0, $count = count($files); $i < $count; $i++) { $from = $fromDir . '/' . $files[$i]; $to = $toDir . '/' . $files[$i]; if (is_dir($from)) { static::createDir($to); } else { static::moveFile($from, $to, $rewrite); } } return true; } public static function copyDir($source, $destination, $rewrite = true) { if (!is_dir($source)) { return false; } if (!is_dir(dirname($destination))) { static::createDir($destination); } $params = array( 'addDir' => true ); $files = static::scanDir($source, '', $params); if (!count($files)) { return false; } static::copyFiles($files, $source, $destination, $rewrite); return true; } public static function getUniqueId($level = 10) { $id = uniqid(); if ($level < 1) { return $id; } if (static::$_lastUniqueId === $id) { usleep(1); return static::getUniqueId(--$level); } static::$_lastUniqueId = $id; return $id; } public static function setToArrayDeep(&$array, $name, $value) { $name = trim($name, '.'); if (array_key_exists($name, $array)) { $array[$name] = $value; return; } if (strpos($name, '.')) { $parts = explode('.', $name, 2); if (!array_key_exists($parts[0], $array)) { $array[$parts[0]] = array(); } return static::setToArrayDeep($array[$parts[0]], $parts[1], $value); } $array[$name] = $value; } public static function getFromArrayDeep($array, $name, $default = null) { if (empty($array)) { return $default; } if (array_key_exists($name, $array)) { return $array[$name]; } if (strpos($name, '.')) { $parts = explode('.', $name, 2); $result = static::getFrom($array, $parts[0], $default); if (is_array($result)) { return static::getFromArrayDeep($result, $parts[1], $default); } elseif (is_object($result)) { return static::getFromObjectDeep($result, $parts[1], $default); } else { return $result; } } return $default; } public static function getFromObjectDeep($object, $name, $default = null) { if (empty($object)) { return $default; } if (isset($object->$name)) { return $object->{$name}; } if (strpos($name, '.')) { $parts = explode('.', $name, 2); $result = static::getFrom($object, $parts[0], $default); if (is_object($result)) { return static::getFromObjectDeep($result, $parts[1], $default); } elseif (is_array($result)) { return static::getFromArrayDeep($result, $parts[1], $default); } else { return $result; } } return $default; } public static function getFrom($source, $key, $default = null) { if (is_array($source)) { return array_key_exists($key, $source) ? $source[$key] : $default; } if (is_object($source)) { return isset($source->{$key}) ? $source->{$key} : $default; } return $default; } public static function getValue($source, $itemPath, $default = null) { if (empty($itemPath)) { return $source; } if (empty($source)) { return $default; } if (!is_array($itemPath)) { $itemPath = explode('.', $itemPath); } foreach ($itemPath as $key) { if (is_array($source)) { if (!array_key_exists($key, $source)) { return $default; } $source = $source[$key]; } elseif (is_object($source)) { if (!isset($source->{$key})) { return $default; } $source = $source->{$key}; } else { return $default; } } return $source; } public static function generateThumbnailResize($pathToDir, $imageName, $sourceImage, $sourceImageWidth, $sourceImageHeight, $sourceImageType, $sourceAspectRatio, $thumbnailInfo) { $maxWidth = $thumbnailInfo->width; $maxHeight = $thumbnailInfo->height; $options = $thumbnailInfo->options; $thumbnailAspectRatio = $maxWidth / $maxHeight; if ($sourceImageWidth <= $maxWidth && $sourceImageHeight <= $maxHeight) { $thumbnailImageWidth = $sourceImageWidth; $thumbnailImageHeight = $sourceImageHeight; } elseif ($thumbnailAspectRatio > $sourceAspectRatio) { $thumbnailImageWidth = (int) ($maxHeight * $sourceAspectRatio); $thumbnailImageHeight = $maxHeight; } else { $thumbnailImageWidth = $maxWidth; $thumbnailImageHeight = (int) ($maxWidth / $sourceAspectRatio); } if (($thumbnailImageWidth == $sourceImageWidth && $thumbnailImageHeight == $sourceImageHeight) || $thumbnailImageWidth == 0 || $thumbnailImageHeight == 0) { return array("isCreated" => false); } $thumbnailImage = imagecreatetruecolor($thumbnailImageWidth, $thumbnailImageHeight); switch ($sourceImageType) { case IMAGETYPE_PNG: imagealphablending($thumbnailImage, false); imagesavealpha($thumbnailImage, true); break; } imagecopyresampled($thumbnailImage, $sourceImage, 0, 0, 0, 0, $thumbnailImageWidth, $thumbnailImageHeight, $sourceImageWidth, $sourceImageHeight); $extension = strrchr($imageName, "."); $imageNameWithoutExtension = substr($imageName, 0, -strlen($extension)); $thumbnailImagePath = Moto\System::getAbsolutePath('@userUploads') . '/' . $pathToDir . 'thumbnails/' . $imageNameWithoutExtension . "_" . $thumbnailInfo->name . "_" . $thumbnailImageWidth . "x" . $thumbnailImageHeight . $extension; switch ($sourceImageType) { case IMAGETYPE_JPEG: $quality = $options->quality ? $options->quality : 80; imagejpeg($thumbnailImage, $thumbnailImagePath, $quality); break; case IMAGETYPE_PNG: $compression = $options->compression ? $options->compression : 7; imagepng($thumbnailImage, $thumbnailImagePath, $compression); break; } imagedestroy($thumbnailImage); return array( "isCreated" => true, "realWidth" => $thumbnailImageWidth, "realHeight" => $thumbnailImageHeight); } public static function generateSystemThumbnails($pathToDir, $imageName, $item) { $thumbnails = Moto\Website\Settings::get('thumbnails', null); if ($thumbnails === null) { return array(); } static::setMemoryLimit(Moto\Config::get('systemRecommend.memory_limit', '')); $sourceImagePath = Moto\System::getAbsolutePath('@userUploads/' . $pathToDir . $imageName); list($sourceImageWidth, $sourceImageHeight, $sourceImageType) = getimagesize($sourceImagePath); $sourceImage = null; switch ($sourceImageType) { case IMAGETYPE_JPEG: $sourceImage = imagecreatefromjpeg($sourceImagePath); break; case IMAGETYPE_PNG: $sourceImage = imagecreatefrompng($sourceImagePath); break; } if (!$sourceImage) { return array(); } $sourceAspectRatio = $sourceImageWidth / $sourceImageHeight; if (is_string($thumbnails)) { $thumbnails = json_decode($thumbnails); } $table = new MediaItemsTable(); $generatedThumbnails = array(); foreach ($thumbnails as $thumbnail) { $result = Moto\Util::generateThumbnailResize($pathToDir, $imageName, $sourceImage, $sourceImageWidth, $sourceImageHeight, $sourceImageType, $sourceAspectRatio, $thumbnail); if ($result["isCreated"]) { $generatedThumbnails[$thumbnail->name] = array( "width" => $result['realWidth'], "height" => $result['realHeight'] ); $item->thumbnails = json_encode($generatedThumbnails); $table->update($item); } } imagedestroy($sourceImage); return $generatedThumbnails; } public static function setMemoryLimit($limit) { $currentLimit = @ini_get('memory_limit'); if (!$currentLimit) { return; } $limit = trim($limit); $currentLimit = static::convertSizeStringToInteger($currentLimit); $limitInByte = static::convertSizeStringToInteger($limit); if (is_null($currentLimit) || is_null($limitInByte)) { return; } if ($limitInByte > $currentLimit) { @ini_set('memory_limit', $limit); } } public static function convertSizeStringToInteger($size) { $size = trim($size); $value = null; if (preg_match('/^([0-9]+)\s*(P|T|G|M|K){0,1}/i', $size, $matches)) { $value = isset($matches[1]) ? (int) $matches[1] : 0; $unit = isset($matches[2]) ? strtoupper($matches[2]) : ''; switch ($unit) { case 'P': $value *= 1024; case 'T': $value *= 1024; case 'G': $value *= 1024; case 'M': $value *= 1024; case 'K': $value *= 1024; break; } } return $value; } public static function decodeValue($value, $type = null) { switch ($type) { case 'int': case 'integer': return (int) $value; case 'float': return (float) $value; case 'string': return (string) $value; case 'bool': case 'boolean': if (is_bool($value)) { return $value; } if (is_string($value)) { return ((strtolower(trim($value)) === 'true') || $value === '1'); } return (bool) $value; case 'object': if (is_array($value)) { $value = json_encode($value); } return (is_string($value) ? json_decode($value) : $value); case 'array': if (is_object($value)) { $value = json_encode($value); } return (is_string($value) ? json_decode($value, true) : $value); default: return $value; } } public static function simpleRender($template, $data) { $vars = explode(',', '{{' . implode("}},{{", array_keys($data)) . '}}'); $values = array_values($data); return str_replace($vars, $values, $template); } public static function encrypt($text, $pass) { $level = error_reporting(); error_reporting(0); $result = trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $pass, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)))); error_reporting($level); return $result; } public static function decrypt($text, $pass) { $level = error_reporting(); error_reporting(0); $result = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $pass, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))); error_reporting($level); return $result; } public static function isFunctionDisabled($function) { static $list = false; if ($list === false) { $list = @ini_get('disable_functions'); $list = trim((string) $list); if (empty($list)) { $list = array(); } else { $list = explode(',', $list); $list = array_map('trim', $list); } } return in_array($function, $list); } public static function extendUrl($url, $parts, $options = array()) { if (!is_string($url) || !is_array($parts)) { return $url; } $urlParts = parse_url($url); if ((static::getFrom($options, 'query') === 'join') && !empty($urlParts['query']) && !empty($parts['query'])) { parse_str($urlParts['query'], $sourceQuery); if (is_string($parts['query'])) { parse_str($parts['query'], $targetQuery); } else { $targetQuery = $parts['query']; } $query = Zend\Stdlib\ArrayUtils::merge($sourceQuery, $targetQuery); $parts['query'] = http_build_query($query); } if ((static::getFrom($options, 'query') === 'strip')) { $parts['query'] = ''; } if (!empty($parts['query']) && is_array($parts['query'])) { $parts['query'] = http_build_query($parts['query']); } $parts = Zend\Stdlib\ArrayUtils::merge($urlParts, $parts); return static::buildUrl($parts); } public static function buildUrl($parts) { if (!is_array($parts)) { return null; } $url = ''; $url .= empty($parts['scheme']) ? '' : $parts['scheme'] . '://'; if (array_key_exists('user', $parts)) { $url .= empty($parts['user']) ? '' : $parts['user']; $url .= empty($parts['pass']) ? '' : ':' . $parts['pass']; $url .= '@'; } $url .= empty($parts['host']) ? '' : $parts['host']; $url .= empty($parts['port']) ? '' : ':' . $parts['port']; $url .= empty($parts['path']) ? '' : (in_array($parts['path'][0], array('.', '/')) ? '' : '/') . $parts['path']; $url .= empty($parts['query']) ? '' : '?' . $parts['query']; $url .= empty($parts['fragment']) ? '' : '#' . $parts['fragment']; return $url; } public static function isInnerUrl($url) { return (!empty($url) && !in_array($url[0], array('.', '/', ':', '#')) && !preg_match('/^([a-z]*:\/\/)/i', $url)); } public static function toSnakeCase($value, $delimiter = '_') { $value = trim($value); if (!ctype_lower($value)) { $value = ucwords($value); $value = preg_replace('/\s+/', '', $value); $value = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1' . $delimiter, $value)); } return $value; } public static function toCamelCase($value, $delimiters = '_-.') { if (is_string($delimiters)) { $delimiters = str_split($delimiters); } $value = trim($value); $value = strtolower($value); $value = str_replace($delimiters, ' ', $value); $value = ucwords($value); $value = str_replace(' ', '', $value); $value = lcfirst($value); return $value; } public static function toStudlyCase($value, $delimiters = '_-.') { if (is_string($delimiters)) { $delimiters = str_split($delimiters); } $value = trim($value); $value = strtolower($value); $value = str_replace($delimiters, ' ', $value); $value = ucwords($value); $value = str_replace(' ', '', $value); return $value; } public static function toArray($target) { if (is_array($target)) { return $target; } if (is_object($target) && method_exists($target, 'toArray')) { return $target->toArray(); } if ($target instanceof Traversable) { return iterator_to_array($target); } return (array) $target; } public static function toJson($target, $options = 0, $depth = 512) { if ($options === '@content') { $options = JSON_UNESCAPED_UNICODE; } if (!is_int($options)) { $options = 0; } if (is_object($target)) { if (method_exists($target, 'toArray')) { $target = $target->toArray(); } elseif (method_exists($target, 'toJson')) { return $target->toJson($options); } elseif (method_exists($target, 'jsonSerialize')) { $target = $target->jsonSerialize(); } } if (PHP_VERSION_ID < 50500) { return json_encode($target, $options); } return json_encode($target, $options, $depth); } public static function arrayHas($array, $key) { if (empty($array) || is_null($key)) { return false; } if (array_key_exists($key, $array)) { return true; } foreach (explode('.', $key) as $segment) { if (!is_array($array) || !array_key_exists($segment, $array)) { return false; } $array = $array[$segment]; } return true; } public static function arrayOnly($array, $keys) { return array_intersect_key($array, array_flip((array) $keys)); } public static function arrayExcept($array, $keys) { return array_diff_key($array, array_flip((array) $keys)); } public static function isResolvablePath($path) { if (!is_string($path) || strlen($path) < 1) { return false; } return ($path[0] === '@' || !preg_match('/^([a-z]+:)?\.?\/\/?/i', $path)); } public static function updateResourcePath($path, $namespace) { if (static::isResolvablePath($path)) { if ($path[0] !== '@') { $path = ($namespace[0] === '@' ? '' : '@') . $namespace . '/' . $path; } } return $path; } public static function generateRandomBytes($length) { $length = (int) $length; if ($length < 1) { return false; } if (function_exists('random_bytes')) { try { return random_bytes($length); } catch (\Exception $e) { if (Moto\System::isDevelopmentStage()) { Moto\System\Log::warning(__CLASS__ . ' : "random_bytes" throwing exception [ ' . $e->getCode() . ' ] ' . $e->getMessage()); } } } try { $result = openssl_random_pseudo_bytes($length); } catch (\Exception $e) { if (Moto\System::isDevelopmentStage()) { Moto\System\Log::warning(__CLASS__ . ' : "openssl_random_pseudo_bytes" throwing exception [ ' . $e->getCode() . ' ] ' . $e->getMessage()); } $result = false; } if ($result === false) { if (Moto\System::isDevelopmentStage()) { Moto\System\Log::warning(__CLASS__ . ' : "openssl_random_pseudo_bytes" return false'); } } else { return $result; } $result = ''; for ($i = 0; $i < $length; $i++) { $result .= chr(mt_rand(16, 254)); } return $result; } public static function sanitizePath($path) { return preg_replace('/[\/\\\]+/', '/', (string) $path); } public static function isTraversable($target) { return is_array($target) || $target instanceof Traversable; } } 