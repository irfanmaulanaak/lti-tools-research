<?php

namespace IMSGlobal\LTI\ToolProvider;

/**
 * Class to represent a content-item placement object
 *
 * @author  Stephen P Vickers <svickers@imsglobal.org>
 * @copyright  IMS Global Learning Consortium Inc
 * @date  2016
 * @version 3.0.2
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */
class ContentItemPlacement
{

/**
 * Class constructor.
 *
 * @param int $displayWidth       Width of item location
 * @param int $displayHeight      Height of item location
 * @param string $documentTarget  Location to open content in
 * @param string $windowTarget    Name of window target
 */
    function __construct($displayWidth, $displayHeight, $documentTarget, $windowTarget)
    {

        if (!empty($displayWidth)) {
            $this->displayWidth = $displayWidth;
        }
        if (!empty($displayHeight)) {
            $this->displayHeight = $displayHeight;
        }
        if (!empty($documentTarget)) {
            $this->documentTarget = $documentTarget;
            //Canvas requires presentationDocumentTarget. Also according to LTI specs: https://www.imsglobal.org/lti/model/uml/purl.imsglobal.org/vocab/lti/v1/ci/index.html
            $this->presentationDocumentTarget = $documentTarget;
        }
        if (!empty($windowTarget)) {
            $this->windowTarget = $windowTarget;
        }

    }

}
