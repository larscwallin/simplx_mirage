<?php

/**
 * Simplx_Mirage_Object wraps a modResource object.
 * The Simplx_Mirage_Object "inherits" many default property values from its assocciated 
 * Simplx_Mirage_Class object.
 *
 * @package Simplx_Mirage
 */
class Simplx_Mirage_Object
{
    
    public $_excludeModResourceFields = null;
    public $_prefixTvs = null;
    public $_tvPrefix = null;
    public $_tvPrefixSeparator = null;
    public $_tvPrefixToLower = null;
    public $_forceTypeCheck = null;
    public $_persistOnAssign = null;
    public $_useFoldersForAssoc = null;
    public $_createFoldersForAssoc = null;
    
    public $_id;
    public $_tvsLoaded = false;
    public $_assocNameMap = array();
    public $_assocIdMap = array();
    public $_parent = null;
    
    protected $_prototype;
    protected $_classTypeName;
    protected $_class;
    protected $_classId;
    protected $_properties = array();
    protected $_aggregates = array();
    protected $_composites = array();
    
    
    public static $_debugmode = false;
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function __construct($id = null, &$prototype = null, $classTypeName = null)
    {
        global $modx;
        $state = null;
        $class = null;
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__construct(): Constructor args, id= ' . $id . ', and classtypeName = "' . $classTypeName . '"');
        
        if ($id == null and $prototype == null) {
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object: __construct(), both id and $resource parameters were empty. Creating new Instance.');

            $typeName = get_class($this);
        
            $class =& Simplx_Mirage::getClass($typeName);

            if($class){
                
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object: __construct(), Got a valid class. Now lets create a Resource.');
                
                $newInstance = $modx->newObject('modResource');
                
                if(!$newInstance){
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object: __construct(), $modx->newObject() returned false. Aborting.');
                    return false;
                    
                }else{
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object: __construct(), New object instance created. Assigning it as prototype and continuing.');
                    $newInstance->set('template', $class->_id);
                    $newInstance->save();
                    
                    $prototype = $newInstance;

                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object: __construct(), New object instance has id '.$prototype->get('id'));

                }
                
            }else{
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object: __construct(), Simplx_Mirage::getClass() returned false. Aborting.');
                return false;
            }
        }else{
            // Proceed...
        }
        
        /*
        Remember that the Mirage Class is only a facade for a MODX Resource to add behavior and more on the fly.
        So, next, we need to add a prototype object to the Mirage Class instance. This prototype is always a 
        modResource with one assigned modTemplate.
        */
        
        if ($prototype === null) {
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__construct(): Got no prototype parameter. Creating a modResource instance.');
            $prototype = $modx->getObject('modResource', $id);
        } else {
            if (!$prototype instanceof modResource) {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object: __construct(), The prototype is not of type "modResource". Aborting.');
                return false;
            }
        }
        
        if (!$prototype === false) {
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__construct(): Prototype is valid. ' . $prototype->toJSON());
            /*
            Default the $_classTypeName variable to the class name.
            This means that Mirage will expect that there is a modTemplate with the same name
            in the MODx system.
            */
            
            if (!isset($classTypeName)) {
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__construct(): Setting the _classTypeName variable to "' . get_class($this) . '".');
                $this->_classTypeName = get_class($this);
            } else {
                // If we got a class name as parameter we use this.
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__construct(): Using classTypeName parameter "' . $classTypeName . '" as class name.');
                
                $this->_classTypeName = $classTypeName;
            }
            
            /* 
            Get the name and the id of the modTemplate that the Resource uses. 
            */
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__construct(): Trying to get the modTemplate object from the prototype.');
                
            $typeName = $prototype->getOne('Template');
            $typeName = $typeName->get('templatename');
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__construct(): The modTemplate is named "' . $typeName . '".');
            
            //if(!$this->_class){
            //	   $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object: __construct(), Can not get modTemplate. Type "'.$typeName.'" is not a compatible Class.');	    
            //return false;	
            //}
            
            
            /* 
            If the instance returned Simplx_Mirage_Object as class name the class has not
            been extended. This meens that we have to default the class name to the modTemplate name.
            */
            
            if ($this->_classTypeName == 'Simplx_Mirage_Object') {
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__construct(): Class name is "Simplx_Mirage_Object" so we set it to the name of its modTemplate "' . $typeName . '".');
                
                $this->_classTypeName = $typeName;
                
            }
            
            
            if (!$typeName) {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object: __construct(), Can not get modTemplate from the modResource object. Aborting.');
                return false;
            }
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__construct(): modTemplate name is "' . $typeName . '".');
            
            /* 
            Lets now check so that the modResource actually is of type Aircraft, in other words
            uses the correct Template.
            */
            
            // Only fail though if the $classTypeName is not set to the default modResource.
            if ($typeName != $this->_classTypeName) {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object: __construct(), Type "' . $typeName . '" not a compatible Class.');
                return false;
            } else {
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__construct(): classTypeName is the same as modTemplate name and is therefor valid.');
            }
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__construct(): Finally assigning the $prototype object to the internal _prototype variable.');
            
            // Get a ref to the Simplx_Mirage_Class associated with this object. The Mirage Class has all default settings
            // for the object.
            $this->_class =& Simplx_Mirage::getClass($typeName);
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__construct(): Assigning all defaults from the Class.');
            
            // If we got a valid reference we assign all defaults. The Class defaults are stored in the Classes 
            // default PropertySet.
            
            if ($this->_class) {
                $result = $this->setDefaultsFromClass($this->_class);
                
                if ($result) {
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__construct(): Successfully assigned defaults from the Class.');
                    
                } else {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object: __construct(), Unable to set Simplx_Mirage_Class defaults.');
                    return false;
                }
            } else {
                
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object: __construct(), Unable to get a valid Simplx_Mirage_Class reference.');
                return false;
                
            }
            
            $this->_prototype = $prototype;
            
            /*
            If we are supposed to use prefixed TVs, lets see that we have it configured.
            Per default, we use the Class name as prefix.
            */
            
            if ($this->_prefixTvs) {
                
                if (!isset($this->_tvPrefix)) {
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__construct(): Using Class name as default prefix.');
                    $tempClsName = $this->_classTypeName;
                } else {
                    $tempClsName = $this->_tvPrefix;
                }
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__construct(): Using prefix "' . $tempClsName . '" for TVs.');
                
                
                /* 
                Have we configured to use only lcase prefixes? If so fix class name before
                building the prefix string.
                */
                if ($this->_tvPrefixToLower) {
                    $tempClsName = strtolower($tempClsName);
                }
                
                $this->_tvPrefix = ($tempClsName . $this->_tvPrefixSeparator);
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__construct(): Complete prefix is "' . $this->_tvPrefix . '".');
            }
            
            
            
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->__construct(): Could not find any modResource instance with id ' . $id . '.');
        }

        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__construct(): Assigning internal id.');
        
        // All went well so we set the internal _id variable to reflect the prototypes id. 
        $this->_id = $id;
        
        // Also set the parent.
        $this->_parent = $this->_prototype->get('parent');
        
        return true;
        
    }
    
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function setDefaultsFromClass(&$classObject)
    {
        global $modx;
        
        if ($classObject instanceof Simplx_Mirage_Class) {
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->setClassDefaults(): Assigning all defaults from the Class.');
            
            // If we got a valid reference we assign all defaults. The Class defaults are stored in the Classes 
            // default PropertySet.
            
            if ($classObject) {
                /*
                Inherit and apply all property defaults from the assoc Simplx_Mirage_Class 
                If the class which extends the Simplx_Mirage_Object does not specify a custom default.	    	    
                */
                
                $this->_excludeModResourceFields = is_null($this->_excludeModResourceFields) ? $classObject->_excludeModResourceFields : $this->_excludeModResourceFields;
                $this->_prefixTvs                = is_null($this->_prefixTvs) ? $classObject->_prefixTvs : $this->_prefixTvs;
                $this->_tvPrefix                 = is_null($this->_tvPrefix) ? $classObject->_tvPrefix : $this->_tvPrefix;
                $this->_tvPrefixSeparator        = is_null($this->_tvPrefixSeparator) ? $classObject->_tvPrefixSeparator : $this->_tvPrefixSeparator;
                $this->_tvPrefixToLower          = is_null($this->_tvPrefixToLower) ? $classObject->_tvPrefixToLower : $this->_tvPrefixToLower;
                $this->_forceTypeCheck           = is_null($this->_forceTypeCheck) ? $classObject->_forceTypeCheck : $this->_forceTypeCheck;
                $this->_persistOnAssign          = is_null($this->_persistOnAssign) ? $classObject->_persistOnAssign : $this->_persistOnAssign;
                $this->_useFoldersForAssoc       = is_null($this->_useFoldersForAssoc) ? $classObject->_useFoldersForAssoc : $this->_useFoldersForAssoc;
                $this->_createFoldersForAssoc    = is_null($this->_createFoldersForAssoc) ? $classObject->_createFoldersForAssoc : $this->_createFoldersForAssoc;
                
            } else {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->setClassDefaults(), Unable to get a valid Simplx_Mirage_Class reference.');
                return false;
                
            }
            
            return true;
            
        } else {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->setClassDefaults(): Parameter &$object was not of instance Simplx_Mirage_Class.');
            return false;
        }
    }
    
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function setClass(&$object)
    {
        if ($object instanceof Simplx_Mirage_Class) {
            $this->_class = $object;
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function getClass()
    {
        if (isset($this->_class)) {
            return $this->_class;
        }
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function setPrototype(&$object)
    {
        if ($object instanceof modResource) {
            $this->_prototype = $object;
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function getPrototype()
    {
        if (isset($this->_prototype)) {
            return $this->_prototype;
        }
    }
    
    
    /*
    "Overides" the default modResource behaviour. 
    This enables us to persist the TV values stored in the Mirage object.
    */
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function save()
    {
        global $modx;
        $result;
        
        if (isset($this->_prototype)) {
            if (!$this->_persistOnAssign) {
                $result = $this->setTVValues();
            } else {
                $result = true;
            }
            
            if ($result) {
                $result = $this->_prototype->save();
                
                if ($result) {
                    $modx->invokeEvent('OnDocFormSave', array(
                        'mode' => 'upd',
                        'resource' => $this->_prototype,
                        'id' => $this->_id
                    ));
                    return true;
                } else {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->save(): Unable to save prototype modResource ' . $this->_id);
                    return false;
                }
                
            } else {
                // Unable to save TV values.
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->save(): Unable to save TVs.');
                return false;
            }
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->save(): Invalid prototype. Unable to save.');
            return false;
        }
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function getAggregates($className, $query = array())
    {
        global $modx;
        $resultList      = array();
        $assocFolderId   = null;
        $assocFolderName = null;
        $assocQuery      = array();
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->getAggregates(): $className = "' . $className . '", $query="' . json_encode($query) . '".');
        
        if ($this->_useFoldersForAssoc) {
            
            if (array_key_exists($className, $this->_assocNameMap)) {
                
                $assocFolderName = $this->_assocNameMap[$className];
                
                // Lets get the custom folder mapping for the class name.
                $assocQuery['parent:=']    = $this->_id;
                $assocQuery['pagetitle:='] = $className;
                $assocQuery['isfolder:=']  = '1';
                
                $result = Simplx_Mirage::getIds($className, $assocQuery);
                
                if ($result) {
                    $assocFolderId = $result[0]['id'];
                }
                
            } else {
                // No custom class name to folder name mapping was specified so we default to the $className parameter.
                $assocQuery['parent:=']    = $this->_id;
                $assocQuery['pagetitle:='] = $className;
                $assocQuery['isfolder:=']  = '1';
                
                $result = Simplx_Mirage::getIds($className, $assocQuery);
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->getAggregates(): Found the following aggr result "' . json_encode($result) . '".');
                
                if ($result) {
                    $assocFolderId = $result[0]['id'];
                }
                
            }
        }
        
        if (!$assocFolderId) {
            $assocFolderId = $this->_id;
        }
        
        // Save the id of the folder which contains the current aggregate class.
        $this->_assocIdMap[$className] = $assocFolderId;
        
        $query['parent:=']    = $assocFolderId;
        $query['class_key:='] = 'modSymLink';
        
        $result = Simplx_Mirage::getIds($className, $query, null, array(
            'c.content'
        ));
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->getAggregates(): Result from Simplx_Mirage::getIds() "' . json_encode($result) . '".');
        
        if (!is_array($result))
            return false;
        
        // Lets get the actual objects which the symlinks point to
        foreach ($result as $row) {
            $id = $row[0];
            if ($id) {
                $obj = new Simplx_Mirage_Object($id);
                if ($obj) {
                    $obj->_parent = $this->_id;
                    $resultList[] =& $obj;
                }
            }
            
        }
        
        if (is_array($resultList)) {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->getAggregates():  ' . count($resultList) . ' Objects of class = "' . $className . '" was found.');
            
            // Store the aggretages in a Class wide store.
            $this->_aggregates[$className] = $resultList;
            
            return $resultList;
        } else {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->getAggregates():  No Objects of class = "' . $className . '" was found.');
            return false;
        }
        
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function getComposites($className, $query = array())
    {
        global $modx;
        $assocFolderId   = null;
        $assocFolderName = null;
        $assocQuery      = array();
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->getComposites(): $className = "' . $className . '", $query="' . json_encode($query) . '".');
        
        if ($this->_useFoldersForAssoc) {
            
            if (array_key_exists($className, $this->_assocNameMap)) {
                
                $assocFolderName = $this->_assocNameMap[$className];
                
                // Lets get the custom folder mapping for the class name.
                $assocQuery['parent:=']    = $this->_id;
                $assocQuery['pagetitle:='] = $className;
                $assocQuery['isfolder:=']  = '1';
                
                $result = Simplx_Mirage::getIds($className, $assocQuery);
                
                if ($result) {
                    $assocFolderId = $result[0]['id'];
                }
                
            } else {
                
                // No custom class name to folder name mapping was specified so we default to the $className parameter.
                $assocQuery['parent:=']    = $this->_id;
                $assocQuery['pagetitle:='] = $className;
                $assocQuery['isfolder:=']  = '1';
                
                $result = Simplx_Mirage::getIds($className, $assocQuery);
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->getComposites(): Found the following result "' . json_encode($result) . '".');
                
                if ($result) {
                    $assocFolderId = $result[0]['id'];
                }
                
            }
        }
        
        if (!$assocFolderId) {
            $assocFolderId = $this->_id;
        }
        
        // Save the id of the folder which contains the current composite class.
        $this->_assocIdMap[$className] = $assocFolderId;
        
        
        $query['parent:=']    = $assocFolderId;
        $query['class_key:='] = 'modDocument';
        $query['template:=']  = Simplx_Mirage::getClass($className)->_id;
        
        $result = Simplx_Mirage::getObjects($className, $query);
        
        if (is_array($result)) {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->getComposites():  ' . count($result) . ' Objects of class = "' . $className . '" was found.');
            
            // Set a proper reference to the parent of each resource. If using folders for associated objects this gets get screwed otherwise
            foreach ($result as &$obj) {
                $obj->_parent = $this->_id;
            }
            
            // Reset the array pointer before returning it
            reset($result);
            
            // Store the composites in a Class wide store.
            $this->_composites[$className] = $result;
            
            return $result;
        } else {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->getComposites():  No Objects of class = "' . $className . '" was found.');
            
            return false;
        }
        
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    private function getAssocFolders()
    {
        global $modx;
        $folders = array();
        $modx->getObject('modResource', array(
            'parent' => $this_id,
            'isfolder' => true
        ));
        
        if (is_array($folders)) {
            return $folders;
        } else {
            return false;
            
        }
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function addComposite($classTypeName = null, $prototype = null)
    {
        global $modx;
        $composite;
        
        $result;
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->addComposite():  Class name "' . $classTypeName . '".');
        
        if (isset($classTypeName)) {
            // As the Class name was specified we assume that no prototype was supplied.
            
            $compositeClass =& Simplx_Mirage::getClass($classTypeName);
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->addComposite():  Got a reference to the Composite Class.');
            
            if ($this->_useFoldersForAssoc) {
                // Get the id of the Container Resource in which to place the new composite Resource.
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->addComposite():  Folders are used to store Composites.');
                
                $result = false; //array_key_exsits($classTypeName,$this->_assocIdMap);	
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->addComposite(): Does the Composite container id exist in the store? "' . $result . '".');
                
                if ($result) {
                    
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->addComposite(): The id for the Composites folder was in the assocIdMap collection. It is "' . $parent . '".');
                    $parent = $this->_assocIdMap[$classTypeName];
                    
                    
                } else {
                    // If the requested composite class name was not in the Id map we load and store it.
                    
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->addComposite(): There was no reference to the id of Composite Resource in the assocIdMap collection.');
                    
                    $result = $modx->getObject('modResource', array(
                        'parent' => $this->_id,
                        'pagetitle' => $classTypeName
                    ));
                    
                    if (!$result) {
                        // $result was false which meens that the class name is not valid where found.
                        $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->addComposite(): Unable to load Composites folder. Aborting.');
                        return false;
                    }
                    
                    $containerId = $result->get('id');
                    
                    $this->_assocIdMap[$classTypeName] = $containerId;
                    
                    $parent = $containerId;
                    
                    
                }
                
            } else {
                // Not using folders for associated object. 
                
                $parent = $this->_id;
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->addComposite():  Not using folders for associated object. id used is "' . $parent . '".');
                
            }
            
            if ($compositeClass) {
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->addComposite():  Creating new Resource with Class/Template "' . $compositeClass->_id . '".');
                
                $composite = $modx->newObject('modResource');
                $composite->set('pagetitle', $classTypeName);
                $composite->set('parent', $parent);
                $composite->set('template', $compositeClass->_id);
                $composite->set('published', true);
                $result = $composite->save();
                
                if ($result) {
                    $modx->invokeEvent('OnDocFormSave', array(
                        'mode' => 'new',
                        'resource' => $this->_prototype,
                        'id' => $composite->get('id')
                    ));
                } else {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->addComposite():  Unable to get the save the new composite Resource modResource->save() returned false. Aborting.');
                    return false;
                }
                
            } else {
                
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->addComposite():  Unable to get the Composite Class object. Simplx_Mireage::getClass() returned false. Aborting.');
                return false;
                // Unable to create class. Aborting.
                
            }
            
            return $composite;
            
        } elseif (isset($prototype)) {
            
            if ($prototype instanceof modResource) {
                
            } else {
                // Prototype must be of type modResource
            }
            
        } else {
            // Missing required params. Abort.
        }
        
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function addAggregate($referenceId, $altObjectName = null)
    {
        global $modx;
        $aggregateClass;
        $aggregateObject;
        $classTypeName;
        $result;
        $list;
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->addAggregate():  Object ref id "' . $referenceId . '".');
        
        if (isset($referenceId)) {
            
            // An aggregate is represented as a SymLink, so we need to load the original, source Resource first.
            $aggregateObject = $modx->getObject('modResource', $referenceId);
            
            if (!$aggregateObject) {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->addAggregate():  The associated object with id "' . $referenceId . '" does not exist. Aborting.');
                return false;
            } else {
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->addAggregate():  Found Object id "' . $referenceId . '".');
            }
            
            // Getting the Class / Template
            // Temporary hack to get template name. This should be done in the Simplx_Mirage class.
            $aggregateClass = $modx->getObject('modTemplate', $aggregateObject->get('template'));
            
            $classTypeName = $aggregateClass->get('templatename');
            
            unset($aggregateClass);
            
            $aggregateClass = Simplx_Mirage::getClass($classTypeName);
            
            if (!$aggregateClass) {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->addAggregate():  Unable to get class "' . $aggregateObject->get('template') . '" does not exist. Aborting.');
            }
            
            $classTypeName = $aggregateClass->_prototype->get('templatename');
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->addAggregate():  Got a reference to the Composite Class "' . $classTypeName . '".');
            
            if ($this->_useFoldersForAssoc) {
                // Get the id of the Container Resource in which to place the new aggregate Resource.
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->addAggregate():  Folders are used to store Aggregate.');
                
                $result = false; //array_key_exsits($classTypeName,$this->_assocIdMap);	
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->addAggregate(): Does the Aggregate container id exist in the store? "' . $result . '".');
                
                if ($result) {
                    
                    
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->addAggregate(): The id for the Aggregates folder was in the assocIdMap collection. It is "' . $parent . '".');
                    $parent = $this->_assocIdMap[$classTypeName];
                    
                    
                } else {
                    // If the requested composite class name was not in the Id map we load and store it.
                    
                    $result = $modx->getObject('modResource', array(
                        'parent' => $this->_id,
                        'pagetitle' => $classTypeName
                    ));
                    
                    if (!$result) {
                        // $result was false which meens that the class name is not valid where found.
                        $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->addComposite(): Unable to load Composites folder. Aborting.');
                        return false;
                    }
                    
                    $containerId = $result->get('id');
                    
                    $this->_assocIdMap[$classTypeName] = $containerId;
                    
                    $parent = $containerId;
                    
                }
            } else {
                // Not using folders for associated object. 
                
                $parent = $this->_id;
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->addAggregate():  Not using folders for associated object. id used is "' . $parent . '".');
                
            }
            
            if ($aggregateClass) {
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->addAggregate():  Creating new Resource with Class/Template "' . $aggregateClass->_id . '".');
                
                $aggregate = $modx->newObject('modSymLink');
                $aggregate->set('pagetitle', $classTypeName);
                $aggregate->set('parent', $parent);
                $aggregate->set('template', $aggregateClass->_id);
                $aggregate->set('content', $aggregateObject->get('id'));
                $aggregate->set('published', true);
                $result = $aggregate->save();
                
                if (!$result) {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->addAggregate():  Unable to get the save the new aggregate Resource modSymLink->save() returned false. Aborting.');
                    return false;
                }
                
            } else {
                
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->addAggregate():  Unable to get the Aggregate Class object. Simplx_Mireage::getClass() returned false. Aborting.');
                return false;
                // Unable to create class. Aborting.
                
            }
            
            return $aggregate;
            
        } else {
            // Missing required params. Abort.
        }
        
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function __get($name)
    {
        global $modx;
        $result;
        $tvName = '';
        
        if (array_key_exists($name, $this->_properties)) {
            return $this->_properties[$name];
        }
        
        // If the property was not found, defer to the prototype.
        if (isset($this->_prototype)) {
            $result = $this->_prototype->get($name);
            
            if (isset($result)) {
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__get("' . $name . '"): Found a get() property for "' . $name . '" has value "' . json_encode($result) . '".');
                
                // Cache the value locally
                $this->_properties[$name] = $result;
                return $result;
                
            } else {
                // Lets check and see if its a template variable we are looking for.
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__get("' . $name . '"): Found no matching get() method. Checking for a matching TV.');
                
                // Add prefix if configured so
                if ($this->_prefixTvs) {
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__get("' . $name . '"): Prefixing TV using "' . $this->_tvPrefix . '".');
                    $tvName = ($this->_tvPrefix . $name);
                    
                } else {
                    $tvName = $name;
                }
                
                $result = $this->_prototype->getTVValue($tvName);
                
                if (isset($result)) {
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__get("' . $name . '"): Matching TV has value "' . $result . '".');
                    // Cache the value locally
                    $this->_properties[$name] = $result;
                    return $result;
                } else {
                    // Sorry, this property does not exist in this context.
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__get("' . $name . '"): Found no matching TV returning null.');
                    
                    return null;
                }
            }
            
        } else {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__get("' . $name . '"): Object prototype not set. Returning null.');
        }
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function __set($name, $value)
    {
        global $modx;
        $result;
        
        // If the property was not found, defer to the prototype.
        
        if (isset($this->_prototype)) {
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__set("' . $name . '","' . $value . '")');
            
            $result = $this->_prototype->get($name);
            
            if (isset($result)) {
                // We got a valid prototype property, lets set the value.
                $this->_properties[$name] = $value;
                return $this->_prototype->set($name, $value);
                
            } else {
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__set("' . $name . '","' . $value . '"): Found no matching set() method. Checking for a matching TV.');
                
                // Lets check and see if its a template variable we are looking for.
                
                // Add prefix if configured so
                if ($this->_prefixTvs) {
                    $tvName = ($this->_tvPrefix . $name);
                    
                } else {
                    $tvName = $name;
                }
                
                $result = $this->_prototype->getTVValue($tvName);
                
                if (isset($result)) {
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object__set("' . $name . '","' . $value . '"): Saving value "' . $value . '" to TV "' . $name . '".');
                    // We got a valid prototype property, lets set the value.
                    $this->_properties[$name] = $value;
                    
                    if ($this->_persistOnAssign) {

                        $result = $this->_prototype->setTVValue($tvName, $value);
                        
                        if($result){
                            
                            $modx->invokeEvent('OnDocFormSave', array(
                                'mode' => 'upd',
                                'resource' => $this->_prototype,
                                'id' => $this->_id
                            ));
                            return true;
                            
                        }else{
                            $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->__set(): modResource->setTVValue("'.$tvName.'", "'.$value.'")');
                            return false;
                        }                        
                    }
                    
                } else {
                    // The property was not found in the prototype either. Return false.
                    return false;
                }
                
            }
        }
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function __call($name, $params)
    {
        global $modx;
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__call("' . $name . '","' . json_encode($params) . '")');
        
        // If the method was not found, defer to the prototype.
        if (isset($this->_prototype)) {
            
            $reflectionClass = new ReflectionClass(get_class($this->_prototype));
            return $reflectionClass->getMethod($name)->invokeArgs($this->_prototype,$params);
        }
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public static function __callStatic($name, $params)
    {
        global $modx;
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->__callStatic("' . $name . '","' . json_encode($value) . '")');
        
        // If the method was not found, defer to the prototype.
        if (isset($this->_prototype)) {
            
            $reflectionClass = new ReflectionClass(get_class($this->_prototype));
            // I think this should work even on static methods...	
            return $reflectionClass->getMethod($name)->invokeArgs(null,$params);
        }
        
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    protected function getTVValues()
    {
        global $modx;
        $name = '';
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->getTVValues()');
        
        $tvs = $this->_prototype->getTemplateVars();
        
        if (is_array($tvs)) {
            
            foreach ($tvs as $tv) {
                
                $name = $tv->get('name');
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->getTVValues(): Adding "' . $tv->get('name') . '" => "' . $tv->get('value') . '" to _properties.');
                
                
                // Strip prefix away if configured to use prefixes.
                if ($this->_prefixTvs) {
                    $name = str_replace($this->_tvPrefix, '', $name);
                }
                
                $this->_properties[$name] = $tv->get('value');
            }
            
            $this->_tvsLoaded = true;
            
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->__construct(): Could not load TVs.');
            return false;
        }
        
        return true;
        
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    protected function setTVValues($validateOnly = false)
    {
        global $modx;
        $name   = '';
        $tvName = '';
        $val;
        $result;
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->setTVValues()');
        
        /* 
        We cant really rely on that the Class signature (its public properties etc) have not 
        changed since we last loaded them so we reload them. 
        This way we let the current existing TV's decided which Mirage properties that are relevant
        at the moment of saving.
        */
        
        $tvs = $this->_prototype->getTemplateVars();
        
        if (is_array($tvs)) {
            
            foreach ($tvs as $tv) {
                unset($val);
                
                $tvName = $tv->get('name');
                
                // Strip prefix away if configured to use prefixes.
                if ($this->_prefixTvs) {
                    $name = str_replace($this->_tvPrefix, '', $tvName);
                } else {
                    $name = $tvName;
                }
                
                // Check so that the property actually exists in the _properties array. Otherwise just skip it.
                if (array_key_exists($name, $this->_properties)) {
                    
                    // Get the current value from Mirage.
                    $val = $this->_properties[$name];
                    
                    // If the value is valid we save it back to the TV object.
                    if ($val) {
                        
                        if (!$validateOnly) {
                            if (self::$_debugmode)
                                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->setTVValues(): Saving "' . $name . '" => "' . $val . '".');
                            
                            $result = $this->_prototype->setTVValue($tvName, $val);
                            
                            if (!$result) {
                                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->setTVValues(): modResource->setTVValue() returned false.');
                                return false;
                            } else {
                                if (self::$_debugmode)
                                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->setTVValues(): Saving TV "' . $tvName . '" => "' . $val . '" went well.');
                            }
                            
                        } else {
                            // Do some type of type checking later.
                        }
                    }
                }
            }
            
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->setTVValues(): Could not load TVs.');
            return false;
        }
        
        return true;
        
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function toArray($excludeDefault = false, $useClassNameWrap = false)
    {
        global $modx;
        $defaultProperties;
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->toArray()');
        
        if (!$this->_tvsLoaded) {
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->toArray(): Calling getTVValues().');
            
            if (!$this->getTVValues()) {
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->toArray(): getTVValues() returned false. Aborting.');
                return false;
            }
        } else {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->toArray(): tvsLoaded == true. _properties contains "' . json_encode($this->_properties) . '"');
        }
        
        if ($this->_excludeModResourceFields || $excludeDefault) {
            
            // Even if we exclude the modResource fields we still need the id for the object.	
            if (!array_key_exists('id', $this->_properties)) {
                $this->_properties['id'] = $this->_id;
            }
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->toArray(): Excluding modResource default Class properties.');
        } else {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->toArray(): Including modResource default Class properties.');
            
            $defaultProperties = $this->_prototype->toArray('');
            
            if (is_array($defaultProperties)) {
                $this->_properties = array_merge($this->_properties, $defaultProperties);
            } else {
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->toArray(): modResource->toArray() returned a non array object.');
            }
            $this->_properties[] = $this->_prototype->toArray('');
            
            
        }
        
        if ($useClassNameWrap && $this->_classTypeName != '') {
            return array(
                $this->_classTypeName => $this->_properties
            );
        } else {
            return $this->_properties;
        }
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function toJSON($excludeDefault = false, $useClassNameWrap = false)
    {
        global $modx;
        $defaultProperties;
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->toJSON()');
        
        if (!$this->_tvsLoaded) {
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->toJSON(): Calling getTVValues().');
            
            if (!$this->getTVValues()) {
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->toJSON(): getTVValues() returned false. Aborting.');
                return false;
            }
        } else {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->toJSON(): tvsLoaded == true. _properties contains "' . json_encode($this->_properties) . '"');
        }
        
        if ($this->_excludeModResourceFields || $excludeDefault) {
            
            // Even if we exclude the modResource fields we still need the id for the object.	
            if (!array_key_exists('id', $this->_properties)) {
                $this->_properties['id'] = $this->_id;
            }
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->toJSON(): Excluding modResource default Class properties.');
            
        } else {
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->toJSON(): Including modResource default Class properties.');
            
            $defaultProperties = $this->_prototype->toArray('');
            
            if (is_array($defaultProperties)) {
                $this->_properties = array_merge($this->_properties, $defaultProperties);
            } else {
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->toJSON(): modResource->toArray() returned a non array object.');
            }
        }
        
        if ($useClassNameWrap && $this->_classTypeName != '') {
            return json_encode(array(
                $this->_classTypeName => $this->_properties
            ));
        } else {
            return json_encode($this->_properties);
        }
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function fromArray($arr, $persist = false)
    {
        global $modx;
        $defaultProperties;
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->fromArray()');
        
        if ($this->_forceTypeCheck) {
            if (!array_key_exists($this->_classTypeName, $arr)) {
                /* 
                This serialized state array does not explicitly signal that it is in fact of	
                the correct heritage. This helps to implement a loose type of type safety.
                */
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->fromArray(): $_forceTypeCheck is set to true, and the object state array is missing a type name key. Unable to validate object type. Aborting.');
                return false;
                
            } else {
                /* If we got a type name, its value is expected to be the property collection ({"Aircraft":{"registration_number":"1234"}}).
                So, we re-assign the $arr variable to hold the properties.
                */
                $arr = $arr[$this->_classTypeName];
                
                if (!is_array($arr)) {
                    // Hey! There were no properties here. Aborting. 
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->fromArray(): $_forceTypeCheck is set to true however no property collection was found. Aborting.');
                    return false;
                }
                
            }
        }
        
        if ($this->_excludeModResourceFields && $excludeDefault) {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Object->fromArray(): Excluding modResource default modResource Class properties.');
            
            $defaultProperties = $this->_prototype->toArray('');
            
            
            // Got a valid properties array?
            
            if (is_array($defaultProperties)) {
                /*
                Loop through all modResource properties and set its values.
                */
                foreach ($arr as $prop => &$val) {
                    /*
                    If the modResource property is stored in the $defaultProperties array we do NOT store it since $_excludeDefault is set to true.
                    */
                    if (!array_key_exists($prop, $defaultProperties)) {
                        $result = $this->__set($prop, $val);
                        
                        // If we had problems storing the value.
                        if (!$result) {
                            $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->fromArray(): Unable to set the modResource property "' . $prop . '".');
                        }
                        
                    }
                }
                
            } else {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->fromArray(): Unable to get the modResource properties. modResource->toArray() returned false.');
            }
        } else {
            
            /*
            Loop through all modResource properties and set its values.
            */
            foreach ($arr as $prop => &$val) {
                $result = $this->__set($prop, $val);
                
                
                if (!$result) {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->fromArray(): Unable to set the property "' . $prop . '".');
                }
                
            }
        }
        
        if ($this->_classTypeName == '') {
            // Reset the array just to be sure.
            reset($arr);
            // And get first key which is presumed to be class name. This is not the best of solutions. will fix.
            $this->_classTypeName = key($arr);
        }
        
        return true;
        
    }
    
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function fromJSON($json)
    {
        
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function renderAspect($aspect = 'input')
    {
        global $modx;
        $result = '';
        
        $params = $this->toArray();
        
        if (is_array($params)) {
            $result = $this->_class->renderAspect($aspect, $params);
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->renderAspect(): Unable to get object state. $this->toArray() returned false.');
            return false;
        }
        
        return $result;
    }
    
}
