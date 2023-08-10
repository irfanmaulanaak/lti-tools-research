<?php
require_once "vendor/autoload.php";

use IMSGlobal\LTI\ToolProvider;
use IMSGlobal\LTI\ToolProvider\DataConnector;
use IMSGlobal\LTI\OAuth\OAuthConsumer;
use IMSGlobal\LTI\OAuth\OAuthRequest;
use IMSGlobal\LTI\OAuth\OAuthSignatureMethod_HMAC_SHA1;
use  IMSGlobal\LTI\OAuth\OAuthDataStore;
use IMSGlobal\LTI\OAuth\OAuthToken;
use IMSGlobal\LTI\OAuth\OAuthServer;
use IMSGlobal\LTI\ToolProvider\ResourceLink;

class ImsToolProvider extends ToolProvider\ToolProvider
{
    function onLaunch()
    {

        $tool_consumer_secrets['ltitoolsgcp1'] = 'ltitoolsgcp1';
        $ok = true;
        // Check it is a POST request
        $ok = $ok && $_SERVER['REQUEST_METHOD'] === 'POST';
        // Check the LTI message type
        $ok = $ok && isset($_POST['lti_message_type']) && ($_POST['lti_message_type'] === 'basic-lti-launch-request');
        // Check the LTI version
        $ok = $ok && isset($_POST['lti_version']) && ($_POST['lti_version'] === 'LTI-1p0');
        // Check a consumer key exists
        $ok = $ok && !empty($_POST['oauth_consumer_key']);
        // Check a resource link ID exists
        $ok = $ok && !empty($_POST['resource_link_id']);
        // Check the consumer key is recognised
        $ok = $ok && array_key_exists($_POST['oauth_consumer_key'], $tool_consumer_secrets);
        // Check the OAuth credentials (nonce, timestamp and signature)
        if ($ok) {
            try {
                $consumer_key = $_POST['oauth_consumer_key'];
                $store = new ImsOAuthDataStore($consumer_key, $tool_consumer_secrets['ltitoolsgcp1']);
                $server = new OAuthServer($store);
                $method = new OAuthSignatureMethod_HMAC_SHA1();
                $server->add_signature_method($method);
                $request = OAuthRequest::from_request();
                $signature_key = $method->build_signature($request, $store->lookup_consumer($consumer_key), $store->lookup_token($consumer_key, '', ''));
                $server->verify_request($request);
            } catch (Exception $e) {
                $ok = FALSE;
            }
        } else {
            include "frontend/error.php";
            exit;
        }

        // Check if the request method is POST or PUT (you can add other methods as needed)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {

            // Get the raw payload data from the request body
            $launchData = $_POST;
            $consumer = new ToolProvider\ToolConsumer($launchData['oauth_consumer_key'], $_SESSION['db_connector']);
            $consumer->name = $_POST['tool_consumer_instance_name'];
            $consumer->secret = $tool_consumer_secrets['ltitoolsgcp1'];
            $consumer->ltiVersion = $_POST['lti_version'];
            $consumer->enabled = TRUE;
            $consumer->save();

            echo "<script>console.log(JSON.parse('" . json_encode($consumer) . "'));</script>";

            $resource_link = ToolProvider\ResourceLink::fromConsumer($consumer, $_POST['resource_link_id']);
            $resource_link->setSetting('lis_outcome_service_url', $_POST['lis_outcome_service_url']);

            // echo "<script>console.log(JSON.parse('" . json_encode($resource_link) . "'));</script>";

            $user = ToolProvider\User::fromResourceLink($resource_link, $launchData['user_id']);
            $score = "1";
            $outcome = new ToolProvider\Outcome($score);



            $ok = $resource_link->doOutcomesService(ResourceLink::EXT_WRITE, $outcome, $user);

            // print_r($_POST);

            $tessto = json_encode($_POST);

            echo "<script type='text/javascript'>console.log('kon_kuda', '$tessto');</script>";
            // echo "<script type='text/javascript'>console.log('kon_sapi');</script>";


            // $outcome2 = new ToolProvider\Outcome();

            // if ($resource_link->doOutcomesService(ToolProvider\ResourceLink::EXT_READ, $outcome2, $user)) {
            //     $_SESSION['score2'] = $outcome2->getValue();
            //     echo "<script type='text/javascript'>console.log('dooman');</script>";
            // }

            // Extract the username from the launch data
            $username = isset($launchData['lis_person_name_given']) ? $launchData['lis_person_name_given'] : '';
            $resource_title = isset($launchData['resource_link_title']) ? urlencode($launchData['resource_link_title']) : '';
            $oath_ckey = isset($launchData['oauth_consumer_key']) ? urlencode($launchData['oauth_consumer_key']) : '';
            $roles = isset($launchData['roles']) ? urlencode($launchData['roles']) : '';
            $fullname = isset($launchData['lis_person_name_full']) ? urlencode($launchData['lis_person_name_full']) : '';
            $oath_sign = isset($launchData['oauth_signature']) ? ($launchData['oauth_signature']) : '';

            // Use the username
            echo "Hello, $username!. Welcome to $resource_title";
            $deeplink = "udptest://?resourceTitle=" . $resource_title . "&oath_ckey=" . $oath_ckey . "&roles=" . $roles . "&fullname=" . $fullname . "&oath_sign=" . $oath_sign;
            $deeplink_desktop = $deeplink;
            $deeplink_android = $deeplink;
            $deeplink_vr = $deeplink;
            include "frontend/index.php";
        }
    }
}
class ImsOAuthDataStore extends OAuthDataStore
{

    private $consumer_key = NULL;
    private $consumer_secret = NULL;

    public function __construct($consumer_key, $consumer_secret)
    {
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
    }

    function lookup_consumer($consumer_key)
    {
        return new OAuthConsumer($this->consumer_key, $this->consumer_secret);
    }

    function lookup_token($consumer, $token_type, $token)
    {
        return new OAuthToken($consumer, '');
    }

    function lookup_nonce($consumer, $token, $nonce, $timestamp)
    {
        return FALSE;  // If a persistent store is available nonce values should be retained for a period and checked here
    }

    function new_request_token($consumer, $callback = null)
    {
        return NULL;
    }

    function new_access_token($token, $consumer, $verifier = null)
    {
        return NULL;
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
$_SESSION['db_connector'] = $db_connector;
$tool = new ImsToolProvider($db_connector);

$tool->onLaunch();

// $tool->handleRequest();
