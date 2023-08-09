<?php

namespace IMSGlobal\LTI\Http;

use IMSGlobal\LTI\HTTPMessage;

class CurlClient implements ClientInterface
{
    /**
     * @inheritdoc
     */
    public function send(HTTPMessage $message)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $message->url);
        if (!empty($message->requestHeaders)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $message->requestHeaders);
        } else {
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }
        if ($message->method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $message->request);
        } else {
            if ($message->method !== 'GET') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $message->method);
                if (!is_null($message->request)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $message->request);
                }
            }
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        //curl_setopt($ch, CURLOPT_SSLVERSION,3);
        $chResp = curl_exec($ch);
        $ok = $chResp !== false;
        if ($ok) {
            $chResp = str_replace("\r\n", "\n", $chResp);
            $chRespSplit = explode("\n\n", $chResp, 2);
            if ((count($chRespSplit) > 1) && (substr($chRespSplit[1], 0, 5) === 'HTTP/')) {
                $chRespSplit = explode("\n\n", $chRespSplit[1], 2);
            }
            $message->responseHeaders = $chRespSplit[0];
            $message->response = $chRespSplit[1];
            $message->status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $ok = $message->status < 400;
            if (!$ok) {
                $message->error = curl_error($ch);
            }
        }
        $message->requestHeaders = str_replace("\r\n", "\n", curl_getinfo($ch, CURLINFO_HEADER_OUT));
        curl_close($ch);

        return $ok;
    }
}
