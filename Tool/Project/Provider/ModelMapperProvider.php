<?php

require_once 'Zend/Tool/Project/Provider/DbTable.php';
require_once 'Zend/Tool/Project/Provider/Abstract.php';
require_once 'Zend/Tool/Project/Provider/Exception.php';

class Zftool_Tool_Project_Provider_ModelMapperProvider
    extends Zend_Tool_Project_Provider_Abstract
    implements Zend_Tool_Framework_Provider_Pretendable
{

    protected $_specialties = array(
        'TableModel',
        'MapperModel',
        'DbTable',
        'TableList',
        'ColumnList'
    );

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'Modelmapper';
    }

    /**
     * @param Zend_Tool_Project_Profile $profile
     * @param $modelName
     * @param null $moduleName
     * @param $colList
     * @return Zend_Tool_Project_Profile_Resource
     * @throws Zend_Tool_Project_Profile_Exception
     * @throws Zend_Tool_Project_Provider_Exception
     * @internal param string $context
     */
    public static function createResource(Zend_Tool_Project_Profile $profile, $modelName, $moduleName = null, $colList)
    {
        if (!is_string($modelName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_Model::createResource() expects \"modelName\" is the name of a model resource to create.');
        }

        if (!($modelsDirectory = self::_getModelsDirectoryResource($profile, $moduleName))) {
            if ($moduleName) {
                $exceptionMessage = 'A model directory for module "' . $moduleName . '" was not found.';
            } else {
                $exceptionMessage = 'A model directory was not found.';
            }
            throw new Zend_Tool_Project_Provider_Exception($exceptionMessage);
        }

        $newModel = $modelsDirectory->createResource(
            'modelColFile',
            array('modelName' => $modelName, 'moduleName' => $moduleName, 'colList' => $colList)
        );

        return $newModel;
    }

    /**
     * @param Zend_Tool_Project_Profile $profile
     * @param $modelName
     * @param null $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     * @throws Zend_Tool_Project_Profile_Exception
     * @throws Zend_Tool_Project_Provider_Exception
     * @internal param string $context
     */
    public static function createMapperResource(Zend_Tool_Project_Profile $profile, $modelName, $moduleName = null)
    {
        $profileSearchParams = array();

        if ($moduleName != null && is_string($moduleName)) {
            $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
        }

        $profileSearchParams[] = 'modelsDirectory';

        $modelsDirectory = $profile->search($profileSearchParams);

        if (!($modelsDirectory instanceof Zend_Tool_Project_Profile_Resource)) {
            throw new Zend_Tool_Project_Provider_Exception(
                'A models directory was not found' .
                (($moduleName) ? ' for module ' . $moduleName . '.' : '.')
            );
        }

        if (!($modelMapperDirectory = $modelsDirectory->search('modelMapperDirectory'))) {
            $modelMapperDirectory = $modelsDirectory->createResource('modelMapperDirectory');
        }

        $modelMapperFile = $modelMapperDirectory->createResource(
            'modelMapperFile',
            array(
                'modelName' => $modelName,
                'moduleName' => $moduleName
            )
        );

        return $modelMapperFile;

    }

    public static function hasMapperResource(Zend_Tool_Project_Profile $profile, $modelName, $moduleName = null)
    {
        $profileSearchParams = array();

        if ($moduleName != null && is_string($moduleName)) {
            $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
        }

        $profileSearchParams[] = 'modelsDirectory';

        $modelsDirectory = $profile->search($profileSearchParams);

        if (!($modelsDirectory instanceof Zend_Tool_Project_Profile_Resource)
            || !($modelMapperDirectory = $modelsDirectory->search('modelMapperDirectory'))) {
            return false;
        }

        $modelMapperFile = $modelMapperDirectory->search(array('modelMapperFile' => array('modelName' => $modelName)));

        return ($modelMapperFile instanceof Zend_Tool_Project_Profile_Resource) ? true : false;
    }

    /**
     * hasResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $modelName
     * @param string $moduleName
     * @param string $context
     * @return Zend_Tool_Project_Profile_Resource
     * @throws Zend_Tool_Project_Profile_Exception
     * @throws Zend_Tool_Project_Provider_Exception
     */
    public static function hasResource(Zend_Tool_Project_Profile $profile, $modelName, $moduleName = null, $context = 'modelFile')
    {
        if (!is_string($modelName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_Model::createResource() expects \"modelName\" is the name of a model resource to check for existence.');
        }

        $modelsDirectory = self::_getModelsDirectoryResource($profile, $moduleName);

        if (!$modelsDirectory instanceof Zend_Tool_Project_Profile_Resource) {
            return false;
        }

        return (($modelsDirectory->search(array($context => array('modelName' => $modelName)))) instanceof Zend_Tool_Project_Profile_Resource);
    }

    /**
     * _getModelsDirectoryResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    protected static function _getModelsDirectoryResource(Zend_Tool_Project_Profile $profile, $moduleName = null)
    {
        $profileSearchParams = array();

        if ($moduleName != null && is_string($moduleName)) {
            $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
        }

        $profileSearchParams[] = 'modelsDirectory';

        return $profile->search($profileSearchParams);
    }

    /**
     * Create a new model
     *
     * @param string $tableName
     * @param string $module
     * @throws Zend_Tool_Project_Provider_Exception
     * @internal param string $name
     */
    public function createTableModel($tableName, $module = null)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        $originalName = $tableName;

        $name = ucwords($tableName);

        // get request/response object
        $request = $this->_registry->getRequest();
        $response = $this->_registry->getResponse();

        // Check that there is not a dash or underscore, return if doesnt match regex
        if (preg_match('#[_-]#', $name)) {
            throw new Zend_Tool_Project_Provider_Exception('Model names should be camel cased.');
        }

        if (self::hasResource($this->_loadedProfile, $name, $module, 'modelColFile')) {
            //throw new Zend_Tool_Project_Provider_Exception('This project already has a model named ' . $name);
            $request->setPretend(true);
        }

        // alert the user about inline converted names
        $tense = (($request->isPretend()) ? 'would be' : 'is');

        if ($name !== $originalName) {
            $response->appendContent(
                'Note: The canonical model name that ' . $tense
                . ' used with other providers is "' . $name . '";'
                . ' not "' . $originalName . '" as supplied',
                array('color' => array('yellow'))
            );
        }

        $colList = $this->_getDb()->describeTable($originalName);

        try {
            $modelResource = self::createResource($this->_loadedProfile, $name, $module, $colList);

        } catch (Exception $e) {
            $response->setException($e);
            return;
        }

        // do the creation
        if ($request->isPretend()) {

            $response->appendContent('This project already has a Mapper model: '  . $modelResource->getContext()->getPath());
            $nameResponse = $this->_registry
                ->getClient()
                ->promptInteractiveInput("Overwrite?(y) Backup old file?(b) Cancel.(q)");
            $name = $nameResponse->getContent();
            if($name == 'y' || $name == 'b')
            {
                if($name == 'b' && file_exists($modelResource->getContext()->getPath()))
                {
                    $response->appendContent('Backup a Mapper model file at ' . $modelResource->getContext()->getPath() . '.bak');
                    rename($modelResource->getContext()->getPath(),$modelResource->getContext()->getPath().'.bak');
                }

                $response->appendContent('Updated a model at ' . $modelResource->getContext()->getPath());
                $modelResource->create();
            }

        } else {

            $response->appendContent('Creating a model at ' . $modelResource->getContext()->getPath());
            $modelResource->create();

            $this->_storeProfile();
        }

    }

    /**
     * Create a new model
     *
     * @param string $name
     * @param string $module
     * @throws Zend_Tool_Project_Provider_Exception
     */
    public function createMapperModel($name, $module = null)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        $originalName = $name;

        $name = ucwords($name);

        // get request/response object
        $request = $this->_registry->getRequest();
        $response = $this->_registry->getResponse();

        // Check that there is not a dash or underscore, return if doesnt match regex
        if (preg_match('#[_-]#', $name)) {
            throw new Zend_Tool_Project_Provider_Exception('Model names should be camel cased.');
        }


        if (self::hasMapperResource($this->_loadedProfile, $name, $module)) {
            //throw new Zend_Tool_Project_Provider_Exception('This project already has a model named ' . $name);
            $request->setPretend(true);
        }

        // alert the user about inline converted names
        $tense = (($request->isPretend()) ? 'would be' : 'is');

        if ($name !== $originalName) {
            $response->appendContent(
                'Note: The canonical model name that ' . $tense
                . ' used with other providers is "' . $name . '";'
                . ' not "' . $originalName . '" as supplied',
                array('color' => array('yellow'))
            );
        }

        try {
            $modelResource = self::createMapperResource($this->_loadedProfile, $name, $module);

        } catch (Exception $e) {
            $response->setException($e);
            return;
        }

        // do the creation
        if ($request->isPretend()) {

            $response->appendContent('This project already has a Mapper model: '  . $modelResource->getContext()->getPath());
            $nameResponse = $this->_registry
                ->getClient()
                ->promptInteractiveInput("Overwrite?(y) Backup old file?(b) Cancel.(q)");
            $name = $nameResponse->getContent();
            if($name == 'y' || $name == 'b')
            {
                if($name == 'b' && file_exists($modelResource->getContext()->getPath()))
                {
                    $response->appendContent('Backup a Mapper model file at ' . $modelResource->getContext()->getPath() . '.bak');
                    rename($modelResource->getContext()->getPath(),$modelResource->getContext()->getPath().'.bak');
                }

                $response->appendContent('Updated a Mapper model at ' . $modelResource->getContext()->getPath());
                $modelResource->create();
            }

        } else {

            $response->appendContent('Creating a model at ' . $modelResource->getContext()->getPath());
            $modelResource->create();

            $this->_storeProfile();
        }

    }

    /**
     * @param $name
     * @param $actualTableName
     * @param null $module
     * @throws Zend_Tool_Framework_Client_Exception
     * @throws Zend_Tool_Project_Provider_Exception
     * @internal param bool|false $forceOverwrite
     */
    public function createDbTable($name, $actualTableName, $module = null)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        // Check that there is not a dash or underscore, return if doesnt match regex
        if (preg_match('#[_-]#', $name)) {
            throw new Zend_Tool_Project_Provider_Exception('DbTable names should be camel cased.');
        }

        $originalName = $name;
        $name = ucfirst($name);

        // get request/response object
        $request = $this->_registry->getRequest();
        $response = $this->_registry->getResponse();

        if ($actualTableName == '') {
            throw new Zend_Tool_Project_Provider_Exception('You must provide both the DbTable name as well as the actual db table\'s name.');
        }

        if (Zend_Tool_Project_Provider_DbTable::hasResource($this->_loadedProfile, $name, $module)) {
            //throw new Zend_Tool_Project_Provider_Exception('This project already has a DbTable named ' . $name);
            $request->setPretend(true);
        }

        // alert the user about inline converted names
        $tense = (($request->isPretend()) ? 'would be' : 'is');

        if ($name !== $originalName) {
            $response->appendContent(
                'Note: The canonical model name that ' . $tense
                . ' used with other providers is "' . $name . '";'
                . ' not "' . $originalName . '" as supplied',
                array('color' => array('yellow'))
            );
        }

        try {
            $tableResource = Zend_Tool_Project_Provider_DbTable::createResource($this->_loadedProfile, $name, $actualTableName, $module);
        } catch (Exception $e) {
            $response = $this->_registry->getResponse();
            $response->setException($e);
            return;
        }

        // do the creation
        if ($request->isPretend()) {

            $response->appendContent('This project already has a Mapper model: '  . $tableResource->getContext()->getPath());
            $nameResponse = $this->_registry
                ->getClient()
                ->promptInteractiveInput("Overwrite?(y) Backup old file?(b) Cancel.(n)");
            $name = $nameResponse->getContent();
            if($name == 'y' || $name == 'b')
            {
                if($name == 'b' && file_exists($tableResource->getContext()->getPath()))
                {
                    $response->appendContent('Backup a model at ' . $tableResource->getContext()->getPath() . '.bak');
                    rename($tableResource->getContext()->getPath(),$tableResource->getContext()->getPath().'.bak');
                }

                $response->appendContent('Updated create a DbTable at ' . $tableResource->getContext()->getPath());
                $tableResource->create();
            }

        } else {
            $response->appendContent('Creating a DbTable at ' . $tableResource->getContext()->getPath());
            $tableResource->create();
            $this->_storeProfile();
        }
    }


    public function create($tableName, $module = null)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        $name = ucwords($tableName);

        $this->createTableModel($tableName, $module);
        $this->createDbTable($name, strtolower($name), $module);
        $this->createMapperModel($name, $module);

    }

    /**
     * @throws Zend_Tool_Project_Profile_Exception
     * @throws Zend_Tool_Project_Provider_Exception
     */
    public function showTableList()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        $db = $this->_getDb();

        // get request/response object
        $response = $this->_registry->getResponse();

        foreach ($db->listTables() as $tableName) {
            $response->appendContent($tableName);
        }

    }

    /**
     * @param $tableName
     * @throws Zend_Tool_Project_Provider_Exception
     */
    public function showColumnList($tableName)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        $db = $this->_getDb();

        $tableResources = array();

        foreach ($db->listTables() as $tableName) {
            $tableResources[] = $tableName;
        }

        $tableName = ucfirst($tableName);

        if ($tableName == '') {
            throw new Zend_Tool_Project_Provider_Exception('You must provide both the DbTable name as well as the actual db table\'s name.');
        }

        $response = $this->_registry->getResponse();

        foreach ($db->describeTable($tableName) as $colTable) {
            $response->appendContent(
                $colTable['COLUMN_NAME'] . ' => '
                . $colTable['DATA_TYPE']
            );
        }

    }

    /**
     * @return void|Zend_Db_Adapter_Abstract
     * @throws Zend_Tool_Project_Profile_Exception
     * @throws Zend_Tool_Project_Provider_Exception
     */
    protected function _getDb()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        $bootstrapResource = $this->_loadedProfile->search('BootstrapFile');

        /* @var $zendApp Zend_Application */
        $zendApp = $bootstrapResource->getApplicationInstance();

        try {
            $zendApp->bootstrap('db');
        } catch (Zend_Application_Exception $e) {
            throw new Zend_Tool_Project_Provider_Exception('Db resource not available, you might need to configure a DbAdapter.');
            return;
        }

        /* @var $db Zend_Db_Adapter_Abstract */
        $db = $zendApp->getBootstrap()->getResource('db');

        return $db;

    }
}