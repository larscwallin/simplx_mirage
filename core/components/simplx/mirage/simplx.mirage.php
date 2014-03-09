<?php

require_once($modx->getOption('core_path').'/components/simplx/mirage/simplx_mirage_class.php');
require_once($modx->getOption('core_path').'/components/simplx/mirage/simplx_mirage_object.php');

/**
 * Simplx_Mirage is the base class which govern all Simplx_Mirage_Class / Simplx_Mirage_Object
 * instances. The class also contains utility functionality which is global in the scope of the package.
 * @package Simplx_Mirage
 */
class Simplx_Mirage
{
    /**
     * Should Simplx_Mirage automatically determine where to place objects?
     *
     * @access public
     * @static
     * @var boolean 
     */
    public static $_autoMapObjectLocation = false;
    
    /**
     * Should Simplx_Mirage automatically create location for objects if they are missing?
     *
     * @access public
     * @static
     * @var boolean 
     */
    public static $_autoCreateObjectLocation = false;
    
    /**
     * Should Simplx_Mirage use strict name matching of Classes extending Simplx_Mirage_Object?
     *
     * @access public
     * @static
     * @var boolean 
     */
    public static $_forceTypeCheck = true;
    
    /**
     * Should Simplx_Mirage output debug info to the modx console?
     *
     * @access public
     * @static
     * @var boolean 
     */
    public static $_debugmode = false;
    public static $_usePreflight = true;
    
    /**
     * Global cache array which store initialized instances of Simplx_Mirage_Class objects for
     * duration of the request.
     *
     * @access public
     * @static
     * @var array 
     */
    public static $_classStore = array();
    
    /**
     * Global cache array which store initialized instances of Simplx_Mirage_Object objects for
     * duration of the request.  
     *
     * @access public
     * @static
     * @var array 
     */
    public static $_objectStore = array();
    
    
    /**
     * Class constructor. Not implemented at this time.
     *
     * @param  
     * @return 
     */
    public function __construct($id)
    {
        
    }
    
    /**
     * This is a global utility method which will return an object of a specific id. 
     * This method is NOT IMPLEMENTED at this time.
     *
     * @static
     * @param $className The class name of the Object which to fetch.
     * @param $id The id of the Object which to fetch.
     * @return Simplx_Mirage_Object|false 
     */
    public static function getObject($className, $id)
    {
        if ($className && $id) {
            
        }
    }
    
    /**
     * Returns a Simplx_Mirage_Class object wrapping its corresponding modTemplate object.
     *
     * @static
     * @param @className Any existing MODx modTemplate object. 
     * @return Simplx_Mirage_Class|false 
     */
    public static function getClass($className)
    {
        global $modx;
        $result = null;
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getClass(): Param $className = "' . $className . '".');
        
        if ($className) {
            
            if (!array_key_exists($className, self::$_classStore)) {
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getClass(): Class was not in the store. Lets create it and cache it.');
                
                $result = new Simplx_Mirage_Class($className);
                
                if ($result) {
                    
                    $className = $result->_prototype->get('templatename');
                    
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getClass(): Storing Class "' . $className . '".');
                    
                    self::$_classStore[$className] =& $result;
                    return $result;
                    
                } else {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->getClass(): Unable to create an instance of Class "' . $className . '". Aborting.');
                    return false;
                }
                
            } else {
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getClass(): Class "' . $className . '" was in the store. Getting and returning it.');
                
                $result = self::$_classStore[$className];
                return $result;
            }
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->getClass(): Param $className was empty. Aborting.');
            return false;
            
        }
    }
    
    /**
     * Returns an array of id's based on Simplx_Mirage Class name and optionally a 
     * constraining query in XPDO format.
     *
     * @static
     * @param 
     * @return array|false 
     */
    public static function getIds($className, $query = array(), $prototypeName = null, $fields = null)
    {
        global $modx;
        $resultList = array();
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getIds(): Params $className = "' . $className . '", $query = "' . json_encode($query) . '", $prototypeName = "' . $prototypeName . '".');
        
        // Lets prepare the query statement by normalizing it to contain field, operator and constraint
        $query = self::prepareQueryStatement($query);
        
        if ($query === false) {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->getIds(): Exception, query is invalid. Aborting.');
            return false;
        } else {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getIds(): query parsed successfully.');
            
        }
        
        if (!isset($prototypeName))
            $prototypeName = 'modResource';
        
        $prototype = $modx->newObject($prototypeName);
        $sqlString = '';
        
        /* 
        If we could not create the prototype object we have recieved an invalid class key in $prototypeName.    
        This in turn mean that we can not get the default class properties for the prototype which a deal
        breaker, so we return false.
        */
        if (!$prototype) {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->getIds(): Exception, Could not create prototype object. Aborting.');
            return false;
        }
        
        /*
        The $className parameter represents the Mirage Class (modTemplate) to get extended properties (TV's) from. 
        $className is required, if its not there we return false.	
        */
        if (!$className) {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->getIds(): Exception, Required param className was empty. Aborting.');
            return false;
        }
        
        if (!class_exists($className)) {
            //if(self::$_debugmode) $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->getIds(): Exception, No Class named "'.$className.'"  was found, did you include your class lib? Aborting.');
            //return false;
        }
        
        
        // Turn the prototype to an array to make property checking easy.
        $prototype = $prototype->toArray('');
        
        if (is_array($prototype)) {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getIds(): Created prototype array which will be used for property checks.');
            $prototype = array(
                'classkey' => $prototypeName,
                $prototype
            );
        }
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getIds(): Calling constructQuery().');
        
        // Should we specify fields to be returned?
        if (!isset($fields)) {
            $sqlString = self::constructQuery($query, $prototype);
        } else {
            $sqlString = self::constructQuery($query, $prototype, $fields);
            
        }
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getIds(): constructQuery() returned "' . $sqlString . '".');
        
        if ($sqlString) {
            
            $objects = $modx->query($sqlString)->fetchAll();
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getIds(): Result from $modx->query() "' . json_encode($objects) . '".');
            
            foreach ($objects as $obj) {
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getIds(): Getting id from "' . $className . '".');
                $resultList[] = $obj;
            }
            
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->getIds(): sqlString was empty. Aborting.');
            return false;
        }
        
        if (is_array($objects)) {
            return $resultList;
        } else {
            return false;
        }
        
    }
    
    /**
     * Returns an array of Simplx_Mirage_Object instances each wrapping its corresponding
     * modResource object. 
     *
     * @static
     * @param $className Name of the Simplx_Mirage_Class/modTemplate object.
     * @param $query XPDO query style array constraining results.	
     * @return array|false 
     */
    public static function getObjects($className, $query = array(), $prototypeName = 'modResource', $fields = null)
    {
        global $modx;
        $resultList      = array();
        $classExists     = false;
        $class           = '';
        $tmpQuery        = '';
        $prefix          = '';
        $separator       = '';
        $classProperties = array();
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getObjects(): Params $className = "' . $className . '", $query = "' . json_encode($query) . '", $prototypeName = "' . $prototypeName . '".');
        
        
        
        // Get the Simplx_Mirage_Class which wraps the modTemplate.
        $class = Simplx_Mirage::getClass($className);
        
        // Without a Class we cant go any further.
        if (!$class) {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->getObjects(): Exception, Could not create Simplx_Mirage_Class object. Aborting.');
            return false;
        }
        
        // Get a local ref of the properties array. This is good practice when it will be used
        // repeatedly, in a loop for example.
        $classProperties =& $class->_properties;
        
        // Make sure that we specify class id (template id) in the query.
        if (!array_key_exists('template', $query)) {
            $query['template:='] = $class->_id;
        }
        
        // Lets prepare the query statement by normalizing it to contain field, operator and constraint
        $query = self::prepareQueryStatement($query);
        
        if ($query === false) {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->getObjects(): Exception, query is invalid. Aborting.');
            return false;
        } else {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getObjects(): query parsed successfully.');
            
        }
        
        $prototype = $modx->newObject($prototypeName);
        $sqlString = '';
        
        /* 
        If we could not create the prototype object we have recieved an invalid class key in $prototypeName.    
        This in turn mean that we can not get the default class properties for the prototype which a deal
        breaker, so we return false.
        */
        if (!$prototype) {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->getObjects(): Exception, Could not create prototype object. Aborting.');
            return false;
        }
        
        /*
        The $className parameter represents the Mirage Class (modTemplate) to get extended properties (TV's) from. 
        $className is required, if its not there we return false.	
        */
        if (!$className) {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->getObjects(): Exception, Required param className was empty. Aborting.');
            return false;
        }
        
        if (!class_exists($className)) {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->getObjects(): No Class named "' . $className . '"  was found, using the generic Simplx_Mirage_Object class.');
            $classExists = false;
        } else {
            $classExists = true;
        }
        
        
        // Turn the prototype to an array to make property checking easy.
        $prototype = $prototype->toArray('');
        
        if (is_array($prototype)) {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getObjects(): Created prototype array which will be used for property checks.');
            $prototype = array(
                'classkey' => $prototypeName,
                $prototype
            );
        }
        
        $prefix    = ($class->_tvPrefix != '') ? $class->_tvPrefix : $class->_classTypeName;
        $separator = $class->_tvPrefixSeparator;
        $prefix    = $prefix . $separator;
        
        /* Lets prefix the query fields if necessary */
        if ($class->_prefixTvs) {
            
            foreach ($query as &$constr) {
                if (!array_key_exists($constr['field'], $prototype[0])) {
                    
                    $constr['field'] = ($prefix . $constr['field']);
                    
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getObjects(): Property "' . $constr['field'] . '" was prefixed.');
                    
                }
            }
        }
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getObjects(): Query after prefixing "' . json_encode($query) . '".');
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getObjects(): Calling constructQuery().');
        
        // Should we specify fields to be returned?
        //if(!isset($fields)){
        $sqlString = self::constructQuery($query, $prototype, array(
            'c.*'
        ));
        //}else{
        //  $sqlString = self::constructQuery($query,$prototype,$fields);
        //}
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getObjects(): constructQuery() returned "' . $sqlString . '".');
        
        if ($sqlString) {
            
            $criteria = new xPDOCriteria($modx, $sqlString);
            
            $objects = $modx->getCollection($prototypeName, $criteria);
            
            foreach ($objects as &$obj) {
                if ($classExists) {
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getObjects(): Creating Object "' . $obj->toJSON() . '" of Class "' . $className . '".');
                    $resultList[] = new $className($obj->get('id'), $obj);
                } else {
                    if (self::$_debugmode)
                        $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->getObjects(): Creating Object "' . $obj->toJSON() . '" of Class "' . $className . ' using Simplx_Mirage_Object.".');
                    $resultList[] = new Simplx_Mirage_Object($obj->get('id'), $obj, $className);
                }
            }
            
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->getObjects(): sqlString was empty. Aborting.');
            return false;
        }
        
        if (is_array($objects)) {
            return $resultList;
        } else {
            return false;
        }
        
        
    }
    /**
     * Prepares the XPDO style query by splitting it into an array.
     *
     * @static
     * @param $query The XPDO style query array. 
     * @param $prototype The modResource object which is to be used when checking which fields "native" and which are TV's.
     SS  * @return string|false
     */
    private static function prepareQueryStatement($query)
    {
        $fieldArray  = array();
        $operator    = '';
        $parsedQuery = array();
        global $modx;
        
        /*
        
        Lets start looping through the query array to build the constraint section.	
        The query array has the following structure, array("field:operator"=>"constraint")
        */
        
        if (is_array($query)) {
            
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->constructQuery(): Looping through the query array().');
            
            foreach ($query as $field => $constr) {
                
                /*
                The query is likely to contain operators such as this, "field:>=". We need to explode apart such query strings.
                */
                $fieldArray = explode(':', $field);
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->prepareQueryStatement(): $fieldArray = "' . json_encode($fieldArray) . '".');
                
                
                if (count($fieldArray) > 1) {
                    $operator = $fieldArray[1];
                } else {
                    $operator = '=';
                    $constr   = $fieldArray[1];
                }
                
                $parsedQuery[] = array(
                    'field' => $fieldArray[0],
                    'operator' => $operator,
                    'constraint' => $constr
                );
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->prepareQueryStatement(): $fieldArray = "' . json_encode($fieldArray) . '".');
                
            }
            
            return $parsedQuery;
            
        } else {
            return false;
            //Not a valid query format 
        }
        
    }
    
    
    /**
     * Builds a SQL query from a XPDO query syntax. This is a central part of the Mirage concept as
     * its lets the user constrain query results using template variables.
     *
     * @static
     * @param $query The XPDO style query array. 
     * @param $prototype The modResource object which is to be used when checking which fields "native" and which are TV's.
     * @return string|false
     */
    private static function constructQuery($query, $prototype, $fields = array())
    {
        global $modx;
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->constructQuery(): Params $query = "' . json_encode($query) . '", $prototype = "' . json_encode($prototype) . '".');
        
        $whereClause       = '';
        $count             = 0;
        $i                 = 0;
        $parsedQuery       = array();
        $operator          = '';
        $viewName          = '';
        $tableName         = '';
        $defaultProperties = null;
        $useJoin           = false;
        
        $fields[] = 'c.id';
        
        $fields = implode($fields, ',');
        
        if (is_array($prototype)) {
            $defaultProperties = $prototype[0];
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->constructQuery(): Set $defaultProperties to "' . json_encode($defaultProperties) . '".');
        } else {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_ERROR, 'Simplx_Mirage->constructQuery(): Exception, the prototype array is invalid. Aborting.');
            return false;
        }
        
        $viewName = $modx->getOption('simplx.mirage.object.viewname');
        
        if (!$viewName) {
            $viewName = 'view_mirage_object_properties';
        }
        
        $tableName = $modx->getTableName($prototype['classkey']);
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->constructQuery(): Set $tableName to "' . $tableName . '", and $viewName to "' . $viewName . '".');
        
        /*
        Ok we can now build the actual SQL statement
        */
        
        $count = 0;
        
        foreach ($query as $constr) {
            
            /*
            If the field is in the prototype array, its part of the default properties for the mod* class.
            If not, we handle them as tv's.
            */
            if (!array_key_exists($constr['field'], $defaultProperties)) {
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->constructQuery(): The field "' . $constr['field'] . '" is not a modResource field.');
                
                
                // Set $useJoin to true for later use.
                $useJoin = true;
                
                if ($count > 0) {
                    $whereClause .= ' AND (';
                } else {
                    $whereClause .= ' (';
                }
                
                /*
                First we need to constrain the result on tv name.
                */
                $whereClause .= 'p.`modtemplatevar.name` = "' . $constr['field'] . '" ';
                
                $whereClause .= ' AND ';
                
                /*
                Then we add the actual value constrain.
                */
                $whereClause .= 'p.`modtemplatevarresource.value` ';
                
                
                // See if we have an operator. If not, we default to equals ("="). 
                if ($constr['operator']) {
                    $whereClause .= (' ' . $constr['operator'] . ' ');
                } else {
                    $whereClause .= ' = ';
                }
                
                $constraint = $constr['constraint'];
                
                if (!is_numeric($constraint)) {
                    $constraint = ('"' . $constraint . '"');
                }
                
                $whereClause .= $constraint;
                
                $whereClause .= ')';
                
                
                
            } else {
                
                if (self::$_debugmode)
                    $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->constructQuery(): The field "' . $constr['field'] . '" is a modResource field.');
                
                if ($count > 0) {
                    $whereClause .= ' AND ';
                    
                }
                
                $whereClause .= ' c.`' . $constr['field'] . '` ';
                
                
                // See if we have an operator. If not, we default to equals ("="). 
                if ($constr['operator']) {
                    $whereClause .= (' ' . $constr['operator'] . ' ');
                } else {
                    $whereClause .= ' = ';
                }
                
                $constraint = $constr['constraint'];
                
                // Make sure we quote any string.
                if (!is_numeric($constraint)) {
                    $constraint = ('"' . $constraint . '"');
                }
                
                $whereClause .= $constraint;
                
                
            }
            $count = $count + 1;
        }
        // This should be it. Lets build and return the query string.
        
        // Build the initial part of the SQL string which joins the table name for the prototype (defaults to 'modResource') and the view.
        if ($useJoin) {
            if (self::$_debugmode)
                $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->constructQuery(): Got more than just the id field so we use a join.');
            $sqlString = 'SELECT DISTINCT ' . $fields . ' FROM ' . $tableName . ' AS c LEFT JOIN `' . $viewName . '` AS p ON c.id = p.`modresource.id` WHERE ';
        } else {
            $sqlString = 'SELECT ' . $fields . ' FROM ' . $tableName . ' AS c WHERE ';
        }
        
        if (self::$_debugmode)
            $modx->log(modX::LOG_LEVEL_DEBUG, 'Simplx_Mirage->constructQuery(): The sqlString is set to "' . $sqlString . '".');
        
        $sqlString .= ($whereClause . ';');
        
        return $sqlString;
        
        
    }
    
}


