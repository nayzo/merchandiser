<?php

namespace Nzo\MerchandBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/{_locale}",
 * requirements={"_locale" = "fr|en"},
 * defaults={"_locale" = "en"} )
 */
class ManagerController extends Controller
{
    /**
     * @Route("/create/", name="nzo_merchand_create")
     * @Method({"GET", "POST"})
     */
    public function createAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $Array = array('sellername' => $request->request->get('sellername'),
                'buyername' => $request->request->get('buyername'),
                'price' => $request->request->get('price'),
                'amount' => $request->request->get('amount'),
            );

            $Array = serialize($Array);
            $this->get('old_sound_rabbit_mq.manager_insert_producer')->publish($Array);

        }

        return $this->render(':Manager:create.html.twig');
    }

    /**
     * @Route("/show/", name="nzo_merchand_show")
     * @Method({"GET", "POST"})
     */
    public function showAction(Request $request)
    {
        $path = $this->get('kernel')->getRootDir() . '/Resources/views/Manager';
        //$path = __DIR__ . '/../Resources/views/Manager';

        $finder = new Finder();
        $finder->files()->in($path)->name('file.xml');
        foreach ($finder as $file) {
            $val = $file->getContents();
        }

        $xml = simplexml_load_string($val);
        $json = json_encode($xml);
        $array = json_decode($json, TRUE);
        $array = array_slice($array['node'], -5);

        if ($request->isXmlHttpRequest()) {
            return new Response(json_encode($array), 200, array('Content-Type' => 'application/json'));
        }

        return $this->render(':Manager:show.html.twig', array('content' => $array));
    }

}
