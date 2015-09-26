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
use Xaj\ErgoBundle\Entity\Ranking;
use Xaj\ErgoBundle\Entity\Category;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class RankController extends FOSRestController
{
    /**
     * @Get("/rankings")
     * @View()
     */
    public function getRankingsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $rankings = $em->getRepository('XajErgoBundle:Ranking')->findAll();

        return $rankings;
    }

    /**
     * @Get("/rankings/check-display")
     * @View()
     */
    public function checkDisplayAction()
    {
        $file = fopen(__DIR__.'/../../../../app/var/results.txt', 'r+');
        $res = fgets($file);
        $res = ($res == "0") ? 0 : 1;
        fclose($file);
        return array('check' => $res);
    }

    /**
     * @Get("/rankings/{ranking}")
     * @ParamConverter("ranking", class="Xaj\ErgoBundle\Entity\Ranking", options={"id" = "ranking"})
     * @View()
     */
    public function getRankingAction(Ranking $ranking)
    {
        return $ranking;
    }

    /**
     * @Post("/rankings/switch-display")
     * @View()
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function switchDisplayAction()
    {
        $file = fopen(__DIR__.'/../../../../app/var/results.txt', 'r+');
        $res = fgets($file);
        fseek($file, 0);
        if ($res == 0) {
            fputs($file, '1');
        } else {
            fputs($file, '0');
        }
        fclose($file);
        $res = ($res == "0") ? 1 : 0;
        return $res;
    }

    /**
     * @Post("/rankings/add")
     * @RequestParam(name="name")
     * @RequestParam(name="categoriesString")
     * @View()
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function addRankingAction($name, $categoriesString)
    {
        $em = $this->getDoctrine()->getManager();
        $repoCat = $em->getRepository('XajErgoBundle:Category');
        $repoRank = $em->getRepository('XajErgoBundle:Ranking');

        if ($categoriesString == '') {
            throw new HttpException(403, 'Il faut au moins une catégorie dans le classement.');
        }
        
        $categories = explode(',', $categoriesString);

        $ranking = new Ranking();
        $ranking->setName($name);
        foreach ($categories as $cat) {
            $category = $repoCat->findOneByCode($cat);
            $ranking->addCategory($category);
        }

        $em->persist($ranking);
        $em->flush();

        return $ranking;
    }

    /**
     * @Post("/rankings/{ranking}")
     * @ParamConverter("ranking", class="Xaj\ErgoBundle\Entity\Ranking", options={"id" = "ranking"})
     * @RequestParam(name="name")
     * @RequestParam(name="categoriesString")
     * @View()
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function editRankingAction(Ranking $ranking, $categoriesString = '', $name = '')
    {
        $em = $this->getDoctrine()->getManager();
        $repoCat = $em->getRepository('XajErgoBundle:Category');
        $repoRank = $em->getRepository('XajErgoBundle:Ranking');

        if ($categoriesString == '' && $name == '') {
            throw new HttpException(403, 'Il faut effectuer au moins un changement.');
        }

        if ($name != '') {
            $ranking->setName($name);
        }
        if ($categoriesString != '') {
            $categories = explode(',', $categoriesString);
            foreach ($ranking->getCategories() as $cat) {
                $ranking->removeCategory($cat);
            }
            foreach ($categories as $cat) {
                $category = $repoCat->findOneByCode($cat);
                $ranking->addCategory($category);
            }
        }
        
        $em->persist($ranking);
        $em->flush();

        return $ranking;
    }

    /**
     * @Delete("/rankings/{ranking}")
     * @ParamConverter("ranking", class="Xaj\ErgoBundle\Entity\Ranking", options={"id" = "ranking"})
     * @View()
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteRankingAction(Ranking $ranking)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($ranking);
        $em->flush();
    }
}
?>