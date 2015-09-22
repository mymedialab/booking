<?php
namespace MML\Booking;

/**
 * Used as a simple databag for configuration variables. Defaults can be over-ridden using the setup method, extra keys can be added
 * using the add method.
 */
class Config
{
    protected $isDevMode = true;

    protected $mysqlUser = 'root';
    protected $mysqlPassword = '';
    protected $mysqlDatabase = 'booking';
    protected $mysqlHost = 'localhost';

    public function __get($key)
    {
        return $this->$key;
    }

    public function has($key)
    {
        return isset($this->$key);
    }

    /**
     * Allows custom configuration to be used. Key names will be checked for existence to aid in catching typos.
     *
     * @param  array  $config array of key-value pairs of config-options
     * @throws Exceptions\Config if key not found
     * @return null
     */
    public function setup(array $config)
    {
        foreach ($config as $k => $v) {
            if (isset($this->$k)) {
                $this->$k = $v;
            } else {
                throw new Exceptions\Config("Could not override non-existent config option $k. Try add instead.");
            }
        }
    }

    /**
     * Use this to add to the config. Might be useful if you're extending class functionality but don't wish to
     * over-ride the injected config.
     *
     * @param [string] $key   Config option name
     * @param [mixed]  $value Config option value
     * @return null
     */
    public function add($key, $value)
    {
        $this->$key = $value;
    }
}
