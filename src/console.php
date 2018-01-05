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
        $swift = new Swift_SmtpTransport("smtp.gmail.com",465, 'ssl');
        $swift->setUsername("dereckvicentin@gmail.com");
        $swift->setPassword('741852963drk');

        $swift->start();


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

        function check($swift, $conn, $valorProduto, $idProduto) {
            $resultQuery = $conn->query("SELECT value FROM request WHERE produto = '{$idProduto}' order by value asc LIMIT 1");
            $fetch = $resultQuery->fetchColumn();
            $precoAtual = $conn->query("SELECT preco FROM preco_atual WHERE produto = '{$idProduto}' LIMIT 1");
            $precoAtualValue = $precoAtual->fetchColumn();

            if ($precoAtualValue && $valorProduto != $precoAtualValue) {
                $message = (new \Swift_Message($idProduto))
                    ->setFrom('dereckleme@globo.com')
		    ->setBcc('procaioviana@gmail.com')
                    ->addBcc('dereckleme@globo.com')
		    ->addBcc('genival.eloi@gmail.com')
                    ->addBcc('re2006gomes@gmail.com')
                    ->addBcc('renato_since93@hotmail.com')
                    ->addBcc('rd-lv@hotmail.com')
                    ->addBcc('serghi1@msn.com')
                    ->addBcc('eltooonrs@gmail.com')
                    ->addBcc('leandronacacio@gmail.com')
                    ->addBcc('Alexandre@visareengenharia.com.br')
                    ->addBcc('freitasmoreiralucas@gmail.com')
                    ->addBcc('ogabriel@mail.com')
                    ->addBcc('marcelosalvado73@gmail.com')
                    ->setTo('dereckvicentin@gmail.com')
                    ->setBody("Altero o valor: R$$valorProduto")
                    ->setContentType('text/html');
                ;


                $swift->send($message);

                $conn->exec("UPDATE preco_atual SET preco = '{$valorProduto}' WHERE produto = '{$idProduto}'");
            } elseif (!$precoAtualValue) {
                $conn->exec("INSERT INTO preco_atual (preco, produto) VALUES ('$valorProduto', '{$idProduto}');");
            }

            if (!$fetch) {
                $conn->exec("INSERT INTO request (value, produto) VALUES ('$valorProduto', '{$idProduto}');");
            } elseif ($valorProduto < $fetch) {
                $conn->exec("INSERT INTO request (value, produto) VALUES ('$valorProduto', '{$idProduto}');");

                $message = (new \Swift_Message($idProduto))
                    ->setFrom('dereckvicentin@gmail.com')
                    ->setBcc('procaioviana@gmail.com')
                    ->addBcc('dereckleme@globo.com')
                    ->addBcc('genival.eloi@gmail.com')
		    ->addBcc('re2006gomes@gmail.com')
		    ->addBcc('renato_since93@hotmail.com')
		    ->addBcc('rd-lv@hotmail.com')
		    ->addBcc('serghi1@msn.com')
		    ->addBcc('eltooonrs@gmail.com')
		    ->addBcc('leandronacacio@gmail.com')
		    ->addBcc('Alexandre@visareengenharia.com.br')
		    ->addBcc('freitasmoreiralucas@gmail.com')
		    ->addBcc('ogabriel@mail.com')
                    ->addBcc('marcelosalvado73@gmail.com')
                    ->setTo('dereckvicentin@gmail.com')
                    ->setBody("Baixo: R$$valorProduto")
                    ->setContentType('text/html');
                ;

           
                $swift->send($message);
	     }
        }



	//$result = $teste::file_get_html("https://www.walmart.com.br/categoria/automotivo/carros/?fq=C:2903/1873/1901/&fq=B:3379&fq=spec_fct_64721:17&fq=spec_fct_92901:225&PS=20&mm=100");

	for($i=1; $i<=12; $i++) { 
		$result = $teste::file_get_html("https://www.walmart.com.br/categoria/automotivo/carros/?fq=C:2903/1873/1901/&fq=B:3379--3077--1067&fq=spec_fct_64721:17--16&fq=spec_fct_92901:225&PS=20&mm=100&PageNumber={$i}");

       		 $produtos = getProducts($result);

        	foreach ($produtos as $produto) {
            		$valorProduto = getValueKitPneu($result,$produto);

            		if ($valorProduto != null) {
                	check($swift, $conn, $valorProduto, $produto);
            		}
              	}
	     }
    })
;

return $console;