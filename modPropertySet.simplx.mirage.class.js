[
   {
      "name":"useFoldersForAssoc",
      "desc":"  /**\n  * Should associations created for Objects of this Class be stored in  \n  * folders, as child resources?\n  *\n  * @access public\n  * @var boolean \n  */                          \n  public $_useFoldersForAssoc = false;",
      "xtype":"combo-boolean",
      "options":[

      ],
      "value":false,
      "lexicon":null,
      "overridden":false,
      "desc_trans":"  /**\n  * Should associations created for Objects of this Class be stored in  \n  * folders, as child resources?\n  *\n  * @access public\n  * @var boolean \n  */                          \n  public $_useFoldersForAssoc = false;",
      "menu":null
   },
   {
      "name":"composites",
      "desc":"",
      "xtype":"textarea",
      "options":[

      ],
      "value":"{}",
      "lexicon":null,
      "overridden":false,
      "desc_trans":"",
      "menu":null
   },
   {
      "name":"defaultObjectLocation",
      "desc":"  /**\n  * This property sets the default location in the site structure for modResources using this\n  * this modTemplate wrapper.  \n  *\n  * @access public\n  * @var int \n  */    \n  public $_defaultObjectLocation = 0;",
      "xtype":"numberfield",
      "options":[

      ],
      "value":"0",
      "lexicon":null,
      "overridden":false,
      "desc_trans":"  /**\n  * This property sets the default location in the site structure for modResources using this\n  * this modTemplate wrapper.  \n  *\n  * @access public\n  * @var int \n  */    \n  public $_defaultObjectLocation = 0;",
      "menu":null
   },
   {
      "name":"classUri",
      "desc":"  /**\n  * Class URI should point to the URI (ex. http://mysite.com/api/Whatnot/) where Objects (modResource's) \n  * of this type are found. \n  *\n  * @access public\n  * @var string \n  */      \n  public $_classUri;",
      "xtype":"textfield",
      "options":[

      ],
      "value":"",
      "lexicon":null,
      "overridden":false,
      "desc_trans":"  /**\n  * Class URI should point to the URI (ex. http://mysite.com/api/Whatnot/) where Objects (modResource's) \n  * of this type are found. \n  *\n  * @access public\n  * @var string \n  */      \n  public $_classUri;",
      "menu":null
   },
   {
      "name":"classTypeName",
      "desc":"  /**\n  * Class name is the Simplx Mirage moniker, or alias, for the modTemplate object. \n  * By default, this is the same the template instance name.\n  *\n  * @access public\n  * @var string \n  */          \n  public $_classTypeName;",
      "xtype":"textfield",
      "options":[

      ],
      "value":"",
      "lexicon":null,
      "overridden":false,
      "desc_trans":"  /**\n  * Class name is the Simplx Mirage moniker, or alias, for the modTemplate object. \n  * By default, this is the same the template instance name.\n  *\n  * @access public\n  * @var string \n  */          \n  public $_classTypeName;",
      "menu":null
   },
   {
      "name":"excludeModResourceFields",
      "desc":"  /**\n  * If excludeModResourceFields is set to true, toJSON/toArray will exclude all modResource fields for this\n  * Simplx Mirage Class. The serialized data only contain the TV's. Handy when you want to really emulate custom \n  * object types.\n  *\n  * @access public\n  * @var boolean \n  */              \n  public $_excludeModResourceFields = false;",
      "xtype":"combo-boolean",
      "options":[

      ],
      "value":false,
      "lexicon":null,
      "overridden":false,
      "desc_trans":"  /**\n  * If excludeModResourceFields is set to true, toJSON/toArray will exclude all modResource fields for this\n  * Simplx Mirage Class. The serialized data only contain the TV's. Handy when you want to really emulate custom \n  * object types.\n  *\n  * @access public\n  * @var boolean \n  */              \n  public $_excludeModResourceFields = false;",
      "menu":null
   },
   {
      "name":"prefixTvs",
      "desc":"  /**\n  * Should prefixes be used to indicate which modTemplate (Simplx_Mirage_Class) a modTemplateVar belongs to?\n  * Its HIGHLY recommended to set this to true as it makes your model infinitly more intuitive. \n  *\n  * @access public\n  * @var boolean \n  */                \n  public $_prefixTvs = true;  ",
      "xtype":"combo-boolean",
      "options":[

      ],
      "value":true,
      "lexicon":null,
      "overridden":false,
      "desc_trans":"  /**\n  * Should prefixes be used to indicate which modTemplate (Simplx_Mirage_Class) a modTemplateVar belongs to?\n  * Its HIGHLY recommended to set this to true as it makes your model infinitly more intuitive. \n  *\n  * @access public\n  * @var boolean \n  */                \n  public $_prefixTvs = true;  ",
      "menu":null
   },
   {
      "name":"tvPrefix",
      "desc":"  /**\t\n  * Actual TV prefix to use. This default to ($_classTypeName.'_'.TV name)\n  *\n  * @access public\n  * @var string \n  */                  \n  public $_tvPrefix;",
      "xtype":"textfield",
      "options":[

      ],
      "value":"",
      "lexicon":null,
      "overridden":false,
      "desc_trans":"  /**\t\n  * Actual TV prefix to use. This default to ($_classTypeName.'_'.TV name)\n  *\n  * @access public\n  * @var string \n  */                  \n  public $_tvPrefix;",
      "menu":null
   },
   {
      "name":"tvPrefixSeparator",
      "desc":"  /**\n  * Prefix separator. This default to '_'.\n  *\n  * @access public\n  * @var string \n  */                  \n  public $_tvPrefixSeparator = '_';",
      "xtype":"textfield",
      "options":[

      ],
      "value":"_",
      "lexicon":null,
      "overridden":false,
      "desc_trans":"  /**\n  * Prefix separator. This default to '_'.\n  *\n  * @access public\n  * @var string \n  */                  \n  public $_tvPrefixSeparator = '_';",
      "menu":null
   },
   {
      "name":"tvPrefixToLower",
      "desc":"  /**\n  * Should we accept prefix regardless of case? A good convention is to use upper case names\n  * for our Simplx Mirage Classes (modTemplates). By default TV prefixing is case sensitive.\n  *\n  * @access public\n  * @var boolean \n  */                    \n  public $_tvPrefixToLower = false;  ",
      "xtype":"combo-boolean",
      "options":[

      ],
      "value":false,
      "lexicon":null,
      "overridden":false,
      "desc_trans":"  /**\n  * Should we accept prefix regardless of case? A good convention is to use upper case names\n  * for our Simplx Mirage Classes (modTemplates). By default TV prefixing is case sensitive.\n  *\n  * @access public\n  * @var boolean \n  */                    \n  public $_tvPrefixToLower = false;  ",
      "menu":null
   },
   {
      "name":"forceTypeCheck",
      "desc":"  /**\n  * Should we force \"type check\" the $_classTypeName against the name of the modTemplate prototype?\n  *\n  * @access public\n  * @var boolean \n  */                      \n  public $_forceTypeCheck = true;",
      "xtype":"combo-boolean",
      "options":[

      ],
      "value":false,
      "lexicon":null,
      "overridden":false,
      "desc_trans":"  /**\n  * Should we force \"type check\" the $_classTypeName against the name of the modTemplate prototype?\n  *\n  * @access public\n  * @var boolean \n  */                      \n  public $_forceTypeCheck = true;",
      "menu":null
   },
   {
      "name":"createFoldersForAssoc",
      "desc":"  /**\n  * If folders for associated objects are not present, should we create them?  \n  *\n  * @access public\n  * @var boolean \n  */                          \n  public $_createFoldersForAssoc = true;",
      "xtype":"combo-boolean",
      "options":[

      ],
      "value":true,
      "lexicon":null,
      "overridden":false,
      "desc_trans":"  /**\n  * If folders for associated objects are not present, should we create them?  \n  *\n  * @access public\n  * @var boolean \n  */                          \n  public $_createFoldersForAssoc = true;",
      "menu":null
   },
   {
      "name":"assocNameMap",
      "desc":"  /**\n  * This map is used to map associated types to custom folder names when $_useFoldersForAssoc is true.\n  *\n  * @access public\n  * @var array \n  */                          \n  public $_assocNameMap = array();",
      "xtype":"textarea",
      "options":[

      ],
      "value":"{}",
      "lexicon":null,
      "overridden":false,
      "desc_trans":"  /**\n  * This map is used to map associated types to custom folder names when $_useFoldersForAssoc is true.\n  *\n  * @access public\n  * @var array \n  */                          \n  public $_assocNameMap = array();",
      "menu":null
   },
   {
      "name":"aggregates",
      "desc":"  /**\n  * Valid aggregate types. The type names should be those used by the referd to Simplx Mirage Classes (modTemplates)\n  *\n  * @access public\n  * @var array \n  */                            \n  public $_aggregates = array();  ",
      "xtype":"textarea",
      "options":[

      ],
      "value":"{}",
      "lexicon":null,
      "overridden":false,
      "desc_trans":"  /**\n  * Valid aggregate types. The type names should be those used by the referd to Simplx Mirage Classes (modTemplates)\n  *\n  * @access public\n  * @var array \n  */                            \n  public $_aggregates = array();  ",
      "menu":null
   },
   {
      "name":"associations",
      "desc":"  /**\n  * Valid association types. The type names should be those used by the referd to Simplx Mirage Classes (modTemplates)\n  *\n  * @access public\n  * @var array \n  */                            \n  public $_associations = array();",
      "xtype":"textarea",
      "options":[

      ],
      "value":"{}",
      "lexicon":null,
      "overridden":false,
      "desc_trans":"  /**\n  * Valid association types. The type names should be those used by the referd to Simplx Mirage Classes (modTemplates)\n  *\n  * @access public\n  * @var array \n  */                            \n  public $_associations = array();",
      "menu":null
   }
]
