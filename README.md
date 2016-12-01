# legacy-adapter
Flyweight-adapter to use legacy code in moder frameworks with lazy refactor.

### Install
```
composer require jced-artem/legacy-adapter
```
### Example
```
<?php
// legacy_lib.php

include("settings.inc.php");
require("functions.php");
require_once("database.connection.php");

define('SOME_CONST', 'value');

$var1 = funcName(CONST_2);

function get_Var2A($param1, $param2) {
    return functionFromAnotherInclude($param2, $param1);
}

class myClass
{
    var $data = '';
    function getData() {
        global $var1;
        // do somethig
        return get_Var2A($var1, SOME_CONST);
    }
}

include_once("specialCode.php");

$var2 = function needThis() {
    $obj = new myClass();
    return unknownFunctionFromInclude() + $obj->getData();
}
printr('{"param":' . $var2 . '; "var": ' . $var1 . '}');
```
You can use this legacy file anywhere simply:
```
class MyLib extends LegacyAbstractAdapter
{
    /**
     * Configure flyweight
     */
    protected function configure()
    {
        $this
            ->setLegacyFile('legacy_lib.php')
            ->setLegacyPath('/path/to/includes')
        ;
    }
}

$myLib = new MyLib();

// global vars
$var1 = $myLib->var1;
$var2 = $myLib->var2;

// set value
$myLib->var1 = 'some new value';

// get global functions
$res1 = $myLib->get_Var2A($param1, $param2);
$res2 = function needThis();

// get printr result
$content = $myLib->getFlyweight()->getContent();
```
or
```
class MyLib extends LegacyAbstractAdapter
{
    /**
     * Configure flyweight
     */
    protected function configure()
    {
        $this
            ->setLegacyFile('legacy_lib.php')
            ->setLegacyPath('/path/to/includes')
        ;
    }
    
    // override
    public function needThis()
    {
        return 'dummy value';
    }
    
    // decorate
    public function get_Var2A($param1, $param2)
    {
        return '<font>' . $this->getFlyweight()->call('get_Var2A', [$param1, $param2]); . '</font>';
    }
    
    // and more
}
```
### Methods available in flyweight

`getContent()` - get echo/print/etc after include file

`get($name)` - get variable from file

`hasVariable($name)` - check if variable exists

`set($name, $value)` - set variable value

`hasFunction($name)` - check if function exists

`call($name, array $parameters = [])` - call function

`hasClassDeclared($className)` - check if class exists

`innerClassGetConstant($className, $constantName)` - get constsnt from class

`innerClassGetInstance($className)` - get instance of class which was declared in file

`innerClassCallStatic($className, $methodName, array $parameters = [])` - call static function of class declared in file

`getFunctionNamesList()` - list of all function

`getVariableNamesList()` - list of all variables

`getClassNamesList()` - list of all classes

### Weakness
1. Singleton-like. You can't create 2 objects because you can't include file 2 times;
2. Global functions and classes are still global;
3. Performance can be 2 times lower then native usage include();
