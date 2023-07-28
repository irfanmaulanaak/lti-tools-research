<?php
require_once "vendor/autoload.php";

use IMSGlobal\LTI\ToolProvider;
use IMSGlobal\LTI\ToolProvider\DataConnector;
use IMSGlobal\LTI\OAuth\OAuthConsumer;



class ImsToolProvider extends ToolProvider\ToolProvider
{

    function onLaunch()
    {
        // Check if the request method is POST or PUT (you can add other methods as needed)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {

            // Get the raw payload data from the request body
            $launchData = $_POST;

            // Extract the username from the launch data
            $username = isset($launchData['lis_person_name_given']) ? $launchData['lis_person_name_given'] : '';
            $resource_title = isset($launchData['resource_link_title']) ? $launchData['resource_link_title'] : '';

            // Use the username
            echo "Hello, $username!. Welcome to $resource_title";
            // print_r($launchData);
        }
    }
}

// Cancel any existing session
session_start();
$_SESSION = array();
session_destroy();
session_start();

$db = mysql_connect("localhost:3307", "root", "");
if (!$db) {
    die('Not connected : ' . mysql_error());
}
$db_selected = mysql_select_db("ltitoolsdb", $db);
if (!$db_selected) {
    die('Can\'t use ltitools : ' . mysql_error());
}
$db_connector = DataConnector\DataConnector::getDataConnector('', $db, "mysql"); //need to specify the type of connector, in this case i use mysql not mysqli
$tool = new ImsToolProvider($db_connector);
$tool->handleRequest();
