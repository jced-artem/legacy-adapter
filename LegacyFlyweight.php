<?php

namespace Jced;

/**
 * Class LegacyFlyweight
 */
class LegacyFlyweight
{
    /** @var array */
    private $classList = [];

    /** @var array */
    private $functionList = [];

    /** @var array */
    private $variableList = [];

    /** @var mixed */
    private $content = null;

    /**
     * LegacyFlyweight constructor.
     * @param string $legacyFile
     * @param string|null $legacyPath
     * @throws \Exception
     */
    public function __construct($legacyFile, $legacyPath = null)
    {
        if (in_array(realpath(__DIR__ . DIRECTORY_SEPARATOR . $legacyFile), get_included_files())) {
            throw new \Exception('File [' . $legacyFile . '] already included');
        }
        $this->classList = get_declared_classes();
        $defaultIncludePath = get_include_path();
        set_include_path(is_null($legacyPath) ? $defaultIncludePath : $legacyPath);
        ob_start();
        require_once($legacyFile);
        $this->content = ob_get_clean();
        set_include_path($defaultIncludePath);
        $this->variableList = get_defined_vars();
        unset(
            $this->variableList['legacyFile'],
            $this->variableList['legacyPath'],
            $this->variableList['defaultIncludePath']
        );
        $this->classList = array_fill_keys(
            array_diff(get_declared_classes(), $this->classList),
            false
        );
        $functionList = get_defined_functions();
        $this->functionList = array_fill_keys($functionList['user'], false);
    }

    /**
     * Get content of included file
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get variable from file
     * @param string $name
     * @return mixed|null
     */
    public function get($name)
    {
        return $this->hasVariable($name) ? $this->variableList[$name] : null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasVariable($name)
    {
        return isset($this->variableList[$name]);
    }

    /**
     * Set variable value
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value)
    {
        $this->variableList[$name] = $value;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasFunction($name)
    {
        return isset($this->functionList[strtolower($name)]);
    }

    /**
     * Call function declared in file
     * @param string $name
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    public function call($name, array $parameters = [])
    {
        $lowerName = strtolower($name);
        if (!$this->hasFunction($name)) {
            throw new \Exception('Undefined function');
        }
        if (empty($this->functionList[$lowerName])) {
            $this->functionList[$lowerName] = new \ReflectionFunction($name);
        }
        return $this->functionList[$lowerName]->invokeArgs($parameters);;
    }

    /**
     * @param string $className
     * @return bool
     */
    public function hasClassDeclared($className)
    {
        return isset($this->classList[$className]);
    }

    /**
     * @param string $className
     * @throws \Exception
     */
    private function hasClassName($className)
    {
        if (!$this->hasClassDeclared($className)) {
            throw new \Exception('Undefined class [' . $className . ']');
        }
    }

    /**
     * @param string $className
     * @return \ReflectionClass
     */
    private function getReflectionClass($className)
    {
        $this->hasClassName($className);
        if (empty($this->classList[$className])) {
            $this->classList[$className] = new \ReflectionClass($className);
        }
        return $this->classList[$className];
    }

    /**
     * @param string $className
     * @param string $constantName
     * @return mixed
     */
    public function innerClassGetConstant($className, $constantName)
    {
        return $this->getReflectionClass($className)->getConstant($constantName);
    }

    /**
     * Get instance of class which was declared in file
     * @param string $className
     * @return object
     */
    public function innerClassGetInstance($className)
    {
        $this->hasClassName($className);
        return new $className();
    }

    /**
     * Call static function of class declared in file
     * @param string $className
     * @param string $methodName
     * @param array $parameters
     * @return mixed
     */
    public function innerClassCallStatic($className, $methodName, array $parameters = [])
    {
        return $this->getReflectionClass($className)->getMethod($methodName)->invokeArgs(null, $parameters);
    }

    /**
     * @return array
     */
    public function getFunctionNamesList()
    {
        return array_keys($this->functionList);
    }

    /**
     * @return array
     */
    public function getVariableNamesList()
    {
        return array_keys($this->variableList);
    }

    /**
     * @return array
     */
    public function getClassNamesList()
    {
        return array_keys($this->classList);
    }
}
