<?php
namespace Moto\Authentication; use Moto; Service::init(); final class Service { protected static $_initialized = false; protected static $_authenticationService = null; public static function init() { if (static::$_initialized) { return; } static::$_initialized = true; } protected static function _getAuthenticationService() { if (null == static::$_authenticationService) { static::$_authenticationService = Moto\Authentication\AuthenticationService::getInstance(); } return static::$_authenticationService; } public static function login($credentials = array()) { $user = static::_getAuthenticationService()->login($credentials); return $user; } public static function isAuthenticated($returnUser = false) { $user = static::_getAuthenticationService()->getIdentity(); return ($returnUser ? $user : (!!$user)); } public static function logout() { return static::_getAuthenticationService()->clearIdentity(); } public static function getUser() { return static::_getAuthenticationService()->getIdentity(); } public static function setUser($user) { if (Moto\System::isInstalled() || !$user instanceof Moto\Application\Users\UserModel) { return null; } static::_getAuthenticationService()->getStorage()->write($user); return static::_getAuthenticationService()->getIdentity(); } public static function updateUser($user) { return static::_getAuthenticationService()->updateIdentity($user); } public static function isSessionExpired() { return static::_getAuthenticationService()->isSessionExpired(); } }