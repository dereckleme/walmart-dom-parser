<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

$console = new Application('My Silex Application', 'n/a');
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));
$console->setDispatcher($app['dispatcher']);
$console
    ->register('my-command')
    ->setDefinition(array(
        // new InputOption('some-option', null, InputOption::VALUE_NONE, 'Some help'),
    ))
    ->setDescription('My command description')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        //$swift = new Swift_SmtpTransport("smtp.gmail.com",465, 'ssl');
        //$swift->setUsername("dereckvicentin@gmail.com");
        //$swift->setPassword('741852963drk');

        /*
        $swift = new Swift_SmtpTransport("smtp.globo.com",465, 'ssl');
        $swift->setUsername("dereckleme@globo.com");
        $swift->setPassword('741852963');

        $swift->start();*/

        $connectionParams = array(
            'dbname' => 'crwler',
            'user' => 'root',
            'password' => 'burti9',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        );
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
        $teste = new \Sunra\PhpSimple\HtmlDomParser();
        //$result = $teste::file_get_html("https://www.walmart.com.br/categoria/automotivo/carros/?fq=C:2903/1873/1901/&fq=B:3379&fq=spec_fct_64721:17&fq=spec_fct_92901:225&fq=spec_fct_92902:45&PS=20&mm=100");

        function getProducts($result)
        {
            $titulos = array();
            $oferta = $result->find("li.shelf-product-item");

            foreach ($oferta as $item) {
                $titleResults = $item->find("span.product-title");

                foreach ($titleResults as $titleResult) {
                    $title = trim($titleResult->text());
                    $titulos[$title] = $title;
                }
            }

            return $titulos;
        }

        function getValueKitPneu($result, $titleRequired)
        {
            $oferta = $result->find("li.shelf-product-item");

            foreach ($oferta as $item) {
                $titleResults = $item->find("span.product-title");

                foreach ($titleResults as $titleResult) {
                    $title = trim($titleResult->text());
                    if ($title == $titleRequired) {
                        $quadro = $item->find("div.card-price-container");
                        $valorInt = null;
                        $valorDec = null;

                        foreach ($quadro as $item) {
                            $int = $item->find("span.int");
                            $dec = $item->find("span.dec");

                            foreach ($int as $value) {
                                $valorInt = $value->text();
                            }

                            foreach ($dec as $value) {
                                $valorDec = $value->text();
                            }

                            $valor = str_replace(".", "", trim($valorInt.$valorDec));
                            $valor = str_replace(",", ".", $valor);
                            $valor = str_replace(" ", "", $valor);

                            return  $valor;
                        }
                    }
                }
            }
        }

        function getProdutoId($conn, $valorProduto, $idProduto, $categoriaId) {
            $produto = $conn->query("SELECT * FROM produtos WHERE nome = '{$idProduto}' and categoriaId = '{$categoriaId}' LIMIT 1");
            $produtoValues = $produto->fetch();

            if (!$produtoValues) {
                $conn->exec("INSERT INTO produtos (valor_atual, nome, categoriaId) VALUES ('$valorProduto', '{$idProduto}', '{$categoriaId}');");
                $produto = $conn->query("SELECT * FROM produtos WHERE nome = '{$idProduto}' and categoriaId = '{$categoriaId}' LIMIT 1");
                $produtoValues = $produto->fetch();
                $conn->exec("INSERT INTO request (valor_registrado, id_produto) VALUES ('$valorProduto', '{$produtoValues['id']}');");

                return $produtoValues;
            } else {
                return $produtoValues;
            }
        }

        function check(OutputInterface $output, $conn, $valorProduto, $idProduto, $categoriaId) {
            $produto = getProdutoId($conn, $valorProduto, $idProduto, $categoriaId);

            if ($produto['valor_atual'] != $valorProduto && !empty($produto['valor_atual'])) {
                //atualiza
                $conn->exec("INSERT INTO request (valor_registrado, id_produto) VALUES ('$valorProduto', '{$produto['id']}');");
                $conn->exec("UPDATE produtos SET valor_atual = '{$valorProduto}' WHERE id = '{$produto['id']}'");
            }
        }

	//$result = $teste::file_get_html("https://www.walmart.com.br/categoria/automotivo/carros/?fq=C:2903/1873/1901/&fq=B:3379&fq=spec_fct_64721:17&fq=spec_fct_92901:225&PS=20&mm=100");

        $categorias = array(
            'pneus' => 1,
            'blackfriday' => 2,
            'blackfriday2' => 6,
            'pcgamer' => 3,
            'smartv' => 4,
            'iphone' => 5
        );

        $urls = array(
            array('url' => 'https://www.walmart.com.br/categoria/automotivo/carros/?fq=C:2903/1873/1901/&fq=B:3379&fq=spec_fct_64721:17&fq=spec_fct_92901:225&PS=20&mm=100', 'categoria' => $categorias['pneus']) ,
            array('url' => 'https://www.walmart.com.br/kp/pneus-aro-17?fq=C%3A2903%2F&ft=pneus%20aro%2017&PS=20&PageNumber=1', 'categoria' => $categorias['pneus']) ,
            array('url' => 'https://www.walmart.com.br/kp/pneus-aro-17?fq=C%3A2903%2F&ft=pneus%20aro%2017&PS=20&PageNumber=2', 'categoria' => $categorias['pneus']) ,
            array('url' => 'https://www.walmart.com.br/kp/pneus-aro-17?fq=C%3A2903%2F&ft=pneus%20aro%2017&PS=20&PageNumber=3', 'categoria' => $categorias['pneus']) ,
            array('url' => 'https://www.walmart.com.br/kp/pneus-aro-17?fq=C%3A2903%2F&ft=pneus%20aro%2017&PS=20&PageNumber=4', 'categoria' => $categorias['pneus']) ,
            array('url' => 'https://www.walmart.com.br/especial/blackfriday/esquenta/?fq=H:43443&utmi_p=wm-desktop/home-promo&utmi_cp=-wm-home-controle-191118&utmi_pc=x01-Desktop-home-0-0-0-tv_flash-0-01-EsquentaInst-19118', 'categoria' => $categorias['blackfriday']),
            array('url' => 'https://www.walmart.com.br/especial/blackfriday/ofertas?fq=H:43456&utmi_p=wm-desktop/home-promo&utmi_cp=-wm-home-controle-221118&utmi_pc=x01-Desktop-home-0-0-0-tv_flash-0-01-Black-Friday-221118', 'categoria' => $categorias['blackfriday2']),
            array('url' => 'https://www.walmart.com.br/categoria/informatica/pc-gamers/?fq=C:4699/8230/8247/&fq=spec_fct_103079:Core%20i7&PS=20&mm=100', 'categoria' => $categorias['pcgamer']),
            array('url' => 'https://www.walmart.com.br/categoria/eletronicos/smart-tv/?fq=C:7240/7242/7249/&PS=20&mm=100', 'categoria' => $categorias['smartv']),
            array('url' => 'https://www.walmart.com.br/categoria/telefonia/iphone/?fq=C:4833/8267/&PS=20&mm=100', 'categoria' => $categorias['iphone']),
        );

        foreach ($urls as $dados) {
            $url = $dados['url'];
            $output->writeln("Acessando url: $url \n");
            $x = file_get_contents($url);
            $result = $teste::str_get_html($x);
            //die;
		    //$result = $teste::file_get_html($url);
       		$produtos = getProducts($result);

        	foreach ($produtos as $produto) {
        	    var_dump($produto);
            		$valorProduto = getValueKitPneu($result,$produto);

            		if (!empty($valorProduto)) {
                        check($output, $conn, $valorProduto, $produto, $dados['categoria']);
                    }
              	}
	     }
    })
;

return $console;
