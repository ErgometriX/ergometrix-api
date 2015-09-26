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

use Xaj\ErgoBundle\Entity\User;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class UserController extends FOSRestController
{
    /**
     * @Get("/users")
     * @View()
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function getUsersAction()
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('XajErgoBundle:User')->findAll();

        return $users;
    }

    /**
     * @Get("/users/{user}")
     * @ParamConverter("user", class="Xaj\ErgoBundle\Entity\User", options={"id" = "user"})
     * @View()
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function getUserAction(User $user)
    {
        return $user;
    }

    /**
     * @Post("/users/add")
     * @RequestParam(name="lastname")
     * @RequestParam(name="firstname")
     * @RequestParam(name="login")
     * @RequestParam(name="password")
     * @RequestParam(name="email")
     *
     * @View()
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function addUserAction($lastname, $firstname, $login, $email, $password)
    {
        $em = $this->getDoctrine()->getManager();
        $user = new User();
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);

        $user->setLastname($lastname);
        $user->setFirstname($firstname);
        $user->setLogin($login);
        $user->setEmail($email);
        $pwd = $encoder->encodePassword($password, $user->getSalt());
        $user->setPassword($pwd);

        $em->persist($user);
        $em->flush();

        $user->setPassword('');
        $user->setSalt('');

        return $user;
    }

    /**
     * @Post("/users/changepwd/{user}")
     * @RequestParam(name="oldpwd")
     * @RequestParam(name="newpwd")
     * @ParamConverter("user", class="Xaj\ErgoBundle\Entity\User", options={"id" = "user"})
     * 
     * @View()
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function changePwdAction(User $user, $oldpwd, $newpwd)
    {
        $em = $this->getDoctrine()->getManager();
        $pwdEncoder = $this->get('security.encoder_factory')->getEncoder($user);
        $valid = $pwdEncoder->isPasswordValid(
                $user->getPassword(), 
                $oldpwd, 
                $user->getSalt()
            );

        if(!$valid) {
            throw new UnauthorizedHttpException('Bad credentials');
        } else {
            $pwd = $pwdEncoder->encodePassword($newpwd, $user->getSalt());
            $user->setPassword($pwd);

            $em->persist($user);
            $em->flush();

            $user->setPassword('');
            $user->setSalt('');

            return $user;
        }
    }

    /**
     * @Delete("/users/{user}")
     * @ParamConverter("user", class="Xaj\ErgoBundle\Entity\User", options={"id" = "user"})
     * @View()
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function removeUserAction(User $user)
    {
        $currentUser = $this->getUser();
        
        if ($user->getLogin() == $currentUser->getLogin()) {
            throw new HttpException(403, 'Impossible de supprimer son propre compte !');
        }
        if ($user->getLogin() == 'admin') {
            throw new HttpException(403, 'Vous ne pouvez pas me supprimer... ;p');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
    }
}
?>