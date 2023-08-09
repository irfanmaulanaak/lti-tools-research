<?php

namespace IMSGlobal\LTI\Http;

use IMSGlobal\LTI\HTTPMessage;

interface ClientInterface
{
    /**
     * @param HTTPMessage $message
     * @return bool True if the request was successful
     */
    public function send(HTTPMessage $message);
}
