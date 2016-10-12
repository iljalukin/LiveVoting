<?php
/**
 * Created by PhpStorm.
 * User: nschaefli
 * Date: 9/30/16
 * Time: 5:11 PM
 */

namespace LiveVoting\Cache\Version\v52;


use LiveVoting\Cache\xlvoCacheService;
use RuntimeException;

require_once('./Services/GlobalCache/classes/class.ilGlobalCache.php');

/**
 * Class xoctCache
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class xlvoCache extends \ilGlobalCache implements xlvoCacheService {

    const COMP_PREFIX = 'xlvo';
    /**
     * @var bool
     */
    protected static $override_active = false;
    /**
     * @var array
     */
    protected static $active_components = array(
        self::COMP_PREFIX,
    );


    /**
     * @return xlvoCache
     */
    public static function getInstance() {
        require_once('./include/inc.ilias_version.php');

        $service_type = self::getSettings()->getService();
        $xlvoCache = new self($service_type);
        $xlvoCache->initCachingService();

        $xlvoCache->setActive(true);
        self::setOverrideActive(true);

        return $xlvoCache;
    }


    protected function initCachingService() {
        /**
         * @var $ilGlobalCacheService \ilGlobalCacheService
         */
        if (!$this->getComponent()) {
            $this->setComponent('LiveVoting');
        }
        $serviceName = self::lookupServiceClassName($this->getServiceType());
        $ilGlobalCacheService = new $serviceName(self::$unique_service_id, $this->getComponent());
        $ilGlobalCacheService->setServiceType($this->getServiceType());
        $this->global_cache = $ilGlobalCacheService;
        $this->setActive(in_array($this->getComponent(), self::getActiveComponents()));
    }


    /**
     * @param $service_type
     *
     * @return string
     */
    public static function lookupServiceClassName($service_type) {
        switch ($service_type) {
            case self::TYPE_APC:
                return 'ilApc';
                break;
            case self::TYPE_MEMCACHED:
                return 'ilMemcache';
                break;
            case self::TYPE_XCACHE:
                return 'ilXcache';
                break;
            default:
                return 'ilStaticCache';
                break;
        }
    }


    /**
     * @return array
     */
    public static function getActiveComponents() {
        return self::$active_components;
    }


    /**
     * @param bool $complete
     *
     * @return bool
     * @throws RuntimeException
     */
    public function flush($complete = false) {
        if (!$this->global_cache instanceof \ilGlobalCacheService || !$this->isActive()) {
            return false;
        }

        return parent::flush($complete); // TODO: Change the autogenerated stub
    }


    /**
     * Manually removes a cached value.
     *
     * @param string $key The unique key which represents the value.
     *
     * @throws RuntimeException
     * @return bool
     */
    public function delete($key) {
        if (!$this->global_cache instanceof \ilGlobalCacheService || !$this->isActive()) {
            return false;
        }

        return parent::delete($key);
    }


    /**
     * @return bool
     */
    public function isActive() {
        return self::isOverrideActive();
    }


    /**
     * @return boolean
     */
    public static function isOverrideActive() {
        return self::$override_active;
    }


    /**
     * @param boolean $override_active
     */
    public static function setOverrideActive($override_active) {
        self::$override_active = $override_active;
    }


    /**
     * @param string    $key     An unique key.
     * @param mixed     $value   Serializable object or string.
     * @param null      $ttl     Time to life measured in seconds.
     *
     * @return bool              True if the cache entry was set otherwise false.
     */
    public function set($key, $value, $ttl = null) {
        //		$ttl = $ttl ? $ttl : 480;
        if (!$this->global_cache instanceof \ilGlobalCacheService || !$this->isActive()) {
            return false;
        }
        $this->global_cache->setValid($key);

        return $this->global_cache->set($key, $this->global_cache->serialize($value), $ttl);
    }


    /**
     * @param $key
     *
     * @return bool|mixed|null
     */
    public function get($key) {
        if (!$this->global_cache instanceof \ilGlobalCacheService || !$this->isActive()) {
            return false;
        }
        $unserialized_return = $this->global_cache->unserialize($this->global_cache->get($key));

        if ($unserialized_return) {
            if ($this->global_cache->isValid($key)) {
                return $unserialized_return;
            }
        }

        return null;
    }
}