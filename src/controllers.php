<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/', function () use ($app) {
    $connectionParams = array(
        'dbname' => 'crwler',
        'user' => 'root',
        'password' => 'burti9',
        'host' => 'localhost',
        'driver' => 'pdo_mysql',
    );
    $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
    $precoAtual = $conn->query("SELECT * FROM preco_atual order by preco desc");
    $list = $precoAtual->fetchAll();
    $forList = array();

    foreach ($list as $item) {
        $precoAtual = $conn->query("SELECT * FROM request WHERE produto = '{$item['produto']}' order by data desc");
        $results = $precoAtual->fetchAll();

        $forList[] = array("produtoData" => $item, "produtoRequests" => $results);
    }

    return $app['twig']->render('index.html.twig', array("list" => $forList));
})
->bind('homepage')
;

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
