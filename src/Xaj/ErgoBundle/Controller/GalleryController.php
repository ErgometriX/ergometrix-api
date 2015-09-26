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

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GalleryController extends FOSRestController
{
    /**
     * @Get("/gallery")
     * @View()
     */
    public function indexAction()
    {
        return $this->get('liip_imagine.cache.manager')->getBrowserPath('bundles/xajergo/media/photos/P5311271.jpg', 'my_thumb');
    }
}
?>