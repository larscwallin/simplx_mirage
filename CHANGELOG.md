##UPDATE 140309

###NEW FEATURES

Instantiating a new Class that inherits from Simplex_Mirage_Object without any parameters now automatically
creates a new modResource with a modTemplate corresponding to the Class name.

```
class Product extends Simplx_Mirage_Object {}

$camera = new Product(); 
$camera->name = 'Canon EOS 333';
$camera->save();

```
The code above will create a new modResource which uses a modTemplate intance named "Product". If no such 
template is found, Mirage will report an error.

Another added feature is that all Classes which inherit from Simplex_Mirage_Object dispatches the onDocFormSave event on save.



##UPDATE 120302 21:41

###NEW FEATURES

Added a Simplx.Mirage.Class PropertySet which all modTemplate objects used by Mirage should
implement. The properties are the same as the public meta properties of the Simplx_Mirage_Class class
and are used as initial config for the Mirage class if not overridden by a custom instance.

##UPDATE 120228 23:45

Massive bug fixes in addComposite and addAggregate. 

##UPDATE 120228 15:00

Bug fixes

##UPDATE 120226 15:30

###NEW FEATURES

Added a Snippet which queries Mirage and returns json representations of Simplx_Mirage_Object's. 
The Snippet also checks whether the setup snippet has run, and if it has'nt it does :)  

Fixed bugs in the query engine.

Did general spring cleaning in the code :)

##UPDATE 120225 22:30

###NEW FEATURES

I made it possible again to use Mirage without explicitly creating php classes as wrappers which extend
Simplx_Mirage_Object. I still recommend it though.

You can now do this:
```
$myClass = new Simplx_Mirage_Class('myClassName');
$objects = Simplx_Mirage_Class->getObjects(array(
  'anyField:='=>'foo'
));

foreach($objects as $obj){
  print $obj->id;
  print $obj->myVeryOwnSuperCoolFieldWhichIsReallyAtemplateVar;
}

```

##UPDATE 120221 11:25

Adding DocBlock annotations

##UPDATE 120216 12:55

Fixed so that Simplx_Mirage_Object->toArray/toJSON always include the modResource instance id.

##UPDATE 120216 12:45

###NEW FEATURES

* Implemented proper behaviour when overriding Simplx_Mirage_Object property defaults.

##UPDATE 120215 13:12

Adding DocBlock annotations

##UPDATE 120120 13:12

###NEW FEATURES

* Simplx_Mirage_Class (a modTemplate remember) is now configured by default by using the templates 
  default PropertySet. 
  The Property Set can be found in the "simplx.mirage.class.json" below. Just import to use. 
* Simplx_Mirage_Object now inherits all config from its Mirage Class. See point above.

###BUG FIXES

* Fixed a misstake in the addAggregate method. 

##UPDATE 120119 12:50

###NEW FEATURES

* Simplx_Mirage_Class->newObject() now also creates modResource containers for all associations declared in the 
  _aggregates, _composites and _associations arrays. These arrays will later be populated by modTemplate's 
 Property Set.

##UPDATE 120119 12:10

###NEW FEATURES

* Simplx_Mirage_Class now has a newObject(array defaults, [modResource prototype]) method which creates a new instance of modResource which uses
  the correct modTemplate for that particular Mirage Class.

* Simplx_Mirage_Class has a new _defaultObjectLocation property which tells Mirage where in the structure to save
  the new modResource objects of a particular Mirage Class by default. Can be overridden in the newObject() method.

UPDATE 120104 14:50

###API CHANGES

* Added Simplx_Mirage_Object->renderAspect($aspect) method. 
  $aspect is a Snippet name which in turn gets called with all Object state serialized to an array as only param.
  Very flexible way to render views. Also remember that there is a identical method in Simplx_Mirage_Class.  


##UPDATE 120103 17:35

###API CHANGES

* Added a Simplx_Mirage_Object->_parent property to ensure that every object always have a reference of their parent.
  The _parent property display the correct parent from Mirage's perspective. This means that if a Composite object is   
  located in a folder beneath the parent _parent will ignore this "symbolic" folder and point to what Mirage sees as 
  the parent object.

##UPDATE 120102 10:19

###API CHANGES
* Added an optional "$useClassNameWrap" parameter to Simplx_Mirage_Object->toJSON() and 
  Simplx_Mirage_Object->toArray(). This tells Mirage if to wrap the serialized object like this:

  {"myObjectTypeName":{"myProperty":"whatever"}}

  or, without class name:

  {"myProperty":"whatever"}

  Default "$useClassNameWrap" value is false.


##UPDATE 111227 13:26

###NEW FEATURES
* Added rudemental MODx to JSON schema type conversion.

##UPDATE 111227 12:55

###NEW FEATURES
* Added JSON-SCHEMA compliant toJSON output from Simplx_Mirage_Class->toJSON()
* Added the possibility to add JSON-SCHEMA validations etc to the Simplx_Mirage_Class using the 
  _propertyValidationRules array. 
* Lots and lots more



##UPDATE 111220 15:00

###BUG FIXES
Fixed issue relating to TV Prefixing.
Fixed issue with Simplx_Mirage_Object->save(). Now respects _persistOnAssign setting.


##UPDATE 111217 23:40

###NEW FEATURES

* Finished implementing the getAggregates() and getComposites() methods.
* Implemented the option to store Aggregates and Composites in sub folders. Folders are named 
  the same as the class they contain by default.
* Started to put more logic in to the Simplx_Mirage_Class class.
* Ditched the usage of static members, except for the debugmode flag which is still static.
* Optimized modResource/view query function so that joins with the view are only used when really needed.

##UPDATE 111214 14:15

###NEW FEATURES

Simplx_Mirage_Object now implements two new cool methods getAggregates() and getComposites().

* Simplx_Mirage_Object->getAggregates($className,$query)
  This method gets sym-linked modResources which are children of the current modResource used as prototype 
  by Simplx_Mirage_Object.
  The Resources must also use the modTemplate object specified in the $className argument.
  It automatically translates the modSymLink to the linked modResources, in other words you get the
  actual resource back in the result list.

  The query argument is just a regular xpdo style constraint array.

* Simplx_Mirage_Object->getComposites($className,$query)
  This method gets all modResources which are children of the current modResource used as prototype 
  by Simplx_Mirage_Object.
  The Resources must also use the modTemplate object specified in the $className argument.
  It automatically translates the modSymLink to the linked modResources, in other words you get the
  actual resource back in the result list.

  The query argument is, again, just a regular xpdo style constraint array.
 
##UPDATE 111212 15:50

###NEW FEATURES

Implemented the first version of fromArray(). This is an important step forward as it will make it 
possible, and very simple, to take a json/array representation of the Resource, including TV's, and 
simply load them and save.

*Important*
Its not tested yet so hold your horses a bit.

###UPDATES

Updated the save() method for the Mirage object a bit.
Added more debug output.
Probably more that i forgot ;p 


##UPDATE 111125 17:10

###NEW FEATURES

Finally! I implemented the first version of Simplx_Mirage_Class::getObjects()!
This lets you use XPDO query style to select objects. The getObjects method takes care of 
resolving which query parameters that are modResource properties and which are TV's.

Example:
```
$objectList = Simplx_Mirage_Class::getObjects('Aircraft',array(
  'registration_number:>'=>10,
  'template:='=>3
));  
```

Cool thing is that this works just fine for getting modResources in general :) No more akward special treatment 
of TV's!

* INSTALL *

Copy paste and run the "simplx.mirage.setup" Snippet to create the necessary MySQL view.
Thats it :)


##UPDATE 111122 15:11

###BUG FIXES

Forgot to add prefix separator.

##UPDATE 111122 15:00

###NEW FEATURES

- Added prefix support for TV's. This means that a Class can use its Class name to distinguish it's own TV's.
If prefixes are used the default prefix would look like this "Aircraft_registration_number".

Prefixing is meant to distinguish TV's from each other, like namespaces. They are not used for
getting or setting of properties. So retrieving a property would look like this "$aircraft->registration_number"
even with prefixing in use.

###BUG FIXES
