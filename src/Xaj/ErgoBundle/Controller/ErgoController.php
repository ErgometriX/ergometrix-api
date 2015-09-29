<?php
namespace Xaj\ErgoBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Xaj\ErgoBundle\Entity\Boat;
use Xaj\ErgoBundle\Entity\Rower;
use Xaj\ErgoBundle\Entity\Leader;
use Xaj\ErgoBundle\Entity\User;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;


use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class ErgoController extends FOSRestController
{
    /**
     * @Get("/boats/check-inscription")
     * @View()
     */
    public function checkInscriptionAction()
    {
        $now = new \DateTime();
        $dernierDelai = new \DateTime("2015-12-08 23:59:59");
        $res = $now <= $dernierDelai;
        $res = ($res == true) ? 1 : 0;
        return array('check' => $res);
    }

    /**
     * @Post("/boats/code-inscription")
     * @RequestParam(name="code")
     * @View()
     */
    public function codeInscriptionAction($code)
    {
        $res = ($code == 'patergo') ? 1 : 0;
        return array('valid' => $res);
    }

    /**
     * @Post("/boats/myboats")
     * @RequestParam(name="email")
     * @View()
     */
    public function myBoatsAction($email)
    {
        $em = $this->getDoctrine()->getManager();
        $leaders = $em->getRepository('XajErgoBundle:Leader')->findByEmail($email);
        $myBoats = array();
        foreach ($leaders as $leader) {
            $myBoats[] = $leader->getBoat();
        }
        return $myBoats;
    }

    /**
     * @Get("/boats")
     * @View()
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Renvoie la liste de tous les bateaux actifs (non soft-removed)",
     * )
     */
    public function getBoatsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $boats = $em->getRepository('XajErgoBundle:Boat')->getBoatsNotDeleted();

        return $boats;
    }

    /**
     * @Get("/boats/deleted")
     * @View()
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function getDeletedBoatsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $boats = $em->getRepository('XajErgoBundle:Boat')->getDeletedBoats();

        return $boats;
    }

    /**
     * @Get("/boats/names")
     * @View()
     */
    public function getBoatsNamesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $names = $em->getRepository('XajErgoBundle:Boat')->getBoatsNames();

        return $names;
    }

    /**
     * @Get("/boats/category/{category}")
     * @View()
     */
    public function getBoatsByCategoryAction($category)
    {
        $em = $this->getDoctrine()->getManager();
        $boats = $em->getRepository('XajErgoBundle:Boat')->findBy(array(
                'category' => $category,
                'deleted' => 0)
            );
        $boats_a = array();
        foreach ($boats as $boat) {
            $boat_a = array();
            $boat_a[] = $boat->getId(); 
            $boat_a[] = $boat->getName();
            $i = 0;
            foreach ($boat->getRowers() as $rwr) {
                $i += 1;
                $boat_a[] = $rwr->getLastname();
                $boat_a[] = $rwr->getFirstname();
                $date = $rwr->getBirthdate();
                $boat_a[] = date_format($date, 'd/m/Y');
                $boat_a[] = $rwr->getLicense();
            }
            $boats_a[] = $boat_a;
        }

        return $boats_a;
    }

    /**
     * @Get("/boats/{boat}")
     * @ParamConverter("boat", class="Xaj\ErgoBundle\Entity\Boat", options={"id" = "boat"})
     * @View()
     */
    public function getBoatAction(Boat $boat)
    {
        return $boat;
    }

    /**
     * @Post("/boats/add")
     * @RequestParam(name="name")
     * @RequestParam(name="category")
     * @View()
     */
    public function addBoatAction($name, $category, $record = 0, $payment = false, $valid = false)
    {
        $em = $this->getDoctrine()->getManager();

        $boat = new Boat();
        $boat->setName($name);
        $boat->setCategory($category);
        $boat->setRecord($record);
        $boat->setPayment($payment);
        $boat->setValid($valid);

        $em->persist($boat);
        $em->flush();

        return $boat;
    }

    /**
     * @Post("/boats/catupdate/{boat}")
     * @ParamConverter("boat", class="Xaj\ErgoBundle\Entity\Boat", options={"id" = "boat"})
     * @RequestParam(name="category")
     * @View()
     */
    public function updateCategoryBoatAction(Boat $boat, $category)
    {
        $em = $this->getDoctrine()->getManager();

        $firstCat = substr($boat->getCategory(), 0, 3);
        $boat->setCategory($firstCat . $category);

        $em->persist($boat);
        $em->flush();

        return $boat;
    }
    

    /**
     * @Post("/boats/valid/{boat}")
     * @ParamConverter("boat", class="Xaj\ErgoBundle\Entity\Boat", options={"id" = "boat"})
     * @View()
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function validBoatAction(Boat $boat)
    {
        $em = $this->getDoctrine()->getManager();

        $boat->setValid(true);

        $em->persist($boat);
        $em->flush();

        return $boat;
    }

    /**
     * @Post("/boats/pay/{boat}")
     * @ParamConverter("boat", class="Xaj\ErgoBundle\Entity\Boat", options={"id" = "boat"})
     * @View()
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function payBoatAction(Boat $boat)
    {
        $em = $this->getDoctrine()->getManager();

        $boat->setPayment(true);

        $em->persist($boat);
        $em->flush();

        return $boat;
    }

    /**
     * @Post("/boats/record/{boat}")
     * @ParamConverter("boat", class="Xaj\ErgoBundle\Entity\Boat", options={"id" = "boat"})
     * @RequestParam(name="temps")
     * @View()
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function recordBoatAction(Boat $boat, $temps)
    {
        $em = $this->getDoctrine()->getManager();

        $boat->setRecord($temps);

        $em->persist($boat);
        $em->flush();

        return $boat;
    }

    /**
     * @Post("/boats/{boat}")
     * @ParamConverter("boat", class="Xaj\ErgoBundle\Entity\Boat", options={"id" = "boat"})
     * @RequestParam(name="name")
     * @View()
     */
    public function editBoatAction(Boat $boat, $name = '')
    {
        $em = $this->getDoctrine()->getManager();

        if ($name != '') {
            $boat->setName($name);
        }

        $em->persist($boat);
        $em->flush();

        return $boat;
    }

    /**
     * @Delete("/boats/{boat}")
     * @ParamConverter("boat", class="Xaj\ErgoBundle\Entity\Boat", options={"id" = "boat"})
     * @View()
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function removeBoatAction(Boat $boat)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($boat);
        $em->flush();
    }

    /**
     * @Post("/boats/softremove/{boat}")
     * @ParamConverter("boat", class="Xaj\ErgoBundle\Entity\Boat", options={"id" = "boat"})
     * @View()
     */
    public function softremoveBoatAction(Boat $boat)
    {
        $em = $this->getDoctrine()->getManager();

        $boat->setDeleted(true);

        $em->persist($boat);
        $em->flush();

        return $boat;
    }

    /**
     * @Get("/boats/count")
     * @View()
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function countBoatsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('XajErgoBundle:Boat');
        $total = $repo->countNotDeleted();
        $toValid = $total - $repo->countValid();
        $toPay = $total - $repo->countPayed();
        $toRecord = $repo->countNotRecorded();

        return array(
            'totalNotDeleted' => $total,
            'toValid' => $toValid,
            'toPay' => $toPay,
            'toRecord' => $toRecord
        );
    }

    /**
     * @Get("/boats/count/{category}")
     * @View()
     */
    public function countBoatsByCategoryAction($category)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('XajErgoBundle:Boat');
        $total = $repo->countByCategory($category);

        return $total;
    }

    /**
     * @Get("/rowers")
     * @View()
     */
    public function getRowersAction()
    {
        $em = $this->getDoctrine()->getManager();
        $rowers = $em->getRepository('XajErgoBundle:Rower')->findAll();

        return $rowers;
    }

    /**
     * @Get("/rowers/{rower}")
     * @ParamConverter("rower", class="Xaj\ErgoBundle\Entity\Rower", options={"id" = "rower"})
     * @View()
     */
    public function getRowerAction(Rower $rower)
    {
        return $rower;
    }

    /**
     * @Post("/rowers/add")
     * @RequestParam(name="lastname")
     * @RequestParam(name="firstname")
     * @RequestParam(name="license")
     * @RequestParam(name="birthdate")
     * @RequestParam(name="boat")
     * @ParamConverter("boat", class="Xaj\ErgoBundle\Entity\Boat", options={"id" = "boat"})
     * @View()
     */
    public function addRowerAction($lastname, $firstname, $license, $birthdate, Boat $boat)
    {
        $em = $this->getDoctrine()->getManager();

        $rower = new Rower($boat);
        $rower->setLastname($lastname);
        $rower->setFirstname($firstname);
        $rower->setLicense($license);
        $rower->setBirthdate(new \DateTime($birthdate, new \DateTimeZone('Europe/Paris')));

        $em->persist($rower);
        $em->flush();

        return $rower;
    }

    /**
     * @Post("/rowers/{rower}")
     * @ParamConverter("rower", class="Xaj\ErgoBundle\Entity\Rower", options={"id" = "rower"})
     * @RequestParam(name="lastname")
     * @RequestParam(name="firstname")
     * @RequestParam(name="license")
     * @RequestParam(name="birthdate")
     */
    public function editRowerAction(Rower $rower, $lastname = '', $firstname = '', $license = '', $birthdate = '')
    {
        $em = $this->getDoctrine()->getManager();

        if ($lastname != '') {
            $rower->setLastname($lastname);
        }
        if ($firstname != '') {
            $rower->setFirstname($firstname);
        }
        if ($license != '') {
            $rower->setLicense($license);
        }
        if ($birthdate != '') {
            $rower->setBirthdate(new \DateTime($birthdate, new \DateTimeZone('Europe/Paris')));
        }

        $em->persist($rower);
        $em->flush();

        return $rower;
    }

    /**
     * @Delete("/rowers/{rower}")
     * @ParamConverter("rower", class="Xaj\ErgoBundle\Entity\Rower", options={"id" = "rower"})
     * @View()
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function removeRowerAction(Rower $rower)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($rower);
        $em->flush();
    }

    /**
     * @Get("/leaders/{leader}")
     * @ParamConverter("leader", class="Xaj\ErgoBundle\Entity\Leader", options={"id" = "leader"})
     * @View()
     */
    public function getLeaderAction(Leader $leader)
    {
        return $leader;
    }

    /**
     * @Post("/leaders/add")
     * @RequestParam(name="boat")
     * @RequestParam(name="email")
     * @RequestParam(name="phone")
     * @RequestParam(name="club")
     * @RequestParam(name="rower")
     * @ParamConverter("boat", class="Xaj\ErgoBundle\Entity\Boat", options={"id" = "boat"})
     * @ParamConverter("rower", class="Xaj\ErgoBundle\Entity\Rower", options={"id" = "rower"})
     *
     * @View()
     */
    public function addLeaderAction(Rower $rower, $email, $phone, $club = '', Boat $boat)
    {
        $em = $this->getDoctrine()->getManager();

        $leader = new Leader($boat, $rower);
        $leader->setEmail($email);
        $leader->setPhone($phone);
        $leader->setClub($club);

        $em->persist($leader);
        $em->flush();

        return $leader;
    }

    /**
     * @Post("/leaders/{leader}")
     * @ParamConverter("leader", class="Xaj\ErgoBundle\Entity\Leader", options={"id" = "leader"})
     * @RequestParam(name="email")
     * @RequestParam(name="phone")
     * @RequestParam(name="club")
     * @RequestParam(name="lastname")
     * @RequestParam(name="firstname")
     */
    public function editLeaderAction(Leader $leader, $email = '', $phone = '', $club = '', $lastname = '', $firstname = '')
    {
        $em = $this->getDoctrine()->getManager();

        if ($lastname != '' || $firstname != '') {
            editRowerAction($leader->getRower(), $lastname, $firstname);
        }
        if ($email != '') {
            $leader->setEmail($email);
        }
        if ($phone != '') {
            $leader->setPhone($phone);
        }
        if ($club != '') {
            $leader->setClub($club);
        }

        $em->persist($leader);
        $em->flush();

        return $leader;
    }

    /**
     * @Post("/boats/email/{boat}")
     * @ParamConverter("boat", class="Xaj\ErgoBundle\Entity\Boat", options={"id" = "boat"})
     * @View()
     */
    public function sendRecapMailAction(Boat $boat)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('Confirmation de votre inscription à ErgometriX 2014')
                ->setFrom(array('ergometrix@polytechniman.fr' => 'ErgometriX 2014'))
                ->setReplyTo(array('ergometrix2014@gmail.com' => 'ErgometriX 2014'))
                ->setTo($boat->getLeader()->getEmail())
                ->setBody(
                    $this->renderView('XajErgoBundle:Boat:email.html.twig', array('boatName' => $boat->getName())), 'text/html')
                ->addPart(
                    $this->renderView('XajErgoBundle:Boat:email-plaintext.html.twig', array('boatName' => $boat->getName())), 'text/plain')
                ;
        return $this->get('mailer')->send($message);
    }

    /**
     * @Post("/boats/pay-email/{boat}")
     * @ParamConverter("boat", class="Xaj\ErgoBundle\Entity\Boat", options={"id" = "boat"})
     * @View()
     */
    public function sendPayMailAction(Boat $boat)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('Paiement reçu pour ErgometriX 2014')
                ->setFrom(array('ergometrix@polytechniman.fr' => 'ErgometriX 2014'))
                ->setReplyTo(array('ergometrix2014@gmail.com' => 'ErgometriX 2014'))
                ->setTo($boat->getLeader()->getEmail())
                ->setBody(
                    $this->renderView('XajErgoBundle:Boat:pay-email.html.twig', array('boatName' => $boat->getName())), 'text/html')
                ->addPart(
                    $this->renderView('XajErgoBundle:Boat:pay-email-plaintext.html.twig', array('boatName' => $boat->getName())), 'text/plain')
                ;
        $this->get('mailer')->send($message);

        return $message->getBody();
    }
}
?>