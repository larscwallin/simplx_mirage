<?php
require_once($modx->getOption('core_path').'components/simplx/mirage/simplx.mirage.php');

$result = false;

if($debugmode){
  $modx->setLogLevel(modX::LOG_LEVEL_DEBUG);
  Simplx_Mirage::$_debugmode = true;
  Simplx_Mirage_Class::$_debugmode = true;
  Simplx_Mirage_Object::$_debugmode = true;
  
}

$modx->log(modX::LOG_LEVEL_DEBUG, '');		 
$modx->log(modX::LOG_LEVEL_DEBUG, '-----------------------------------------------------------------------------------------------------');		 
$modx->log(modX::LOG_LEVEL_DEBUG, '');		 
$modx->log(modX::LOG_LEVEL_DEBUG, 'Running Snippet simplx.mirage : ');		 
$modx->log(modX::LOG_LEVEL_DEBUG, '');		 
/*
  Check if the Simplx Mirage setup Snippet has run. Otherwise do so.
  The "simplx.mirage.setup.hasrun" flag is a System Setting which is
  is only present if the setup has run.
*/
if(!$modx->getOption('simplx.mirage.setup.hasrun')){

  $modx->log(modX::LOG_LEVEL_DEBUG, 'Snippet simplx.mirage : Setting "simplx.mirage.setup.hasrun" was either false or did not exist. Running Snippet "simplx.mirage.setup".');		 

  $result = $modx->runSnippet('simplx.mirage.setup');
  
  if($result){
       $modx->log(modX::LOG_LEVEL_DEBUG, 'Snippet simplx.mirage : Running "simplx.mirage.setup" returned true.');		 
  
  }else{
       $modx->log(modX::LOG_LEVEL_ERROR, 'Snippet simplx.mirage : Running "simplx.mirage.setup" returned false. Aborting.');		 
	return false;  
  }

}else{

}

switch($get){
  
  case 'objects':
      
      $modx->log(modX::LOG_LEVEL_DEBUG, 'Snippet "simplx.mirage", case "objects", class "'.$class.'".');	     
      
      if(!$class){
	$modx->log(modX::LOG_LEVEL_ERROR, 'Snippet "simplx.mirage" Exception: Argument class "'.$class.'" is missing.');	     
	return false;
      }
      
      if(!$query){
	$query = array();
      }else{
	$query = json_decode($query,true);  
	if(!is_array($query)){
	  $modx->log(modX::LOG_LEVEL_ERROR, 'Snippet "simplx.mirage", case "objects": Malformed JSON query "'.$query.'".');		       
	  $query = array();	
	}
      }
            
      $mirageClass = new Simplx_Mirage_Class($class);
      
      $list = $mirageClass->getObjects($query);      
      
      $result = array();
      
      foreach($list as $obj){
	$result[] = $obj->toArray();	
      }
    
    break;

  case 'aggregates':
      
      $modx->log(modX::LOG_LEVEL_DEBUG, 'Snippet "simplx.mirage", case "aggregates", class "'.$class.'", oid "'.$oid.'".');	     
      
      if(!$class){
	$modx->log(modX::LOG_LEVEL_ERROR, 'Snippet "simplx.mirage" Exception: Argument class is missing.');	     
	return false;
      }

      if(!$oid){
	$modx->log(modX::LOG_LEVEL_ERROR, 'Snippet "simplx.mirage" Exception: Argument oid is missing.');	     
	return false;
      }
      
      if(!$query){
	$query = array();
      }else{
	$query = json_decode($query,true);  
	if(!is_array($query)){
	  $modx->log(modX::LOG_LEVEL_ERROR, 'Snippet "simplx.mirage", case "aggregates": Malformed JSON query "'.$query.'".');		       
	  $query = array();	
	}
      }
            
      $mirageObject = new Simplx_Mirage_Object($oid);
      
      $list = $mirageObject->getAggregates($class,$query);      
       
      $result = array();
      
      foreach($list as $obj){
	$result[] = $obj->toArray();	
      }
    
    break;  

  case 'composites':
      
      $modx->log(modX::LOG_LEVEL_DEBUG, 'Snippet "simplx.mirage", case "composites", class "'.$class.'", oid "'.$oid.'".');	     
      
      if(!$class){
	$modx->log(modX::LOG_LEVEL_ERROR, 'Snippet "simplx.mirage" Exception: Argument class is missing.');	     
	return false;
      }

      if(!$oid){
	$modx->log(modX::LOG_LEVEL_ERROR, 'Snippet "simplx.mirage" Exception: Argument oid is missing.');	     
	return false;
      }
      
      if(!$query){
	$query = array();
      }else{
	$query = json_decode($query,true);  
	if(!is_array($query)){
	  $modx->log(modX::LOG_LEVEL_ERROR, 'Snippet "simplx.mirage", case "composites": Malformed JSON query "'.$query.'".');		       
	  $query = array();	
	}
      }
            
      $mirageObject = new Simplx_Mirage_Object($oid);
      
      $list = $mirageObject->getComposites($class,$query);      
      
      $result = array();
      
      foreach($list as $obj){
	$result[] = $obj->toArray();	
      }
    
    break;  
  
  case 'schema':

    $modx->log(modX::LOG_LEVEL_DEBUG, 'Snippet "simplx.mirage", case "schema", class "'.$class.'".');	     

    if(!$class){
      $modx->log(modX::LOG_LEVEL_ERROR, 'Snippet "simplx.mirage" Exception: Argument "class" is missing.');	           
      return false;
    }
    
    $mirageClass = new Simplx_Mirage_Class($class);
    $result = $mirageClass->toJSON();
    
    break;
  
  case 'object':
    if(!$oid){
      $modx->log(modX::LOG_LEVEL_ERROR, 'Snippet "simplx.mirage" Exception: Argument "oid" is missing.');	           
      return false;
    }else{
    
    }
    
    //$mirageObject = new Simplx_Mirage_Object($oid); 
    $mirageObject = Simplx_Mirage::getObject($oid);
    $result = $mirageObject->toJSON();
    break;
  
  default:
    $modx->log(modX::LOG_LEVEL_DEBUG, 'Snippet "simplx.mirage", case "default", class "'.$class.'".');	     
    $result = '';

}

$modx->log(modX::LOG_LEVEL_DEBUG, '');		 
$modx->log(modX::LOG_LEVEL_DEBUG, '-----------------------------------------------------------------------------------------------------');		 
$modx->log(modX::LOG_LEVEL_DEBUG, '');		 

// Only return result if its valid.
if($result){
  return json_encode($result);
}else{
  return;
}
