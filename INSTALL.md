#INSTALL

Simplx Mirage is simple to install. 

* Add the folder structure (core/components/simplx/mirage/*.php) to your installation
* Create 2 Snippets called simplx.mirage.setup and simplx.mirage. Copy paste the code contained in the files, or link to the files i you prefer.
* Execute the simplx.mirage.setup Snippet (very important) by adding it to a page and visiting the page using your browser (to much information? ;)
* Go into System Settings and search for mirage. You will find two Setting items 
    
    * simplx.mirage.object.viewname - The name of the Mysql view which is used by Mirage
    * simplx.mirage.setup.hasrun - A boolean flag indicating if setup was run successfully

* Include `require_once($modx->getOption('core_path').'/components/simplx/mirage/simplx_mirage.php');` in a Snippet, Plugin or php file
* Optionally turn on debugging output for each of the Mirage components while developing

```
    Simplx_Mirage::$_debugmode = true;
    Simplx_Mirage_Class::$_debugmode = true;
    Simplx_Mirage_Object::$_debugmode = true;
```
* All things should be good to go!
