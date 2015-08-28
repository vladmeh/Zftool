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
class Zftool_Tool_Project_Context_Zf_ModelMapperFile extends Zend_Tool_Project_Context_Zf_AbstractClassFile
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
     * init()
     *
     */
    public function init()
    {
        $this->_modelName = $this->_resource->getAttribute('modelName');
        $this->_filesystemName = ucfirst($this->_modelName) . '.php';
        parent::init();
    }

    /**
     * getPersistentAttributes
     *
     * @return array
     */
    public function getPersistentAttributes()
    {
        return array('modelName' => $this->getModelName());
    }

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'ModelMapperFile';
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        return $this->_modelName;
    }

    /**
     * @return string
     */
    public function getContents()
    {

        $classMapperName = $this->getFullClassName($this->_modelName, 'Model_Mapper');
        $className = $this->getFullClassName($this->_modelName, 'Model');
        $classDbTableName = $this->getFullClassName($this->_modelName, 'Model_DbTable');

        $codeGenFile = new Zend_CodeGenerator_Php_File(array(
            'fileName' => $this->getPath(),
            'classes' => array(
                new Zend_CodeGenerator_Php_Class(array(
                    'name' => $classMapperName,
                    //'extendedClass' => 'Application_Models_Base_Mapper_Abstract',
                    'properties' => array(
                        array(
                            'name'         => '_dbTable',
                            'visibility'   => 'protected',
                        ),
                    ),
                    'methods' => array(
                        new Zend_CodeGenerator_Php_Method(array(
                            'name' => 'setDbTable',
                            'parameters' => array(
                                array('name' => 'dbTable'),
                            ),
                            'body' => 'if (is_string($dbTable))'."\n\t".
                                '$dbTable = new $dbTable();'."\n\n".
                                'if (!$dbTable instanceof Zend_Db_Table_Abstract)'."\n\t".
                                'throw new Exception(\'Invalid table data gateway provided\');'."\n\n".
                                '$this->_dbTable = $dbTable;'."\n\n".
                                'return $this;',
                            'docblock'   => new Zend_CodeGenerator_Php_Docblock(array(
                                'tags'             => array(
                                    array(
                                        'name'        => 'param',
                                        'description' => '$dbTable',
                                    ),
                                    array(
                                        'name'        => 'return',
                                        'description' => '$this',
                                    ),
                                    array(
                                        'name'        => 'throws',
                                        'description' => 'Exception',
                                    ),
                                ),
                            )),
                        )),
                        new Zend_CodeGenerator_Php_Method(array(
                            'name' => 'getDbTable',
                            'body' => 'if (null === $this->_dbTable)'."\n\t".
                                '$this->setDbTable(\''.$classDbTableName.'\');'."\n\n".
                                'return $this->_dbTable;',
                            'docblock'   => new Zend_CodeGenerator_Php_Docblock(array(
                                'tags'             => array(
                                    array(
                                        'name'        => 'return',
                                        'description' => $classDbTableName,
                                    ),
                                ),
                            )),
                        )),
                        new Zend_CodeGenerator_Php_Method(array(
                            'name' => 'save',
                            'parameters' => array(
                                array(
                                    'name' => strtolower($this->_modelName),
                                    'type' => $className,
                                ),
                            ),
                            'body' => '$data = $this->_getDbData($'.strtolower($this->_modelName).');'."\n\n".
                                'if (null == ($id = $'.strtolower($this->_modelName).'->getId())) {'."\n\t".
                                'unset($data[$this->_getDbPrimary()]);'."\n\t".
                                '$this->getDbTable()->insert($data);'."\n".
                                '} else {'."\n\t".
                                '$this->getDbTable()->update($data, array($this->_getDbPrimary(). \' = ?\' => $id));'."\n".
                                '}'."\n\n".
                                'return $this;',
                            'docblock'   => new Zend_CodeGenerator_Php_Docblock(array(
                                'tags'             => array(
                                    array(
                                        'name'        => 'param',
                                        'description' => $className. ' $' .strtolower($this->_modelName),
                                    ),
                                    array(
                                        'name'        => 'return',
                                        'description' => '$this',
                                    ),
                                ),
                            )),
                        )),
                        new Zend_CodeGenerator_Php_Method(array(
                            'name' => 'find',
                            'parameters' => array(
                                array(
                                    'name' => 'id',
                                ),
                                array(
                                    'name' => strtolower($this->_modelName),
                                    'type' => $className,
                                ),
                            ),
                            'body' => '$result = $this->getDbTable()->find($id);'."\n\n".
                                'if (0 == count($result)) {'."\n\t".
                                'return null;'."\n".
                                '}'."\n\n".
                                '$row = $result->current();'."\n".
                                '$entry = $this->_setDbData($row, $'.strtolower($this->_modelName).');'."\n\n".
                                'return $entry;',
                            'docblock'   => new Zend_CodeGenerator_Php_Docblock(array(
                                'tags'             => array(
                                    array(
                                        'name'        => 'param',
                                        'description' => '$id',
                                    ),
                                    array(
                                        'name'        => 'param',
                                        'description' => $className. ' $' .strtolower($this->_modelName),
                                    ),
                                    array(
                                        'name'        => 'return',
                                        'description' => $className.'|null',
                                    ),
                                ),
                            )),
                        )),
                        new Zend_CodeGenerator_Php_Method(array(
                            'name' => 'fetchAll',
                            'body' => '$resultSet = $this->getDbTable()->fetchAll();'."\n\n".
                                '$entries   = array();'."\n".
                                'foreach ($resultSet as $row) {'."\n\t".
                                '$entry = new '.$className.'();'."\n\t".
                                '$entry = $this->_setDbData($row, $entry);'."\n\t".
                                '$entries[] = $entry;'."\n".
                                '}'."\n\n".
                                'return $entries;',
                            'docblock'   => new Zend_CodeGenerator_Php_Docblock(array(
                                'tags'             => array(
                                    array(
                                        'name'        => 'return',
                                        'description' => 'array',
                                    ),
                                ),
                            )),
                        )),
                        new Zend_CodeGenerator_Php_Method(array(
                            'name' => '_getDbPrimary',
                            'visibility'   => 'protected',
                            'body' => '$primaryKey = $this->getDbTable()->info(\'primary\');'."\n\n".
                                'return $primaryKey[1];',
                            'docblock'   => new Zend_CodeGenerator_Php_Docblock(array(
                                'tags'             => array(
                                    array(
                                        'name'        => 'return',
                                        'description' => 'mixed',
                                    ),
                                ),
                            )),
                        )),
                        new Zend_CodeGenerator_Php_Method(array(
                            'name' => '_getDbData',
                            'parameters' => array(
                                array(
                                    'name' => strtolower($this->_modelName),
                                    'type' => $className,
                                ),
                            ),
                            'visibility'   => 'protected',
                            'body' => '$info = $this->getDbTable()->info();'."\n".
                                '$properties = $info[\'cols\'];'."\n\n".
                                '$data = array();'."\n".
                                'foreach ($properties as $property) {'."\n\t".
                                '$name = $this->_normaliseName($property);'."\n\n\t".
                                'if($property != $this->_getDbPrimary())'."\n\t\t".
                                '$data[$property] = $'.strtolower($this->_modelName).'->__get($name);'."\n".
                                '}'."\n\n".
                                'return $data;',
                            'docblock'   => new Zend_CodeGenerator_Php_Docblock(array(
                                'tags'             => array(
                                    array(
                                        'name'        => 'param',
                                        'description' => $className.' $' .strtolower($this->_modelName),
                                    ),
                                    array(
                                        'name'        => 'return',
                                        'description' => 'array',
                                    ),
                                ),
                            )),
                        )),
                        new Zend_CodeGenerator_Php_Method(array(
                            'name' => '_setDbData',
                            'parameters' => array(
                                array(
                                    'name' => 'row',
                                ),
                                array(
                                    'name' => 'entry',
                                    'type' => $className,
                                ),
                            ),
                            'visibility'   => 'protected',
                            'body' => '$info = $this->getDbTable()->info();'."\n".
                                '$properties = $info[\'cols\'];'."\n\n".
                                'foreach ($properties as $property) {'."\n\t".
                                '$entry->__set($this->_normaliseName($property), $row->$property);'."\n".
                                '}'."\n\n".
                                'return $entry;',
                            'docblock'   => new Zend_CodeGenerator_Php_Docblock(array(
                                'tags'             => array(
                                    array(
                                        'name'        => 'param',
                                        'description' => 'Zend_Db_Table_Rowset $row',
                                    ),
                                    array(
                                        'name'        => 'param',
                                        'description' => $className. ' $entry',
                                    ),
                                    array(
                                        'name'        => 'return',
                                        'description' => $className,
                                    ),
                                ),
                            )),
                        )),
                        new Zend_CodeGenerator_Php_Method(array(
                            'name' => '_normaliseName',
                            'parameters' => array(
                                array(
                                    'name' => 'property',
                                ),
                            ),
                            'visibility'   => 'protected',
                            'body' => '$name = preg_split(\'~_~\', $property);'."\n".
                                '$normaliseName = implode(array_map(\'ucwords\', $name));'."\n\n".
                                'return $normaliseName;',
                            'docblock'   => new Zend_CodeGenerator_Php_Docblock(array(
                                'tags'             => array(
                                    array(
                                        'name'        => 'param',
                                        'description' => '$property string',
                                    ),
                                    array(
                                        'name'        => 'return',
                                        'description' => 'string',
                                    ),
                                ),
                            )),
                        )),
                    ),
                ))
            )
        ));
        return $codeGenFile->generate();
    }
}