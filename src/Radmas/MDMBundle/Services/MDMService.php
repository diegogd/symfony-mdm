<?php

namespace Radmas\MDMBundle\Services;

use CFPropertyList\CFArray;
use CFPropertyList\CFDictionary;
use CFPropertyList\CFPropertyList;
use CFPropertyList\CFString;
use JMS\Serializer\Serializer;
use Radmas\MDMBundle\Model\MDMDictionary;
use Radmas\MDMBundle\Model\MDMPayload;
use Symfony\Component\Routing\Router;

class MDMService {

    private $signcert;
    private $privkey;
    /**
     * @var Router
     */
    private $router;

    /**
     * @var Serializer
     */
    private $serializer;

    function __construct($signcert, $privkey, $privkeyPasswordFile, Serializer $serializer)
    {
        $this->signcert = $signcert;
        $this->privkey = $privkey;
        $this->serializer = $serializer;

        $this->password = file_get_contents($privkeyPasswordFile);
    }

    /**
     * @return CFPropertyList
     */
    public function generalPayload() {
        return new CFPropertyList();
    }

    public function profileServicePayload($request, $challenge, $sign = false) {

        $payload = $this->generalPayload();

        $payload->add($dict = new CFDictionary());

        $dict->add('PayloadType', new CFString("Profile service"));
        $dict->add('PayloadIdentifier', new CFString("com.acme.mobileconfig.profile-service"));
        $dict->add('PayloadDisplayName', new CFString("ACME Profile Service"));
        $dict->add('PayloadDescription', new CFString("Install this profile to enroll for secure access to ACME Inc."));

        $dict->add('PayloadContent', $content = new CFDictionary());

        $content->add('URL', new CFString("https://" . $this->router->getContext()->getBaseUrl() . "/profile"));
        $content->add('DeviceAttributes', $array = new CFArray());

        $collection = [
            "UDID",
            "VERSION",
            "PRODUCT", # e.g. iPhone1,1 or iPod2,1
            "SERIAL", # The device's serial number
            "MEID", # The device's Mobile Equipment Identifier
            "IMEI"
        ];

        foreach ($collection as $item) {
            $array->add(new CFString($item));
        }


        if ($challenge && !empty($challenge)) {
            $content->add('Challenge', new CFString($challenge));
        }


        $content = $payload->toXML(false);

        // $content = $payload->toXML();

        if ($sign) {
            $content = $this->singMessage($content);
        }

        return $content;
    }

    public function singMessage($message) {

        $dataStrFile  = realpath(tempnam('/tmp', 'pp_'));
        $fd = fopen($dataStrFile, 'w');
        fwrite($fd, $message);
        fclose($fd);

        $signedDataFile  = realpath(tempnam('/tmp', 'pp_'));


        $data = null;
        if (openssl_pkcs7_sign($dataStrFile, $signedDataFile, 'file://'.realpath($this->signcert), ['file://'.realpath($this->privkey), $this->password], [], PKCS7_BINARY)) {
            unlink($dataStrFile);

            $signedData = file_get_contents($signedDataFile);
            $signedDataArray = explode("\n\n", $signedData);
            $signedData = $signedDataArray[1];
            $signedData = base64_decode($signedData);

            unlink($signedDataFile);
            $data = $signedData;
        }

        return $data;
    }

    public function setRouter($router) {
        $this->router = $router;
    }

    public function serializeMessage($message) {
        $xml = new \SimpleXMLElement("<plist/>");

        foreach ($message as $key => $element) {
            $xml->addChild('key', $key);

            if (is_array($element)) {
                $array = $xml->addChild("array");
                foreach ($message as $key => $element) {
                    $array->addChild($element);
                }

            } else {
                $xml->addChild('string', $element);
            }
        }


    }
}