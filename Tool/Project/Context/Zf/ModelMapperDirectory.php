<?php
/**
 * Created by PhpStorm.
 * User: mvl
 * Date: 20.08.2015
 * Time: 15:21
 */

require_once 'Zend/Tool/Project/Context/Filesystem/Directory.php';


class Zftool_Tool_Project_Context_Zf_ModelMapperDirectory
    extends Zend_Tool_Project_Context_Filesystem_Directory
{
    /**
     * @var string
     */
    protected $_filesystemName = 'mappers';

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'ModelMapperDirectory';
    }

}