## Class definitions

Please refer to the [class definition documentation](http://www.imsglobal.org/sites/default/files/lti/tp-library-php/docs/index.html) for details of the classes defined in this library.

## Specifying a data connector

A data connector instance is required to initialize the entities defined in this library.  This is a mechanism for abstacting the data persistence from the application code and allows support for different databases as well as bespoke implementations based on an existing table structure within an application.  It is also possible to set up a dummy application which does not persist any data.

### MySQL database

When using the PHP MySQL library, connect to the database in the normal way (using a server name, user name and password) and use the MySQL link identifier which is returned to create the data connector.  A table name prefix (e.g. 'app1_') can be optionally included.

```
use IMSGlobal\LTI\ToolProvider\DataConnector;

$db = mysql_connect($db_server, $db_user, $db_password);
mysql_select_db($db_schema);
 
$db_connector = DataConnector\DataConnector::getDataConnector('app1_', $db);
```

### PHP Data Objects (PDO) interface

Other types of database can be accessed via the PDO interface.  Here are three examples.

#### SQL Server

```
use IMSGlobal\LTI\ToolProvider\DataConnector;

$db = new PDO("mssql:host={$db_host};dbname={$db_schema}", $db_user, $db_password);

$db_connector = DataConnector\DataConnector::getDataConnector('', $db);
```

#### Oracle

```
use IMSGlobal\LTI\ToolProvider\DataConnector;

$db = new PDO("oci:dbname={$db_schema}", $db_user, $db_password);
$db_connector = DataConnector\DataConnector::getDataConnector('', $db);
```

#### SQLite

```
use IMSGlobal\LTI\ToolProvider\DataConnector;

$db = new PDO('sqlite::memory:');
$db_connector = DataConnector\DataConnector::getDataConnector('', $db);
```

### Other connections

Other standard data connectors will be provided for direct connections to SQL Server and Oracle databases.  Otherwise a bespoke connector can be created by writing a sub-class of the DataConnector class.

## Initializing a tool consumer

When a launch request is received it will be validated with the shared secret associated with the consumer key. The default data structure uses the `lti2_consumer` table to record details of tool consumers.  A record may be initialized in this table for an LTI 1.x tool consumer as follows (records for LTI 2 tool consumers will be created as part of the registration process):

```
use IMSGlobal\LTI\ToolProvider;

$consumer = new ToolProvider\ToolConsumer('testing.edu', $db_connector);
$consumer->name = 'Testing';
$consumer->secret = 'ThisIsASecret!';
$consumer->enabled = TRUE;
$consumer->save();
```

## Validating a launch request

The primary use case for the classes is to validate an incoming launch request from a tool consumer. Once a record has been initialised for the tool consumer (see above), the verification of the authenticity of the LTI launch request is handled automatically by the `ToolProvider` class.  A sub-class is created and the `onLaunch` method overridden to define the code to be run when a valid launch request is received.

```
use IMSGlobal\LTI\ToolProvider;

class App1ToolProvider extends ToolProvider\ToolProvider {
 
  function onLaunch() {
 
    // Insert code here to handle incoming connections - use the user,
    // context and resourceLink properties of the class instance
    // to access the current user, context and resource link.
 
  }
 
}
 
$tool = new App1ToolProvider($db_connector);
$tool->handleRequest();
```

The `handleRequest` method checks the authenticity of the incoming request by verifying the OAuth signature (using the shared secret recorded for the tool consumer), the timestamp is within a defined limit of the current time, and the nonce value has not been previously used.  Only if the request passes all these checks is the `onLaunch` method called.  The process also captures various standard launch parameters to allow access to service requests.

When a launch is not valid, a message is returned to the tool consumer with a more detailed reason to be logged.  If a custom parameter of `debug=true` is included in the launch then the more detailed reason for the failure is displayed to the user.

### The onLaunch method

The `onLaunch` method may be used to:
* create the user account if it does not already exist (or update it if it does);
* create any workspace reqjuired for the resource link if it does not already exist (or update it if it does);
* establish a new session for the user (or otherwise log the user into the tool provider application);
* keep a record of the return URL for the tool consumer (for example, in a session variable);
* set the URL for the home page of the application so the user may be redirected to it.

Even though a request may be in accordance with the LTI specification, a tool provider may still choose to reject it because, for example, not all of the required data has been passed.  A request may be rejected as follows:

* optionally set an error message to return to the user (if the tool consumer supports this facility);
* set the `ok` property to `FALSE`.

For example:

```
function onLaunch() {

  ...
 
  $this->reason = 'Incomplete data';
  $this->ok = FALSE;

}
```

### Content-item and Registration messages

If your tool also supports the Content-Item message or LTI 2 registration messages, then their associated method should also be overridden; for example:

```
use IMSGlobal\LTI\ToolProvider;

class App1ToolProvider extends ToolProvider\ToolProvider {

  function onLaunch() {

    // Insert code here to handle incoming launches - use the user, context
    // and resourceLink properties to access the current user, context and resource link.

  }

  function onContentItem() {

    // Insert code here to handle incoming content-item requests - use the user and context
    // properties to access the current user and context.

  }
 
  function onRegister() {

    // Insert code here to handle incoming registration requests - use the user
    // property of the $tool_provider parameter to access the current user.

  }

  function onError() {
 
    // Insert code here to handle errors on incoming connections - do not expect
    // the user, context and resourceLink properties to be populated but check the reason
    // property for the cause of the error.  Return TRUE if the error was fully
    // handled by this method.
 
  }

}

$tool = new App1ToolProvider($db_connector);
$tool->handleRequest();
```

## Protecting a consumer key

The connection between a tool consumer and a tool provider is secured using a consumer key and a shared secret. However, there are some risks to this mechanism:
* launch requests will continue to be accepted even if a license has expired;
* if the consumer key is used to submit launch requests from more than one tool conumser, there is a risk of clashing resource link and user IDs being received from each.

The first risk can be avoided by manually removing or disabling the consumer key as soon as the license expires. Alternatively, the dates for any license may be recorded for the tool consumer so that the library can make the appropriate check when a launch request is received.

The second risk can be alleviated by setting the protected property of the tool consumer. This will cause launch requests to be only accepted from tool consumers with the same `tool_consumer_guid` parameter.  The value of this parameter is recorded from the first launch request received using the associated consumer key.  Note that this facility depends upon the tool consumer sending a value for the `tool_consumer_guid` parameter and each tool consumer instance having a unique value for this parameter.

The following code illustrates how these options may be set:

```
use IMSGlobal\LTI\ToolProvider;

// load the tool consumer record
$consumer = new ToolProvider\ToolConsumer('testing.edu', $db_connector);

// set an expiry date for 30 days time
$consumer->enable_until = time() + (30 * 24 * 60 * 60);
 
// protect use of the consumer key to a single tool consumer
$consumer->protected = TRUE;
 
// save the changes
$consumer->save();
```

Note that the default value of the `enable_from` property is `NULL` which means that access is available immediately.  A `NULL` value for the `enable_until` property means that access does not automatically expire (this is also the default).

## Content-item resource link IDs

One of the differences in handling a content-item message request is that any LTI links your tool passes back to be created will not yet have an associated resource link ID.  One solution to this is to create an internal resource link ID for the resource and add this as a custom parameter to the link with a name of content_item_id.  When a launch request is received from a resource link ID which is not recognised and this custom parameter is present, a check is made for a resource link with the value of the parameter.  If found, the resource link ID is updated with the resource link ID from the launch request and so the custom parameter will be ignored on any subsequent launches.  In this way, the resource created via a content-item request will be automatically connected to the resource link created in the tool consumer.  For example, here is some sample code based on this workflow implemented in the sample Rating application:

```
...
      $item = new ToolProvider\ContentItem('LtiLink');
      $item->setMediaType(ToolProvider\ContentItem::LTI_LINK_MEDIA_TYPE);
      $item->setTitle($_SESSION['title']);
      $item->setText($_SESSION['text']);
      $item->icon = new ToolProvider\ContentItemImage(getAppUrl() . 'images/icon50.png', 50, 50);
      $item->custom = array('content_item_id' => $_SESSION['resource_id']);
      $form_params['content_items'] = ToolProvider\ContentItem::toJson($item);
      if (!is_null($_SESSION['data'])) {
        $form_params['data'] = $_SESSION['data'];
      }
      $data_connector = DataConnector\DataConnector::getDataConnector(DB_TABLENAME_PREFIX, $db);
      $consumer = ToolProvider\ToolConsumer::fromRecordId($_SESSION['consumer_pk'], $data_connector);
      $form_params = $consumer->signParameters($_SESSION['return_url'], 'ContentItemSelection', $_SESSION['lti_version'], $form_params);
      $page = ToolProvider\ToolProvider::sendForm($_SESSION['return_url'], $form_params);
      echo $page;
      exit;
...
```

The $_SESSION['resource_id'] variable contains a GUID generated on launch; this is used as the placeholder until the first launch of this item is performed and the validation of the request will automatically replace this resource link ID with the one passed in the launch parameters.
