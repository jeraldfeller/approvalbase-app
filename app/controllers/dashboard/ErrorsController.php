<?php

namespace Aiden\Controllers;

class ErrorsController extends _BaseController {

    public function show404Action() {

        $this->response->setHeader('Content-Type', 'text/plain');

        $error = [
            'error_status' => 404,
            'info' => $this->dispatcher->getParams(),
            'router_rewriteUri' => $this->router->getRewriteUri(),
            'router_matchedRoute' => $this->router->getMatchedRoute(),
            'router_params' => $this->router->getParams(),
            'router_wasMatched' => $this->router->wasMatched(),
        ];

        echo json_encode($error, JSON_PRETTY_PRINT);
        $this->view->disable();

    }

    public function show401Action() {

        $this->response->setHeader('Content-Type', 'text/plain');

        $error = [
            'error_status' => 401,
            'info' => $this->dispatcher->getParams(),
            'router_rewriteUri' => $this->router->getRewriteUri(),
            'router_matchedRoute' => $this->router->getMatchedRoute(),
            'router_params' => $this->router->getParams(),
            'router_wasMatched' => $this->router->wasMatched(),
        ];

        echo json_encode($error, JSON_PRETTY_PRINT);
        $this->view->disable();

    }

}
