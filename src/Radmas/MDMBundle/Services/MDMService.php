<?php

namespace Radmas\MDMBundle\Services;

use Symfony\Component\Routing\Router;

class MDMService {

    private $signcert;
    private $privkey;
    /**
     * @var Router
     */
    private $router;

    function __construct($signcert, $privkey)
    {
        $this->signcert = $signcert;
        $this->privkey = $privkey;
    }

    /**
     * @return array
     */
    public function generalPayload() {
        return [];
    }

    public function profileServicePayload($request, $challenge, $sign = false) {

        $payload = $this->generalPayload();

        $payload['PayloadType'] = "Profile service";
        $payload['PayloadIdentifier'] = "com.acme.mobileconfig.profile-service";

        // strings that show up in UI, customisable
        $payload['PayloadDisplayName'] = "ACME Profile Service";
        $payload['PayloadDescription'] = "Install this profile to enroll for secure access to ACME Inc.";

        $payloadContent['URL'] = "https://" + $this->router->getContext()->getBaseUrl() + "/profile";
        $payloadContent['DeviceAttributes'] = [
            "UDID",
            "VERSION",
            "PRODUCT", # e.g. iPhone1,1 or iPod2,1
            "SERIAL", # The device's serial number
            "MEID", # The device's Mobile Equipment Identifier
            "IMEI"
        ];

        if ($challenge && !empty($challenge)) {
            $payloadContent['Challenge'] = $challenge;
        }

        $payload['PayloadContent'] = $payloadContent;

        if ($sign) {
            return $this->singMessage($this->serializeMessage($payloadContent));
        } else {
            $this->serializeMessage($payloadContent);
        }
    }

    public function singMessage($message) {
        $temp = tmpfile();
        fwrite($temp, $message);
        fseek($temp, 0);
        $output = tmpfile();

        $data = null;
        if (openssl_pkcs7_sign($temp, $output, $this->signcert, $this->privkey, [], PKCS7_BINARY)) {
            $data = file_get_contents($output);
        }

        fclose($temp);
        fclose($output);

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