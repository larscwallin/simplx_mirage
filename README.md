#Simplx Mirage

###Yes. You are seeing things 

_"In contrast to a hallucination, a mirage is a real optical phenomenon which can be captured on camera. ... What the image appears to represent, however, is determined by the interpretive faculties of the human mind."_ - Wikipedia

Simplx Mirage is a simple Class library that helps you see and handle your MODX Templates and Resources as PHP Classes and Objects. Mirage is my 'optical illusion' to help you build web applications using only the very basic features of MODX.

###Using the awesomeness of MODX 

Since day one with our dear CMS I have seen Templates as modx version of data types, or Classes.

This is not only a working Metaphore but pretty much a practical reality as Templates in MODX do represent a logical entity with named fields. When a Resource chooses a Template it inherits these fields, extending its own set of fields. The Resource ends up representing the Template in much the same manner as an Object represents an instance of a Class.

###The MODX API twist

The MODX API is plenty great for dealing with all aspects of the framework. This goes for the modResource class as well. The problem for me however, with my line of thinking, has been that the Template Variables that extend the Templates (and thus also the Resources) are very generic and volatile in their nature. By this l refer to the fact that one TV can be assigned to multiple Templates, and can also be removed from a Template at any time.
The whole thing is made even more problematic by the fact that a Resource can change template at anytime and loose its set of attributes, and thus also its signature. Summing up, we have all the stuff in MODX to turn it in to the Rapid Application Studio for the web, but with a slightly incompatible model.

Now lets put on our 3D goggles and see how Mirage lets us fix this!

###Conjuring up the illusion 

Ok. First of all I have made a Template which is to represent a product on my site. This Template has a bunch of Template Variables such as category, price and product image.

I have also made a container Resource called Product in the tree. This in turn is placed in a container called Objects. Like this,

    Objects 
    - Product 

Remember, when using Mirage, the Product Template with its Template Variables is considered to be a type declaration, or Class.

To create an Object of type Product, we now add a new Resource in our Product container and make sure to set the Template to Product. Lets say the new Resource is called 'Camera' gets the id 30.

    Objects 
    - Product 
    -- Camera 

Create a Snippet (for testing purposes) .

Be sure to include the simplx.mirage.php file.
Declare a php class like this:

`class Product extends Simplx_Mirage_Object {}`

We can now get the Product Object like this,

`$camera = new Product(30); `

Easy right? You can now thanks to the inner workings of Mirage use this object in plain old php fashion:

```
$camera->price = 45; 
$camera->category = 'Cameras'; 
$camera->save(); 
```

And you can use the Simplx_Mirage class to do this:

```
$ProductClass = Simplx_Mirage::getClass('Product'); 
$cheapCameras = $ProductClass->getObjects(array( 'price:<'=>50, 'category:='=>'Cameras' )); 
```

##Introducing Simplx Mirage Part II

Previous post showed the very minimum features to make Mirage useful. Now lets take a look at what else is in the box.


###A word on Prototyping
Simplx Mirage implements a prototypical style of inheritance. This means that instead of deriving attributes and functions using extending of php classes, you simply build on an existing php object. Note that i wrote object, not class.

In prototypical inheritance you don't have the concept of classes. Objects, with state and all, are simply extended at runtime. Its generally done by setting a reference to a prototype, which is then used as a starting point for building the new entity.
A neat and handy feature in prototype orientation is the possibility to add properties and methods at any time during excecution. This is used heavily in the most famous prototype based language today, which is of course Javascript.

Simplx Mirage does prototyping in a very straight forward way. Much like Javascript, you extend the MODX core model by simply creating a new class and setting its _prototype property to an instance of a modResource class. You can then add and override the prototypes properties and public methods just as if they where extended in a normal inheritance style.

###How stuff works - in short
Many things can be said about the highs and lows of php. One thing is clear though, its a darn flexible language! To make Mirage tick I have used many of php's pragmatic features such as Magic Methods, Introspection, Dynamic Class invocation and more.

The Mirage package consists of three Classes at the moment,
Simplx_Mirage - a Factory/Repository style class with static utility properties and methods. This class also handles caching and overall settings management.
Simplx_Mirage _Class - a class which encapsulates a modTemplate object using the Prototype concept mentioned previously. Its members gives a straight forward way access what Mirage sees as Class definition: the modTemplate and its modTemplateVar associations.
Simplx_Mirage_Object - a class that encapsulates a modResource object and lets you override and extend it thanks to php goodies like Introspection and Magic Methods. Every Mirage Object gets a reference to its corresponding Simplx_Mirage_Class as it is created.
With these three classes we have everything we need to play dress-up with MODX.

Conventions to keep it simple
In MODX, as you might know, Template Variables have a one-to-many relationship with Templates. This is not a problem for Mirage really, but a one-to-one association is more in tune with the overall Class/Object metaphore we use. To get this working Mirage uses namespace prefixes by default. The default convention used here is to simply take the name of the class (modTemplate) as prefix. A Template Variable for the Product class could look like this:

`Product_category`

which would resolve to:

`$myProduct->category`

This general convention of using class name is used through out Mirage.
A really nice feature is the automagic matching of class/template matching using class name.

If you do the following:

`class Product extends Simplx_Mirage_Object {}`


Mirage very gracefully helps you at by assuming that the class 'Product' represents a modResource which uses a modTemplate named 'Product', and that its associated Template Variables are prefixed with 'Product_'.


Overriding and extending modResource
The possibilities to override properties and methods in the modResource class are not really obvious as our Mirage classes do not extend modResource but Simplx_Mirage_Object. Simplx_Mirage_Object in turn does not inherit from modResource either. So what is the secrete? Again, the Prototype concept in combination with php magic.

Let's revisit the Product class and add some new features. 

```

class Product extends Simplx_Mirage_Object {
  // Set a custom default value to the modResource pagetitle property
  public $pagetitle = 'New Product';
  public function save(){
    // Do something customish
    parent::save();
  }  
}
```


Nothing out of the ordinary taking place above right? Well thanks, I'll take that as a compliment :) That was my grand plan actually, to hide all the details from you. What actually takes place is far from rocket science thankfully. 

This is what happens in the Simplx_Mirage_Object class constructor:
The name of the class which implements the Simplx_Mirage_Object class is noted using the php function get_class(). We now know that the class being constructed is of type Product.
The Simplx_Mirage static method getClass()  using the class name as argument. A reference to a Simplx_Mirage_Class is returned, or, 'false' if no modTemplate named Product exists.
If we got an 'id' argument in the constructor call we get the modResource using the good old MODX API. If we get a valid reference back we continue.
Now we must check so that the modResource actually uses the Template wrapped in the Mirage class we checked previously. If it uses the Product class, we can continue.
All the modResource properties are serialized to the internal _properties array.

This is what happens in the Simplx_Mirage_Object class __set, __get and __call:

Read up on php Magic Methods if your unfamiliar with them.
A property or method on a Product object is called. If php can not find a match using its regular process of resolving implemented members, it defaults to its Magic Functions. This first step is where your implementations overrides modResource members.
The method or property was not found in the Product class, or the Simplx_Mirage_Object class. Here is where a big part of the prototype coolness takes place. 

If a property was requested, the name is used to perform a lookup in the _property array. If the property is found its retrieved and returned. If the lookup returned false it is assumed that the property is a Template Variable and the MODX API is used to get or set the requested value.
If a method call was made it is intercepted by the Magic  __call  function. This in turn uses php's Introspection features to lookup a matching method on the modResource prototype and simply pass the method call on, including any and all arguments. The resulting value, if any, is returned back.


##Introducing Simplx Mirage part III

So, in the last post i tried to demystify the inner workings of Mirage a bit. Now its time for some practical stuff again, namely, relationships between objects.

###A word on associations
As you might be aware, a cornerstone of Object Oriented programming (well actually in systematics as a whole) is the ability to be able to describe one objects relationships to its own parts, and also associations to other objects in its vicinity which affects its daily activity. 
The most important types of associations can be categorized as being:

- Composite (is part of)
- Aggregate (uses)
- Reference (knows of)

An association is to be considered a Composite when one of the related Objects can not function in a context other than that in which it was created. Examples of such associations are:

- Pages in a book. Any Object could reference a book page, however a single page would make little sense outside the book which it is originally a part of. Also, an individual book page could not be in two books at the same time.

Aggregates are more flexible in nature as they allow dynamic relationships between more Objects than one. These are what you resort to when you find that a so called one-to-many relationship is called for. Examples of such are:

- Books in a shelf. A book will probably be moved from shelf to shelf during its life span. Even though it may end up in strange company from time to time, it will still be perfectly readable never the less. Not only can the book be moved, it can also be associated with one or more owners.

Reference is the association type with least dependency level. It is as its name suggests simply a named reference to an entity and is likely to be registered only at one end. The referenced Object will probably not be aware of the relationship.

- A reference in a book to another book or author.

So, summing it up, Composite relationships are really rigid and pretty reliable. Aggregates are much more dynamic and needs more explicit rules to govern them. Such as a penalty system for people who don't return your books to their correct shelf ;) And references are what helps is build simple, often pretty loose, networks of relationships between data.

So far Mirage handles Composite and Aggregate associations.

###Keeping it conventional 
Associations in Mirage are represented and managed in a simple and highly conventional manner. MODX actually, as do most file systems, handle these two types of relationships out of the box.

In an hierarchical file system structure any resource which is located in a folder is considered a Composite. As a direct consequence of this the resource will be deleted it its parent folder is deleted.

And then we have the Aggregates. In the world of Unix, Linux and Windows 7, and MODX, you have the concept of Symlinks. A Symlink, or symbolic link, makes it possible to create a link to an external entity enabling you to interact with it as if it was part of your local structure. If the object which contained the Symlink was deleted, the actual entity to which the link pointed will remain, unaffected.

Simplx Mirage uses the exact same metaphore to build hierarchical object relationships. Look at the following example:

```
$myObject = MyClass->getObject(10);
$myNewAggr = $myObject->addAggregate(22); // lets assume the Class is named 'SomeAggrClass'
$myNewComp = $myObject->addComposite('SomeCompClass');
```

The code above would result in the modResource with id 10 getting 2 child resources:

- The first would be a Symlink to an existing modResource with the id 22.

- The second resource to show up as a child to modResource 10 is a document using the 'SomeCompClass' template.

Simple right? Lets add some type check etc.

###Order please! 
If order was of limited concern to us we could stick with the above code. In my world of order and quest for readability l would want my associated objects to end up in a logical sub-structure. As is, the child resources are added in a jumble under their parent. This is of no concern to Mirage but to a system user or admin it would ruin usability.

Simplx Mirage has a nice little configuration API which i will write more about in a later post. Today I will cover the Simplx_Mirage_Object->_useFoldersForAssoc  boolean flag.

Simplx_Mirage_Object->_useFoldersForAssoc tells Mirage to store associated objects in folders which by default will use the same naming as the object type they represent. The above code would in other words expect a folder structure like this:

```

MyClass (folder)
- SomeAggrClass (folder)
- SomeCompClass (folder)
```

if _useFoldersForAssoc is set to true. If the expected structure is missing you will get an exception.

This brings me to the most important practice:

- Always wrap addAggregate, addComposite and their get and delete friends in more specialized Class members. Look at the following code and you will get the big point of this:

```

class BirthdayParty extends Simplx_Mirage_Object {
  public function __construct($id){
    $this->_useFoldersForAssoc = true;
    parent::__construct($id);
  }

  public function addInvitation($to, $answerBy){  
    $invite = $this->addComposite('Invitation');
    $invite->to = $to;
    $invite->answerBy = $answerBy;
    $invite->save();
  }

public function getInvitations(){  
  .....
  }
}
```
