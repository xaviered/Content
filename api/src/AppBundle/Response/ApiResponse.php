<?php

namespace AppBundle\Response;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse extends JsonResponse
{
    public function __construct($data, $currentPage = 0, $totalPages = 1, $headers=[])
    {
        $response = [
            'data' => $data,
            'links' => $this->getLinks($currentPage, $totalPages)
        ];
        parent::__construct($response, 200, $headers);
    }

    private function getLinks($currentPage = 0, $totalPages = 1)
    {
        $request = Request::createFromGlobals();
        $links = [
            'self' => $request->getRequestUri()
        ];
        // has next
        if ($currentPage + 1 < $totalPages) {
            $request->query->set('page', $currentPage + 1);
            // @todo: Will the following call have the updated query params?
            $links['next'] = $request->getRequestUri();
        }
        // has prev
        if ($currentPage - 1 >= 0) {
            if ($currentPage - 1 == 0) {
                $request->query->remove('page');
            } else {
                $request->query->set('page', $currentPage - 1);
            }
            // @todo: Will the following call have the updated query params?
            $links['prev'] = $request->getRequestUri();
        }
        return $links;
    }
}
