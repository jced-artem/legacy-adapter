<?php

namespace Jced;

/**
 * Class LegacyAbstractAdapter
 */
abstract class LegacyAbstractAdapter
{
    /** @var null|string */
    private $legacyPath = null;

    /** @var string */
    private $legacyFile;

    /** @var LegacyFlyweight */
    private $flyweight;

    /**
     * Configure flyweight
     */
    abstract protected function configure();

    /**
     * LegacyAbstractAdapter constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->configure();
        if (empty($this->legacyFile)) {
            throw new \Exception('Undefined legacy file');
        }
        // If file has absolute path
        if (!file_exists($this->legacyFile)) {
            $this->legacyFile = $this->legacyPath . DIRECTORY_SEPARATOR . $this->legacyFile;
            // If file has relative path
            if (!file_exists($this->legacyFile)) {
                throw new \Exception('File does not exists');
            }
        }
        $this->flyweight = new LegacyFlyweight($this->legacyFile, $this->legacyPath);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if ($this->getFlyweight()->hasFunction($name)) {
            return $this->getFlyweight()->call($name, $arguments);
        } else {
            throw new \Exception('Call to undefined method [' . $name . ']');
        }
    }

    /**
     * @param string $name
     * @return mixed|null
     * @throws \Exception
     */
    public function __get($name)
    {
        if ($this->getFlyweight()->hasVariable($name)) {
            return $this->getFlyweight()->get($name);
        } else {
            throw new \Exception('Get undefined property [' . $name . ']');
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->getFlyweight()->hasVariable($name);
    }

    /**
     * @param string$name
     * @param mixed $value
     */
    function __set($name, $value)
    {
        $this->getFlyweight()->set($name, $value);
    }

    /**
     * @return LegacyFlyweight
     */
    protected function getFlyweight()
    {
        return $this->flyweight;
    }

    /**
     * @param string $legacyPath
     * @return $this
     */
    protected function setLegacyPath($legacyPath)
    {
        $this->legacyPath = $legacyPath;
        return $this;
    }

    /**
     * @param string $legacyFile
     * @return $this
     */
    protected function setLegacyFile($legacyFile)
    {
        $this->legacyFile = $legacyFile;
        return $this;
    }
}
