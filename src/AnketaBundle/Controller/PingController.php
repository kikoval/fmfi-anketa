<?php

namespace AnketaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Description of ContactController
 *
 * @author ivan
 */
class PingController  extends Controller {
    //put your code here

    public function pingAction($devName) {

        $devs = array ( 'joe' => 'jakub.marek@gmail.com', "ivan" => 'descent89@gmail.com');
        $devMail = $devs[$devName];
        if (array_key_exists($devName, $devs)) {
            $devMail = $devs[$devName];
        } else {
            $devMail = $devs['joe'];
        }

        $mailer = $this->get('mailer');
        $message = \Swift_Message::newInstance()
                        ->setSubject('FMFI ANKETA')
                        ->setFrom('pingas@azet.sk')
                        ->setTo($devMail)
                        ->setBody($this->renderView('AnketaBundle:Ping:emailPing.html.twig'))
        ;
        $mailer->send($message);

        return $this->render('AnketaBundle:Ping:ping.html.twig', array('devName' => $devName));
    }
}
?>
