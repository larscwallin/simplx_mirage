<?php

/**
 * Simplx_Mirage_Class wraps a named modTemplate object.
 * Any Simplx_Mirage_Object using a particular Simplx_Mirage_Class will "inherit" many
 * of it's public properties as defaults.
 *
 * @package Simplx_Mirage
 */
class Simplx_Mirage_Class
{
    
    /**
     * Unique id key for the Class. This is always the same as the modTemplate object which it wraps. 
     *
     * @access public
     * @var int 
     */
    public $_id;
    
    /**
     * This property sets the default location in the site structure for modResources using this
     * this modTemplate wrapper.  
     *
     * @access public
     * @var int 
     */
    public $_defaultObjectLocation = 0;
    
    /**
     * Class URI should point to the URI (ex. http://mysite.com/api/Whatnot/) where Objects (modResource's) 
     * of this type are found. 
     *
     * @access public
     * @var string 
     */
    public $_classUri;
    
    /**
     * The prototype is an instance of the actual modTemplate being wrapped. 
     *
     * @access public
     * @var modTemplate 
     */
    public $_prototype;
    
    /**
     * Class name is the Simplx Mirage moniker, or alias, for the modTemplate object. 
     * By default, this is the same the template instance name.
     *
     * @access public
     * @var string 
     */
    public $_classTypeName;
    
    /**
     * The properties collection is an array where all the Simplx Mirage Class properties (TV's)
     * are cached for snappy retrieval. This should not really be used on its own
     * and should probably really be protected.
     *
     * @access public
     * @var array 
     */
    public $_properties = array();
    
    /**
     * If excludeModResourceFields is set to true, toJSON/toArray will exclude all modResource fields for this
     * Simplx Mirage Class. The serialized data only contain the TV's. Handy when you want to really emulate custom 
     * object types.
     *
     * @access public
     * @var boolean 
     */
    public $_excludeModResourceFields = false;
    
    /**
     * Should prefixes be used to indicate which modTemplate (Simplx_Mirage_Class) a modTemplateVar belongs to?
     * Its HIGHLY recommended to set this to true as it makes your model infinitly more intuitive. 
     *
     * @access public
     * @var boolean 
     */
    public $_prefixTvs = true;
    
    /**
     * Actual TV prefix to use. This default to ($_classTypeName.'_'.TV name)
     *
     * @access public
     * @var string 
     */
    public $_tvPrefix;
    
    /**
     * Prefix separator. This default to '_'.
     *
     * @access public
     * @var string 
     */
    public $_tvPrefixSeparator = '_';
    
    /**
     * Should we accept prefix regardless of case? A good convention is to use upper case names
     * for our Simplx Mirage Classes (modTemplates). By default TV prefixing is case sensitive.
     *
     * @access public
     * @var boolean 
     */
    public $_tvPrefixToLower = false;
    
    /**
     * Should we force "type check" the $_classTypeName against the name of the modTemplate prototype?
     *
     * @access public
     * @var boolean 
     */
    public $_forceTypeCheck = true;
    
    /**
     * Should we persist (save) property values directly on assignment?
     * NOT IMPLEMENTED
     *
     * @access public
     * @var boolean 
     */
    public $_persistOnAssign = false;
    
    /**
     * 
     *
     * @access public
     * @var array 
     */
    public $_propertyObjects = array();
    
    /**
     * 
     *
     * @access public
     * @var array 
     */
    public $_propertyValidationRules = array();
    
    /**
     * Maps TV input formats to php types. Used for serialization. 
     *
     * @access public
     * @var array 
     */
    public $_propertyTypeMap = array('text' => 'string', 'checkbox' => 'boolean', 'resourcelist' => 'integer', 'date' => 'date', 'time' => 'time', '*' => 'string');
    
    /**
     * Should associations created for Objects of this Class be stored in  
     * folders, as child resources?
     *
     * @access public
     * @var boolean 
     */
    public $_useFoldersForAssoc = false;
    
    /**
     * If folders for associated objects are not present, should we create them?  
     *
     * @access public
     * @var boolean 
     */
    public $_createFoldersForAssoc = true;
    
    /**
     * This map is used to map associated types to custom folder names when $_useFoldersForAssoc is true.
     *
     * @access public
     * @var array 
     */
    public $_assocNameMap = array();
    
    /**
     * Valid composite types. The type names should be those used by the referd to Simplx Mirage Classes (modTemplates)
     *
     * @access public
     * @var array 
     */
    public $_composites = array();
    
    /**
     * Valid aggregate types. The type names should be those used by the referd to Simplx Mirage Classes (modTemplates)
     *
     * @access public
     * @var array 
     */
    public $_aggregates = array();
    
    /**
     * Valid association types. The type names should be those used by the referd to Simplx Mirage Classes (modTemplates)
     *
     * @access public
     * @var array 
     */
    public $_associations = array();
    
    private $_prototypePropertySet = array();
    
    public static $_debugmode = false;
    
    /**
     * 
     *
     * @static
     * @param 
     * @return 
     */
    public function __construct($className = null, &$prototype = null)
    {
        global $modx;
        $query;
        $ruleSet;
        $propertyName;
        $elementArray;
        $inputPropertiesTmp;
        $inputProperties;
        
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->__construct(): Constructor args, $className = "' . $className . '".');
        
        if (isset($prototype)) {
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->__construct(): $prototype is set.');
            
            if (!$prototype instanceof modTemplate) {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Class->__construct(): $prototype parameter was not of type "modTemplate". Aborting.');
                return false;
            }
            $this->_prototype =& $prototype;
            
        } else {
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->__construct(): $prototype is not set.');
            
            // Get the modTemplate which represents our Class. If class name is an int we get it using id.
            
            if (!is_numeric($className)) {
                $query = array(
                    'templatename' => $className
                );
            } else {
                $query = array(
                    'id' => $className
                );
            }
            
            $prototype = $modx->getObject('modTemplate', $query);
            
            if (!$prototype) {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Class->__construct(): Could not get a valid modTemplate named "' . $className . '". Aborting.');
                return false;
            } else {
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->__construct(): Setting $_prototype property.');
                $this->_prototype     = $prototype;
                $this->_classTypeName = $className;
                $this->_tvPrefix      = $className;
                $this->_id            = $prototype->get('id');
            }
            
        }
        
        /*
        We hould now have a valid modTemplate object to play with.
        
        */
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->__construct(): Using prototypes PropertySet to assign defaults.');
        /*
        Get the modTemplates PropertySet
        */
        
        $this->_prototypePropertySet = $this->_prototype->getPropertySet('simplx.mirage.class');
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->__construct(): PropertySet holds "' . json_encode($this->_prototypePropertySet) . '".');
        
        /*
        Apply all defaults for the Simplx Mirage Class. Defaults are specified in the default Property Set for the 
        modTemplate prototype.
        */
        
        
        if (is_array($this->_prototypePropertySet) && count($this->_prototypePropertySet) > 0) {
            
            $this->_excludeModResourceFields = array_key_exists('excludeModResourceFields', $this->_prototypePropertySet) ? $this->_prototypePropertySet['excludeModResourceFields'] : $this->_excludeModResourceFields;
            $this->_prefixTvs                = array_key_exists('prefixTvs', $this->_prototypePropertySet) ? $this->_prototypePropertySet['prefixTvs'] : $this->_prefixTvs;
            $this->_tvPrefix                 = array_key_exists('tvPrefix', $this->_prototypePropertySet) ? $this->_prototypePropertySet['tvPrefix'] : $this->_tvPrefix;
            $this->_tvPrefixSeparator        = array_key_exists('tvPrefixSeparator', $this->_prototypePropertySet) ? $this->_prototypePropertySet['tvPrefixSeparator'] : $this->_tvPrefixSeparator;
            $this->_tvPrefixToLower          = array_key_exists('tvPrefixToLower', $this->_prototypePropertySet) ? $this->_prototypePropertySet['tvPrefixToLower'] : $this->_tvPrefixToLower;
            $this->_forceTypeCheck           = array_key_exists('forceTypeCheck', $this->_prototypePropertySet) ? $this->_prototypePropertySet['forceTypeCheck'] : $this->_forceTypeCheck;
            $this->_persistOnAssign          = array_key_exists('persistOnAssign', $this->_prototypePropertySet) ? $this->_prototypePropertySet['persistOnAssign'] : $this->_persistOnAssign;
            $this->_useFoldersForAssoc       = array_key_exists('useFoldersForAssoc', $this->_prototypePropertySet) ? $this->_prototypePropertySet['useFoldersForAssoc'] : $this->_useFoldersForAssoc;
            $this->_createFoldersForAssoc    = array_key_exists('createFoldersForAssoc', $this->_prototypePropertySet) ? $this->_prototypePropertySet['createFoldersForAssoc'] : $this->_createFoldersForAssoc;
            $this->_defaultObjectLocation    = array_key_exists('defaultObjectLocation', $this->_prototypePropertySet) ? $this->_prototypePropertySet['defaultObjectLocation'] : $this->_defaultObjectLocation;
            $this->_classUri                 = array_key_exists('classUri', $this->_prototypePropertySet) ? $this->_prototypePropertySet['classUri'] : $this->_classUri;
            
            $this->_aggregates   = array_key_exists('aggregates', $this->_prototypePropertySet) ? json_decode($this->_prototypePropertySet['aggregates'], true) : $this->_aggregates;
            $this->_composites   = array_key_exists('composites', $this->_prototypePropertySet) ? json_decode($this->_prototypePropertySet['composites'], true) : $this->_composites;
            $this->_associations = array_key_exists('associations', $this->_prototypePropertySet) ? json_decode($this->_prototypePropertySet['associations'], true) : $this->_associations;
            
            if ($this->_prefixTvs && $this->_tvPrefix === '') {
                $this->_tvPrefix = $className;
            }
            
        } else {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->__construct(): PropertySet was empty.');
        }
        
        /*
        Get Properties (TV's)
        */
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->__construct(): Fetching all TVs.');
        
        $properties = $this->_prototype->getTemplateVars();
        
        // Reseting the array just in case.
        reset($properties);
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->__construct(): Number of $properties "' . count($properties) . '".');
        
        if (!is_array($properties)) {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Class->__construct(): Could not get the modTemplateVars array from the modTemplate "' . $className . '". Aborting.');
            return false;
        }
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->__construct(): Setting $_properties property.');
        
        // Persist the TV object collection
        $this->_propertyObjects = $properties;
        
        
        // Iterate through the collection and serialize a simplified version of each TV.
        foreach ($properties as $prop) {
            
            $propertyName = $prop->get('name');
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->__construct(): Current property "' . $propertyName . '".');
            
            // Split the input elements list to array
            $elementArray = explode('||', $prop->elements);
            
            
            // Get the input options which will be used for validation.
            $inputPropertiesTmp = $prop->input_properties;
            
            if ($inputPropertiesTmp !== '') {
                // Fix the input_properties string which for some utterly illogical reason is malformed json.
                
                $preg = '/[a-z].[0-9]?[0-9]:/i';
                
                $inputPropertiesTmp = preg_replace($preg, '', $inputPropertiesTmp);
                
                $inputPropertiesTmp = str_replace('{', '[', $inputPropertiesTmp);
                $inputPropertiesTmp = str_replace(';}', ']', $inputPropertiesTmp);
                $inputPropertiesTmp = str_replace(';', ',', $inputPropertiesTmp);
                
            } else {
                // If we have no input properties for the TV we default the inputProperties variable to an empty array.
                $inputPropertiesTmp = '[]';
            }
            
            // Turn the properties string to array format.
            $inputPropertiesTmp = json_decode($inputPropertiesTmp);
            
            if ($inputPropertiesTmp) {
                $i = 0;
                
                while ($i < count($inputPropertiesTmp)) {
                    $inputProperties[$inputPropertiesTmp[$i++]] = $inputPropertiesTmp[$i++];
                }
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->__construct(): Input validators "' . json_encode($inputProperties) . '".');
                
            } else {
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->__construct(): Got no Input validators.');
                $inputProperties = array();
            }
            
            // If we your tv prefixes we must make sure not to return any incomp. properties.
            if ($this->_prefixTvs) {
                $pos = strpos($propertyName, $this->_tvPrefix);
                
                if ($pos !== 0) {
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->__construct(): Got an incompatible TV. "' . $propertyName . '" does not match prefix.');
                    unset($inputPropertiesTmp);
                    unset($inputProperties);
                    continue;
                } else {
                    $pos = strpos($propertyName, $this->_tvPrefixSeparator);
                    if ($pos) {
                        $propertyName = substr($propertyName, ($pos + 1));
                    }
                }
            }
            
            if (array_key_exists($propertyName, $this->_propertyValidationRules)) {
                // Get possible validation rules specified by the overriding Simplx_Mirage_Class
                $ruleSet = $this->_propertyValidationRules[$propertyName];
                
            } else {
                // No custom validation rules set at this time. 
                
                $ruleSet = array(
                    'required' => (array_key_exists('allowBlank', $inputProperties) ? $inputProperties['allowBlank'] : 'false'),
                    'pattern' => (array_key_exists('pattern', $inputProperties) ? $inputProperties['pattern'] : ''),
                    'maximum' => (array_key_exists('maxValue', $inputProperties) ? $inputProperties['maxValue'] : ''),
                    'minimum' => (array_key_exists('minValue', $inputProperties) ? $inputProperties['minValue'] : ''),
                    'minItems' => (array_key_exists('minItems', $inputProperties) ? $inputProperties['minItems'] : ''),
                    'maxItems' => (array_key_exists('maxItems', $inputProperties) ? $inputProperties['maxItems'] : ''),
                    'uniqueItems' => (array_key_exists('uniqueItems', $inputProperties) ? $inputProperties['uniqueItems'] : ''),
                    'minLength' => (array_key_exists('minLength', $inputProperties) ? $inputProperties['minLength'] : ''),
                    'maxLength' => (array_key_exists('maxLength', $inputProperties) ? $inputProperties['maxLength'] : ''),
                    'default' => $prop->get('default_text')
                );
                
                if(count($elementArray) > 0 && $elementArray[0] !== ''){
                   $ruleSet['enum'] = $elementArray;

                }

                // Remove unset rules
                foreach($ruleSet as $key => &$rule){
                    if($rule == ''){
                        unset($ruleSet[$key]);
                    }
                }
            }
            
            $this->_properties[$propertyName] = array(
                'id' => $prop->get('id'),
                'type' => (array_key_exists($prop->get('type'), $this->_propertyTypeMap) ? $this->_propertyTypeMap[$prop->get('type')] : $this->_propertyTypeMap['*']),
                'title' => $propertyName
            );
            
            // Merge the custom/default ruleSet with then mandatory "property properties".
            $this->_properties[$propertyName] = array_merge($this->_properties[$propertyName], $ruleSet);
        }
        
        
        
        //$this->_properties = $properties;	  
        
        return true;
        
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function toJSON()
    {
        $result = $this->toArray();
        //$result = json_encode($result);
        
        if (!$result) {
            return false;
        } else {
            return $result;
        }
        
        
    }
    
    public function toArray()
    {
        $result = array(
            'name' => $this->_classTypeName,
            'type' => 'object',
            'properties' => $this->_properties
        );
        
        if (!$result) {
            return false;
        } else {
            return $result;
        }
        
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function createClassSQLView(){
        global $modx;
        
        $objectPropertiesViewName = $modx->getOption('simplx.mirage.object.viewname'); 
        $tablePrefix = ('`'.$modx->getOption(xPDO::OPT_TABLE_PREFIX).'`');
        $modTemplateVarTemplateTable = $modx->getTableName('modTemplateVarTemplate');
        $modTemplateVarTable = $modx->getTableName('modTemplateVar');
        $modTemplateVarResourceTable = $modx->getTableName('modTemplateVarResource');
        
        if(!$objectPropertiesViewName){
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->createClassSQLView(): Unable to find the "simplx.mirage.object.viewname" setting in MODx System Settings. Aborting.');
            return false;
            
        }else{
            $objectPropertiesViewName = ($objectPropertiesViewName.'.'.$this->_classTypeName);
        }
        
        /*
        	I suggest not removing the MERGE algorithm since not using it would ditch the underlying table indexes.
        	Remember however that MERGE does not work well with aggregate functions and DISTINC, ORDER BY etc
        */
        $createViewQuery = '
        CREATE OR REPLACE ALGORITHM=MERGE VIEW `'.$objectPropertiesViewName.'` AS 
        SELECT  
        	`val`.`contentid` AS `modresource.id`,
        	`var`.`id` AS `modtemplatevar.id`,
        	`tpl`.`templateid` AS `modtemplate.id`,
        	`var`.`name` AS `modtemplatevar.name`,
        	`val`.`value` AS `modtemplatevarresource.value`
        FROM 
        	'.$modTemplateVarTable.' AS  `var`, 
        	'.$modTemplateVarResourceTable.' AS `val`, 
        	'.$modTemplateVarTemplateTable.' AS `tpl` 
        WHERE 
        	`val`.`tmplvarid` = `var`.`id` 
        	AND 
        	`tpl`.`tmplvarid` = `var`.`id`
        	AND
        	`tpl`.`templateid` = '.$this->_id.'
        	;
        ';
        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->createClassSQLView(): $createViewQuery = '.$createViewQuery);
        $result = $modx->exec($createViewQuery);        
        
        return $result;
    }  
        
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function createClassContainer($location = null, $alias = null, $defaults = array())
    {
        global $modx;
        
        if ($location === null) {
            $location = $this->_defaultObjectLocation;
        } else {
            
        }
        
        if ($alias === null) {
            $alias = $this->_classTypeName;
        } else {
            
        }
        
        $res = $modx->newObject('modResource');
        
        if ($res) {
            
            $res->set('parent', $location);
            $res->set('isfolder', true);
            $res->set('pagetitle', $alias);
            $res->set('published', true);
            
            $result = $res->save();
            
            if (!$result) {
                
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->createClassContainer(): Unable to create save object for associated class "' . $alias . '".');
                return false;
            } else {
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->createClassContainer() : Created container "' . $alias . '".');
                return true;
            }
            
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->createClassContainer(): Unable to create folder for associated class "' . $alias . '".');
            return false;
        }
    }
    
    
        
    /**
     * 
     *
     * @param 
     * @return 
     */
    protected function applyDefaultsToObject(&$instanceObject)
    {
        global $modx;
        
        if ($instanceObject instanceof Simplx_Mirage_Object) {
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->applyDefaultsToObject(): Assigning all defaults from the Class.');
            
            /*
            Inherit and apply all property defaults from the assoc Simplx_Mirage_Class 
            If the class which extends the Simplx_Mirage_Object does not specify a custom default.	    	    
            */
            
            $instanceObject->_excludeModResourceFields = is_null($instanceObject->_excludeModResourceFields) ? $classObject->_excludeModResourceFields : $instanceObject->_excludeModResourceFields;
            $instanceObject->_prefixTvs                = is_null($instanceObject->_prefixTvs) ? $classObject->_prefixTvs : $instanceObject->_prefixTvs;
            $instanceObject->_tvPrefix                 = is_null($instanceObject->_tvPrefix) ? $classObject->_tvPrefix : $instanceObject->_tvPrefix;
            $instanceObject->_tvPrefixSeparator        = is_null($instanceObject->_tvPrefixSeparator) ? $classObject->_tvPrefixSeparator : $instanceObject->_tvPrefixSeparator;
            $instanceObject->_tvPrefixToLower          = is_null($instanceObject->_tvPrefixToLower) ? $classObject->_tvPrefixToLower : $instanceObject->_tvPrefixToLower;
            $instanceObject->_forceTypeCheck           = is_null($instanceObject->_forceTypeCheck) ? $classObject->_forceTypeCheck : $instanceObject->_forceTypeCheck;
            $instanceObject->_persistOnAssign          = is_null($instanceObject->_persistOnAssign) ? $classObject->_persistOnAssign : $instanceObject->_persistOnAssign;
            $instanceObject->_useFoldersForAssoc       = is_null($instanceObject->_useFoldersForAssoc) ? $classObject->_useFoldersForAssoc : $instanceObject->_useFoldersForAssoc;
            $instanceObject->_createFoldersForAssoc    = is_null($instanceObject->_createFoldersForAssoc) ? $classObject->_createFoldersForAssoc : $instanceObject->_createFoldersForAssoc;
                
            return true;
            
        } else {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Object->applyDefaultsToObject(): Parameter $instanceObject was not of instance Simplx_Mirage_Object.');
            return false;
        }
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function newObject($defaults = array(), &$prototype = null)
    {
        global $modx;
        $result      = false;
        $prototypeId = 0;
        $assoc       = array();
        $res;
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->newObject()');
        
        if ($this->_id) {
            
            if (!$prototype) {
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->newObject() : No prototype for the new Object was supplied.');
                
                $prototype = $modx->newObject('modResource', $defaults);
                
                if (!$prototype) {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->newObject(): Unable to create object of type "' . $className . '". $modx->newObject() returned false. Aborting.');
                    return false;
                } else {
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->newObject() : Successfully created a modResource for the new Object.');
                }
                
            } else {
                if (!(is_object($prototype) && ($prototype instanceof modResource || $prototype instanceof modResourceInterface))) {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->newObject(): Unable to create object. Prototype was of invalid type. Aborting.');
                    return false;
                } else {
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->newObject() : Prototype was of correct type.');
                }
                
            }
            
            // Make sure it ends up in the right place in the site structure.
            if (!array_key_exists('parent', $defaults)) {
                $prototype->set('parent', $this->_defaultObjectLocation);
            }
            
            // Make sure we have a name (pagetitle).
            if (!array_key_exists('pagetitle', $defaults)) {
                $prototype->set('pagetitle', $this->_classTypeName);
            }
            
            // Default to public.
            if (!array_key_exists('published', $defaults)) {
                $prototype->set('published', true);
            }
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->newObject() : Setting template property of the prototype to "' . $this->_id . '".');
            
            // Reset the Resource template to the Class id just in case.
            $prototype->set('template', $this->_id);
            
            $result = $prototype->save();
            
            
            if (!$result) {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->newObject(): Unable to create object. modResource->save() returned false. Aborting.');
                return false;
            } else {
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->newObject() : Successfully saved prototype. Got new id "' . $prototype->get('id') . '"');
                
            }
            
            $prototypeId = $prototype->get('id');
            
            if ($prototypeId) {
                
                if ($this->_useFoldersForAssoc) {
                    
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->newObject() : Using folders for assoc objects.');
                    
                    $assoc = array_merge($this->_aggregates, $this->_composites, $this->_associations);
                    
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->newObject() : Assoc objects "' . json_encode($this->_aggregates) . '".');
                    
                    foreach ($assoc as $className => $alias) {
                        
                        $result = $this->createClassContainer($prototypeId, $alias);
                    }
                    
                } else {
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->newObject() : Not using folders for assoc objects.');
                    
                }
                
                if (!class_exists($this->_classTypeName)) {
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->newObject() : Creating the Simplx_Mirage_Object instance.');
                    
                        $result = new Simplx_Mirage_Object($prototypeId, $prototype);

                } else {
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->newObject() : Creating the custom class instance.');
                    
                    $result = new $this->_classTypeName($prototypeId, $prototype);
                }
                
                if ($result) {
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->newObject() : Returning Simplx_Mirage_Object instance '.$result->get('id'));
                    return $result;
                } else {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->newObject(): Unable to create Simplx_Mirage_Object object for resource.');
                    return false;
                }
                
            } else {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->newObject(): New object is missing valid id. Aborting.');
                return false;
            }
            
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->newObject(): Class not is missing its internal _id reference to modTemplate. Aborting.');
            return false;
        }
    }
    
    /**
     * 
     *
     * @param 
     * @return 
     */
    public function getObject($id, $forceCacheRefresh = false)
    {
        
        // Check for cached object instance
        if(array_key_exists($id, Simplx_Mirage::$_objectStore) && $forceCacheRefresh == false){
            $object = Simplx_Mirage::$_objectStore[$id];
        }else{
            $object = new Simplx_Mirage_Object($id);

            if ($object) {
                Simplx_Mirage::$_objectStore[$id] =& $object;    
            } else {
                
            }

        }
        
        
        if ($object) {
            return $object;
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
    public function getObjects($query)
    {
        global $modx;
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->getObjects()');
        
        $prefix      = $this->_tvPrefix . $this->_tvPrefixSeparator;
        $objectQuery = array();
        
        /*    
        Moved prefixing to Simplx_Mirage::getObjects()
        */
        
        $result = Simplx_Mirage::getObjects($this->_classTypeName, $query);
        
        if (is_array($result)) {
            return $result;
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
    public function renderAspect($aspect = '', $aspectParameters = array(), $forInstance = 0)
    {
        global $modx;
        $output       = '';
        $aspectString = '';
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage_Class->renderAspect()');
        
        
        if (!$this->_properties) {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage_Class->__renderAspect(): The $_properties array is empty. Aborting.');
            return false;
        }
        
        switch ($aspect) {
            case 'input':
                foreach ($this->_propertyObjects as &$property) {
                    
                    $output = $output . $property->renderInput($forInstance);
                    
                }
                
                $aspectString = ('<form name="' . $this->_classTypeName . '" data-class_key="modResource" id="" data-classtypename="' . $this->_classTypeName . '">') . ($output . '</form>');
                
                break;
            
            case 'output':
                foreach ($this->_propertyObjects as &$property) {
                    
                    $output = $output . $property->renderOutput($forInstance);
                    
                }
                break;
            
            default;
                
                $aspectString = $modx->runSnippet($aspect, $aspectParameters);
                
        }
        
        return $aspectString;
        
    }
}
