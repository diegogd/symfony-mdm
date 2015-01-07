<?php

namespace Radmas\MDMBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/CA")
     */
    public function CAAction()
    {
        $path = $this->get('kernel')->getRootDir(). "/../certs/mdmca.crt";
        $content = file_get_contents($path);

        $response = new Response();
        $response->headers->set('Content-Type', "application/x-x509-ca-cert");
        $response->headers->set('Content-Disposition', 'attachment;filename="mdmca.crt"');

        $response->setContent($content);

        return $response;
    }

    /**
     * @Route("/enroll")
     */
    public function enrollAction(Request $request)
    {
        $result = $this->get('mdm_service')->profileServicePayload($request, "signed-auth-token", true);

        $response = new Response();
        $response->setContent($result);
        $response->headers->set("Content-Type", "application/x-apple-aspen-config");
        $response->headers->set('Content-Disposition', 'attachment;filename="signed.plist"');

        return $response;
    }
}
