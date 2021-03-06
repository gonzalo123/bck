<?php
namespace G\Bck;

use Psr\Log\LoggerInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class App
{
    private $app;
    private $conf;
    private $validator;
    private $authController;
    private $logger;
    private $logErrorMessage;
    private $logOKMessage;

    public function __construct(Application $app, Auth\CredentialsValidatorIface $validator, Auth\Controller $authController, LoggerInterface $logger)
    {
        $this->app            = $app;
        $this->validator      = $validator;
        $this->authController = $authController;
        $this->logger         = $logger;
    }

    public function setConf($confPath)
    {
        $conf = [];
        $list = json_decode(file_get_contents($confPath), true);
        foreach ($list as $name => $folder) {
            $conf[$name] = json_decode(file_get_contents(dirname($confPath) . "/{$folder}"), true);
        }
        $this->conf = $conf;
    }

    public function run(Request $request)
    {
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $transformedRequest = json_decode($request->getContent(), true);
            $request->request->replace($transformedRequest);
        }

        $pathInfo     = $request->getPathInfo();
        $pathInfoData = explode("/", $pathInfo);
        $path         = $pathInfoData[1];

        $this->app->mount('/auth', $this->authController->getControllerFactory());
        if (!$this->authController->isValidRoute($request)) {
            $mountConf = $this->conf[$request->get('_a')];
            $this->setUpAppValues($mountConf);
        } else {
            /** @var ControllerCollection $route */
            $route     = $this->app['controllers_factory'];
            $mountConf = $this->conf[$path];
            $this->setUpAppValues($mountConf);

            foreach (array_keys($mountConf['routes']) as $routeKey) {
                list($httpMethod, $routePath) = explode("::", $routeKey);
                $route->match($routePath, function (Request $request) use ($routePath, $mountConf) {
                    /** @var BuilderIface $builder */
                    $builder = new $mountConf['builder']($request, $this->app);
                    $builder->preFetch();
                    $builder->setRoutes($mountConf['routes']);
                    $builder->init($routePath);

                    $data = $builder->fetch();
                    switch ($builder->getType()) {
                        case 'raw':
                            return $data;
                        case 'stream':
                            $headers = [
                                'pdf'  => ['Content-Type' => 'application/pdf'],
                                'js'   => ['Content-Type' => 'application/javascript'],
                                'css'  => ['Content-Type' => 'text/css'],
                                'html' => ['Content-Type' => 'text/html; charset=UTF-8'],
                                'jpg'  => ['Content-Type' => 'image/jpeg'],
                            ];

                            $stream = function () use ($data) {
                                echo $data;
                            };

                            return $this->app->stream($stream, 200, $headers[$builder->getSubType()]);
                        default:
                            return $this->app->json($data);
                    }
                })
                      ->method(strtoupper($httpMethod))
                      ->before(function (Request $request) use ($mountConf, $pathInfo) {
                          $requestToken = $request->get('_t');
                          $token        = $this->validator->getDecodedToken($requestToken, $mountConf['secret']);
                          $appName      = $mountConf['appName'];
                          $user         = isset($this->app['user']) ? $this->app['user'] : 'unknown';

                          if ($token !== false) {
                              $user = $token->user;
                              if ($token->appName != $appName) {
                                  $this->logErrorMessage = "{$appName}::{$pathInfo}::{$user}::Access Denied. Wrong app in token";
                                  throw new AccessDeniedHttpException("Access Denied. Wrong app in token");
                              }
                              $serverVersion = $mountConf['version'];
                              $clientVersion = $request->get('_v');

                              if ($clientVersion == $serverVersion) {
                                  $this->app['user'] = $user;
                              } else {
                                  $this->logErrorMessage = "{$appName}::{$pathInfo}::{$user}::Wrong app version. client: {$clientVersion} server: {$serverVersion}";
                                  throw new HttpException(412, "Wrong version");
                              }
                          } else {
                              $this->logErrorMessage = "{$appName}::{$pathInfo}::{$user}::Wrong Token: {$requestToken}";
                              throw new AccessDeniedHttpException("Access Denied");
                          }
                          $this->logOKMessage = "{$appName}::{$user}::Wrong app version. client: {$clientVersion} server: {$serverVersion}";
                      });
            }

            $this->app->mount($path, $route);
        }

        $this->app->finish(function (Request $request, Response $response, Application $app) {
            if ($this->logErrorMessage) {
                $this->logger->error($this->logErrorMessage);
            }
            if ($this->logOKMessage) {
                $this->logger->info($this->logOKMessage);
            }
        });

        $this->app->run($request);
    }

    private function setUpAppValues($mountConf)
    {
        $this->app['version']                 = $mountConf['version'];
        $this->app['appName']                 = $mountConf['appName'];
        $this->app['twoFactorAuthentication'] = $mountConf['twoFactorAuthentication'];
        $this->app['builder']                 = $mountConf['builder'];

        $this->validator->setUp([
            'secret'         => $mountConf['secret'],
            'preTokeExpires' => $mountConf['preTokeExpires'],
            'tokeExpires'    => $mountConf['tokeExpires'],
            'allowedTries'   => $mountConf['allowedTries'],
        ]);
    }
}