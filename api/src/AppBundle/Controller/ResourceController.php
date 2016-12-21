<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\Request;

// @todo: Add all RESTful API actions for a resource of a given type: i.e. game, player, etc
class ResourceController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Get all resources in collection
     *
     * @Route("/v1/{type}", name="resource_list")
     */
    public function cgetAction($type)
    {

    }

    /**
     * Get single resource in collection
     *
     * @Route("/v1/{type}/{slug}", name="resource_view")
     */
    public function getAction($type, $slug)
    {

    }

    /**
     * Create new resource in collection
     *
     * @Route("/v1/{type}", name="resource_view")
     */
    public function postAction(Request $request, $type)
    {

    }

    /**
     * Update existing resource in collection
     *
     * @Route("/v1/{type}/{$slug}", name="resource_view")
     */
    public function putAction(Request $request, $type, $slug)
    {

    }

    /**
     * Alias of putAction
     *
     * @Route("/v1/{type}/{$slug}", name="resource_view")
     */
    public function patchAction(Request $request, $type, $slug)
    {
        return $this->putAction($request, $type, $slug);
    }

    /**
     * Delete a resource from a collection
     *
     * @Route("/v1/{type}/{$slug}", name="resource_view")
     */
    public function deleteAction(Request $request, $type, $slug)
    {

    }
}
