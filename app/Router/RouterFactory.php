<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$routeList = new RouteList;
		$routeList->addRoute('<presenter>/<action>[/<id>]', 'ImportDoiMain:default');

		return $routeList;
	}
}
