<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ModelFile.php 24593 2012-01-05 20:35:02Z matthew $
 */

/**
 * Zend_Tool_Project_Context_Zf_AbstractClassFile
 */
require_once 'Zend/Tool/Project/Context/Zf/AbstractClassFile.php';

/**
 * This class is the front most class for utilizing Zend_Tool_Project
 *
 * A profile is a hierarchical set of resources that keep track of
 * items within a specific project.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zftool_Tool_Project_Context_Zf_ModelColFile extends Zend_Tool_Project_Context_Zf_AbstractClassFile
{

    /**
     * @var string
     */
    protected $_modelName = 'Base';

    /**
     * @var string
     */
    protected $_filesystemName = 'modelName';

    /**
     * @var array
     */
    protected $_propertiesClass = array();

    /**
     * @var array
     */
    protected $_methodsClass = array();

    /**
     * @var array
     */
    protected $_colList = array();

    /**
     * init()
     *
     */
    public function init()
    {
        $this->_modelName = $this->_resource->getAttribute('modelName');
        $this->_filesystemName = ucfirst($this->_modelName) . '.php';
        $this->_colList = $this->_resource->getAttribute('colList');
        $this->_propertiesClass = $this->getPropertiesClass();
        parent::init();
    }

    /**
     * getPersistentAttributes
     *
     * @return array
     */
    public function getPersistentAttributes()
    {
        return array(
            'modelName' => $this->getModelName()
            );
    }

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'ModelColFile';
    }

    public function getModelName()
    {
        return $this->_modelName;
    }

    public function getContents()
    {

        $className = $this->getFullClassName($this->_modelName, 'Model');

        $codeGenFile = new Zend_CodeGenerator_Php_File(array(
            'fileName' => $this->getPath(),
            //'requiredFiles' => array('Model/Base/Abstract.php'),
            'classes' => array(
                new Zend_CodeGenerator_Php_Class(array(
                    'name' => $className,
                    //'extendedClass' => 'Application_Model_Base_Abstract',
                    'properties' => $this->_propertiesClass,
                    'methods' => $this->getMethodsClass(),
                ))
            )
        ));
        return $codeGenFile->generate();
    }

    /**
     * @return array
     */
    public function getPropertiesClass()
    {
        $colList = $this->getColList();
        $propertiesClass = array();
        if (!empty($colList)){
            foreach ($colList as $colTable) {
                $propertiesClass[] = array(
                    'name' => '_'.$colTable['COLUMN_NAME'],
                    'visibility'   => Zend_CodeGenerator_Php_Property::VISIBILITY_PROTECTED,
                    //'defaultValue' => Zend_CodeGenerator_Php_Property_DefaultValue::TYPE_AUTO,
                );
            }

        }

        return $this->_propertiesClass = $propertiesClass;
    }

    /**
     * @return array
     */
    public function getColList()
    {
        return $this->_colList;
    }

    /**
     * @return array
     */
    public function getMethodsClass()
    {
        $colList = $this->getColList();
        $methodsClass = array();
        $methodsClass[] = new Zend_CodeGenerator_Php_Method(array(
            'name' => '__construct',
            'parameters' => array(
                array(
                    'type' => 'array',
                    'name' => 'options',
                    'defaultValue' => null,

                ),
            ),
            'body' => 'if (is_array($options))'."\n\t".
                '$this->setOptions($options);',
            'docblock'   => new Zend_CodeGenerator_Php_Docblock(array(
                'tags'             => array(
                    array(
                        'name'        => 'param',
                        'description' => '$options',
                        'type' => 'array',
                    ),
                ),
            )),
        ));
        $methodsClass[] = new Zend_CodeGenerator_Php_Method(array(
            'name' => '__set',
            'parameters' => array(
                array('name' => 'name'),
                array('name' => 'value'),
            ),
            'body' => '$method = \'set\' . $name;'."\n\n".
                'if ((\'mapper\' == $name) || !method_exists($this, $method))'."\n\t".
                'throw new Exception(\'Invalid property (\' . $name . \')\');'."\n\n".
                'return $this->$method($value);',
            'docblock'   => new Zend_CodeGenerator_Php_Docblock(array(
                'tags'             => array(
                    array(
                        'name'        => 'param',
                        'description' => '$name',
                    ),
                    array(
                        'name'        => 'param',
                        'description' => '$value',
                    ),
                    array(
                        'name'        => 'throws',
                        'description' => 'Exception',
                    ),
                ),
            )),
        ));
        $methodsClass[] = new Zend_CodeGenerator_Php_Method(array(
            'name' => '__get',
            'parameters' => array(
                array('name' => 'name'),
            ),
            'body' => '$method = \'get\' . $name;'."\n\n".
                'if ((\'mapper\' == $name) || !method_exists($this, $method))'."\n\t".
                'throw new Exception(\'Invalid property (\' . $name . \')\');'."\n\n".
                'return $this->$method();',
            'docblock'   => new Zend_CodeGenerator_Php_Docblock(array(
                'tags'             => array(
                    array(
                        'name'        => 'param',
                        'description' => '$name',
                    ),
                    array(
                        'name'        => 'throws',
                        'description' => 'Exception',
                    ),
                ),
            )),
        ));
        $methodsClass[] = new Zend_CodeGenerator_Php_Method(array(
            'name' => 'setOptions',
            'parameters' => array(
                array(
                    'type' => 'array',
                    'name' => 'options',
                ),
            ),
            'body' => '$methods = get_class_methods($this);'."\n\n".
                'foreach ($options as $key => $value) {'."\n\t".
                '$method = \'set\' . ucfirst($key);'."\n\n\t".
                'if (in_array($method, $methods)) {'."\n\t\t".
                '$this->$method($value);'."\n\t".
                '}'."\n".
                '}'."\n\n".
                'return $this;',
            'docblock'   => new Zend_CodeGenerator_Php_Docblock(array(
                'tags'             => array(
                    array(
                        'name'        => 'param',
                        'description' => '$options',
                        'type' => 'array',
                    ),
                    array(
                        'name'        => 'return',
                        'description' => '$this',
                    ),
                ),
            )),
        ));
        $methodsClass[] = new Zend_CodeGenerator_Php_Method(array(
            'name' => 'getOptions',
            'body' => '$class = new ReflectionClass($this);'."\n".
                '$properties = $class->getProperties(ReflectionProperty::IS_PROTECTED);'."\n\n".
                'if(0 === count($properties))'."\n\t".
                'return null;'."\n\n".
                '$data = array();'."\n".
                'foreach ($properties as $property) {'."\n\t".
                '$name = preg_split("~_~", $property->getName());'."\n\t".
                '$normaliseName = implode(array_map("ucwords", $name));'."\n\t".
                '$option = lcfirst($normaliseName);'."\n\n\t".
                'if ($property->isProtected()) {'."\n\t\t".
                '$property->setAccessible(TRUE);'."\n\t\t".
                '$data[$option] = (!is_null($property->getValue($this)))'."\n\t\t".
                '?$property->getValue($this)'."\n\t\t".
                ':"";'."\n\t".
                '}'."\n".
                '}'."\n\n".
                'return $data;',
            'docblock'   => new Zend_CodeGenerator_Php_Docblock(array(
                'tags'             => array(
                    array(
                        'name'        => 'return',
                        'description' => 'array|null',
                    ),
                ),
            )),
        ));
        if(!empty($colList)){
            foreach ($colList as $colTable) {
                $originName = $colTable['COLUMN_NAME'];
                $name = preg_split('~_~', $originName);
                $nameFirst = implode(array_map('ucwords', $name));

                $methodsClass[] = new Zend_CodeGenerator_Php_Method(array(
                    'name' => 'set'.$nameFirst,
                    'parameters' => array(
                        array('name' => $originName)
                    ),
                    'body' => '$this->_'.$originName.' = $'.$originName.';'."\n".
                        'return $this;',
                    'docblock'   => new Zend_CodeGenerator_Php_Docblock(array(
                        'shortDescription' => 'Set value '.$nameFirst,
                        'tags'             => array(
                            new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                                'paramName' => '$value',
                                'datatype' => '$this',
                            )),
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'datatype' => '$'.$originName,
                            )),
                        ),
                    )),
                ));

                $methodsClass[] = new Zend_CodeGenerator_Php_Method(array(
                    'name' => 'get'.$nameFirst,
                    'body' => 'return $this->_'.$originName.';',
                    'docblock'   => new Zend_CodeGenerator_Php_Docblock(array(
                        'shortDescription' => 'Get value '.$nameFirst,
                        'tags'             => array(
                            new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                                'paramName' => '$value',
                                'datatype' => 'mixed',
                            )),
                        ),
                    )),
                ));
            }

        }
        return $this->_methodsClass = $methodsClass;
    }


}