<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/', function (Request $request) use ($app) {
    $connectionParams = array(
        'dbname' => 'crwler',
        'user' => 'root',
        'password' => 'burti9',
        'host' => 'localhost',
        'driver' => 'pdo_mysql',
    );
    $categoriaSetted = $request->query->get("categoria");
    $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);

    if ($categoriaSetted) {
        $precoAtual = $conn->query("SELECT * FROM produtos WHERE categoriaId = {$categoriaSetted} order by valor_atual desc");
    } else {
        $precoAtual = $conn->query("SELECT * FROM produtos order by valor_atual desc");
    }
    $list = $precoAtual->fetchAll();
    $forList = array();

    foreach ($list as $item) {
        $precoAtual = $conn->query("SELECT * FROM request INNER JOIN produtos ON request.id_produto = produtos.id WHERE id_produto = '{$item['id']}' order by data asc");
        $results = $precoAtual->fetchAll();
        $ocorrencias = (isset($_GET['ocorrencias'])) ? $_GET['ocorrencias'] : null;

        if ($ocorrencias) {
            if (count($results) > 1) {
                $forList[] = array("produtoData" => $item, "produtoRequests" => $results);
            }
        } else {
            $forList[] = array("produtoData" => $item, "produtoRequests" => $results);
        }
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
