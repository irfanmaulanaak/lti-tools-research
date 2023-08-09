<?php

namespace IMSGlobal\LTI\Http;

use IMSGlobal\LTI\HTTPMessage;

class StreamClient implements ClientInterface
{
    /**
     * @inheritdoc
     */
    public function send(HTTPMessage $message)
    {
        if (empty($message->requestHeaders)) {
            $message->requestHeaders = ["Accept: */*"];
        } elseif (count(preg_grep("/^Accept:/", $message->requestHeaders)) == 0) {
            $message->requestHeaders[] = "Accept: */*";
        }
        $opts = [
            'method' => $message->method,
            'content' => $message->request,
            'header' => $message->requestHeaders,
            'ignore_errors' => true,
        ];

        $message->requestHeaders = implode("\n", $message->requestHeaders);
        try {
            $ctx = stream_context_create(['http' => $opts]);
            $fp = @fopen($message->url, 'rb', false, $ctx);
            if ($fp) {
                $resp = @stream_get_contents($fp);
                $ok = $resp !== false;
                if ($ok) {
                    $message->response = $resp;
                    // see http://php.net/manual/en/reserved.variables.httpresponseheader.php
                    if (isset($http_response_header[0])) {
                        $message->responseHeaders = implode("\n", $http_response_header);
                        if (preg_match("/HTTP\/\d.\d\s+(\d+)/", $http_response_header[0], $out)) {
                            $message->status = $out[1];
                        }
                        $ok = $message->status < 400;
                        if (!$ok) {
                            $message->error = $http_response_header[0];
                        }
                    }
                    return $ok;
                }
            }
        } catch (\Exception $e){
            $message->error = $e->getMessage();
            return false;
        }
        $message->error = error_get_last()["message"];
        return false;
    }
}
