<?php

namespace Nzo\MerchandBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;

class ManagerController extends Controller
{
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
        return $this->render('NzoMerchandBundle:Manager:create.html.twig');
    }

    public function showAction(Request $request)
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/../Resources/views/Manager')->name('file.xml');
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

        return $this->render('NzoMerchandBundle:Manager:show.html.twig', array('content' => $array));
    }

}
