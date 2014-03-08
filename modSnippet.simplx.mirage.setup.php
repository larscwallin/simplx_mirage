<?php
/*******************************************************************************************

Simplx Mirage Setup Snippet

This Snippet sets up, most importantly, the mysql view needed to query template vars
with efficiance.

********************************************************************************************/

$objectPropertiesViewName = isset($objectPropertiesViewName) ? $objectPropertiesViewName : 'view_mirage_object_properties';
$tablePrefix = ('`'.$modx->getOption(xPDO::OPT_TABLE_PREFIX).'`');
$modTemplateVarTemplateTable = $modx->getTableName('modTemplateVarTemplate');
$modTemplateVarTable = $modx->getTableName('modTemplateVar');
$modTemplateVarResourceTable = $modx->getTableName('modTemplateVarResource');

$modx->log(modX::LOG_LEVEL_DEBUG, 'Snippet simplx.mirage.setup: $objectPropertiesViewName = '.$objectPropertiesViewName);
$modx->log(modX::LOG_LEVEL_DEBUG, 'Snippet simplx.mirage.setup: $tablePrefix = '.$tablePrefix);
$modx->log(modX::LOG_LEVEL_DEBUG, 'Snippet simplx.mirage.setup: $modTemplateVarTemplateTable  = '.$modTemplateVarTemplateTable );
$modx->log(modX::LOG_LEVEL_DEBUG, 'Snippet simplx.mirage.setup: $modTemplateVarTable = '.$modTemplateVarTable);
$modx->log(modX::LOG_LEVEL_DEBUG, 'Snippet simplx.mirage.setup: $modTemplateVarResourceTable  = '.$modTemplateVarResourceTable);$modx->log(modX::LOG_LEVEL_DEBUG, 'Snippet simplx_mirage_setup: $modTemplateVarResourceTable  = '.$modTemplateVarResourceTable);

$result = false;
$handle;
$contents = '';
$success = false;
$properties = array();


/*******************************************************************************************

* SYSTEM SETUP SECTION

********************************************************************************************/


/*
* Create a new Namespace and get rid of the one that PackMan created.
*/


$simplx_mirage_ns = $modx->getObject('modNamespace',array(
	'name' => 'simplxmirage'
));

if($simplx_mirage_ns){
  $result = $simplx_mirage_ns->remove();
  
  if($result){
    $modx->log(modX::LOG_LEVEL_ERROR, 'simplx.mirage.setup() Exception: Could not delete Namespace "simplxmirage". Please do this manually.');
  }
}

unset($simplx_mirage_ns);

$simplx_mirage_ns = $modx->newObject('modNamespace');

if($simplx_mirage_ns){
  $simplx_mirage_ns->set('name','simplx.mirage');   
  $simplx_mirage_ns->set('path',($modx->getOption('assets_path').'components/simplx/mirage/'));     
  $result = $simplx_mirage_ns->save();
  
  if($result === false){
    $modx->log(modX::LOG_LEVEL_ERROR, 'simplx.mygit.setup() Exception: Could create Namespace "simplx.mirage". Please do this manually to keep things in good order.');
  }
}

unset($simplx_mirage_cat);


/*
* Create a Settings
*/


$simplx_mirage_setting = $modx->getObject('modSystemSetting',array(
'key' => 'simplx.mirage.setup.hasrun'
));

if($simplx_mirage_setting !== 1){
  unset($simplx_mirage_setting);
  
  $simplx_mirage_setting = $modx->newObject('modSystemSetting');
  $simplx_mirage_setting->set('key','simplx.mirage.setup.hasrun');
  $simplx_mirage_setting->set('value','0');
  $simplx_mirage_setting->set('namespace','simplx.mirage');
   
  $result = $simplx_mirage_setting->save();
  
  if(!$result){  
    $modx->log(modX::LOG_LEVEL_ERROR, 'simplx.mirage.setup() Exception: Could not create System Setting "simplx.mygit.username". Please do this manually.');
  }
  
  unset($simplx_mirage_setting);
  
  $simplx_mirage_setting = $modx->newObject('modSystemSetting');
  $simplx_mirage_setting->set('key','simplx.mirage.object.viewname');
  $simplx_mirage_setting->set('value',$objectPropertiesViewName);
  $simplx_mirage_setting->set('namespace','simplx.mirage');
   
  $result = $simplx_mirage_setting->save();
  
  if(!$result){  
    $modx->log(modX::LOG_LEVEL_ERROR, 'simplx.mirage.setup() Exception: Could not create System Setting "simplx.mirage.object.viewname". This is a critical Setting. Please do this manually.');
  }

}else{
  $modx->log(modX::LOG_LEVEL_ERROR, 'simplx.mirage.setup() Exception: "simplx.mirage.setup.hasrun" exists so executing setup is not necessary. Aborting.');
}

unset($simplx_mygit_setting);


/*
*  
* Set up the simplx.mirage.class Property Set
*
*/


$simplx_mirage_class_ps = $modx->getObject('modPropertySet',array(
      'name' => 'simplx.mirage.class'
));

// If there is no 'simplx.mirage.class' yet, lets create it.
if(!$simplx_mirage_class_ps){

  $filename = $modx->getOption('core_path')."components/simplx/mirage/simplx_mirage_class_properties.js";
  
  if (!file_exists($file_name))  {	  
    $handle = fopen($filename, "rb");
    $contents = fread($handle, filesize($filename));
    fclose($handle);  
  
  
    // Create an array with all properties.
    $properties = json_decode($contents,true);
    
    if(is_array($properties)){
      $simplx_mirage_class_ps = $modx->newObject('modPropertySet');
      $simplx_mirage_class_ps->set('name','simplx.mirage.class');
      $simplx_mirage_class_ps->set('description','Property Set which must be implemented by all Templates wanting to use with Mirage.');
      
      $simplx_mirage_class_ps->setProperties($properties);
      
      $success = $simplx_mirage_class_ps->save();
      
      if(!$success){
	// Could not set the properties.	
	$modx->log(modX::LOG_LEVEL_ERROR, 'simplx.mirage.setup() Exception: Could not create Property Set "simplx.mirage.class". Please do this manually.');
    
      }else{
	     
      } 
  
    }else{      
	  $modx->log(modX::LOG_LEVEL_ERROR, 'simplx.mirage.setup() Exception: Could not decode the "simplx.mirage.class" Property Set. Please import the Property Set manually.');       
    }	
  
  
  }else{
	$modx->log(modX::LOG_LEVEL_ERROR, 'simplx.mirage.setup() Exception: Could not create Property Set "simplx.mirage.class". Please do this manually.');
  }
  
}else{      
  $modx->log(modX::LOG_LEVEL_DEBUG, 'simplx.mirage.setup(): The "simplx.mirage.class" Property Set is already installed.');       
}


/*******************************************************************************************

* SQL VIEW SETUP SECTION

********************************************************************************************/


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
	`tpl`.`tmplvarid` = `var`.`id`;
';

$modx->log(modX::LOG_LEVEL_DEBUG, 'Snippet simplx_mirage_setup: $createViewQuery = '.$createViewQuery);

$result = $modx->exec($createViewQuery);

if($result === false){
	$modx->log(modX::LOG_LEVEL_ERROR, 'Snippet simplx_mirage_setup: CREATE VIEW returned false. Aborting.');	    
	return false;
}else{
  
  // All's well that ends well. We can now set the "simplx.mirage.setup.hasrun" setting to 1. 
    
  $simplx_mirage_setting = $modx->getObject('modSystemSetting',array(
  'key' => 'simplx.mirage.setup.hasrun'
  ));
  
  $simplx_mirage_setting->set('value','1');     
  $result = $simplx_mirage_setting->save();
  
  // And return true.
  return true;
}



/*******************************************************************************************
*
*
********************************************************************************************/
