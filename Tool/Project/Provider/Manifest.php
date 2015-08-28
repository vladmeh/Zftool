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
 * @version    $Id: Manifest.php 24593 2012-01-05 20:35:02Z matthew $
 */

/**
 * @see Zend_Tool_Project_Provider_Manifest
 */
require_once 'Zend/Tool/Project/Provider/Manifest.php';

/**
 * @see Vlmeh_Tool_Project_Provider_ModelMapperProvider
 */
require_once 'Zftool/Tool/Project/Provider/ModelMapperProvider.php';

/**
 * @see Vlmeh_Tool_Project_Provider_BaseModelProvider
 */
require_once 'Zftool/Tool/Project/Provider/BaseModelProvider.php';

/**
 * @see Zend_Tool_Framework_Manifest_ProviderManifestable
 */
require_once 'Zend/Tool/Framework/Manifest/ProviderManifestable.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zftool_Tool_Project_Provider_Manifest
    extends Zend_Tool_Project_Provider_Manifest
    implements Zend_Tool_Framework_Manifest_ProviderManifestable
{

    /**
     * getProviders()
     *
     * @return array Array of Providers
     */
    public function getProviders()
    {
        $this->addContexts();
        // the order here will represent what the output will look like when iterating a manifest
        return array(
            new Zftool_Tool_Project_Provider_ModelMapperProvider(),
            //new Zftool_Tool_Project_Provider_BaseModelProvider(),
        );
    }

    public function addContexts(){
        // add contexts for ZFS specific directory structure.
        $contextRegistry = Zend_Tool_Project_Context_Repository::getInstance();
        $contextRegistry->addContextsFromDirectory(
            dirname(dirname(__FILE__)) . '/Context/Zf/', 'Zftool_Tool_Project_Context_Zf_'
        );
    }
}
