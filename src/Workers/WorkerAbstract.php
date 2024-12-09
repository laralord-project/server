<?php

namespace Server\Workers;

use OpenSwoole\{Http\Request, Http\Response, Http\Server, Process};
use Server\Log;
use Server\Traits\ProjectClassWrapper;
use Swoole\Process\Pool;

abstract class WorkerAbstract
{
    use ProjectClassWrapper;

    /**
     * @var string|mixed
     */
    protected string $basePath;

    /**
     * @var string
     */
    protected string $name = "laralord:worker";

    /**
     * @var int
     */
    protected int $workerId;

    /**
     * @var Server|Pool|Process
     */
    protected Server|Pool|Process $server;

    /**
     * @var
     */
    protected $app;

    static string $bootstrapCode;

    static string $healthCheckEndpoint = '/';


    abstract public function getInitHandler(): \Closure;


    abstract public function getRequestHandler(): \Closure;


    protected function initWorker($server, $workerId)
    {
        $this->server = $server;
        $this->workerId = $workerId;

        cli_set_process_title("{$this->name}-$workerId");
        Log::$logger = Log::$logger->withName("{$this->name}-$workerId");
        require_once "{$this->basePath}/vendor/autoload.php";
    }


    public function warmUp()
    {
        try {
            if (!empty($this->env)) {
                $_ENV = $this->env->env();
            }

            $kernel = $this->app->make(
                $this->getProjectClass("@Contracts\\Http\\Kernel")
            );

            $symfonyRequestClass = $this->getProjectClass('@Http\\Request');
            $server = [
                'REQUEST_URI' => self::$healthCheckEndpoint,
            ];

            $symfonyResponse = $kernel->handle(
                new $symfonyRequestClass([], [], [], [], [], $server, '')
            );

            Log::debug('Application wormed up with code: ', [
                'status'  => $symfonyResponse->getStatusCode(),
                'content' => $symfonyResponse->getContent(),
            ]);
        } catch (\Throwable $e) {
            Log::error($e);
        }
    }


    public function cleanMemory(): void
    {
        unset($server);
        unset($GLOBALS['cli']);
    }


    public function isolate(\Closure $action, int $exitSignal = \SIGKILL, bool $wait = true): int
    {
        $childPid = \pcntl_fork();

        if ($childPid < 0) {
            throw new \Exception("Failed to create a fork of the process to isolate action");
        }

        // executing closure method on new created work
        if ($childPid === 0) {
            cli_set_process_title(\cli_get_process_title().':fork');
            try {
                $action();
            } catch (\Throwable $e) {
                Log::error($e);
            }
            finally {
                Process::kill(\getmypid(), $exitSignal);
                throw new \Exception("The child process".\getmypid()." didn't exit");
            }
        }

        if (!$wait) {
            return $childPid;
        }

        \pcntl_wait($status);

        return $status;
    }


    public function handleRequest(array $requestData, $response)
    {
        $app = $this->app;

        $response = Response::create(\strval($response));

        $symfonyRequestClass = $this->getProjectClass('@Http\\Request');
        $request = new $symfonyRequestClass(...$requestData);

        if ($app->hasBeenBootstrapped()) {
            Log::debug("App is bootstrapped");
        } else {
            Log::debug("App hasn\'t been bootstrapped");
        }

        $kernel = $app->make($this->getProjectClass("@Contracts\\Http\\Kernel"));
        $symfonyResponse = $kernel->handle($request);

        $this->respond($symfonyResponse, $response);
        $kernel->terminate($request, $symfonyResponse);
    }


    /**
     * @param            $symfonyResponse
     * @param  Response  $response
     *
     * @return void
     */
    protected function respond($symfonyResponse, Response $response)
    {
        // Set status code
        $response->status($symfonyResponse->getStatusCode());

        // Set headers
        foreach ($symfonyResponse->headers->allPreserveCaseWithoutCookies() as $name => $values) {
            foreach ($values as $value) {
                $response->header($name, $value);
            }
        }

        // Set cookies
        foreach ($symfonyResponse->headers->getCookies() as $cookie) {
            $response->cookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpiresTime(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->isSecure(),
                $cookie->isHttpOnly(),
                $cookie->getSameSite()
            );
        }

        // Set content
        $response->end($symfonyResponse->getContent());
    }


    public function createApplication()
    {
        // if (!empty(self::$bootstrapCode)) {
        //     Log::debug('loaded from bootstrap code');
        //     return $this->app = eval(self::$bootstrapCode);
        // }

        Log::debug('required app.php');
        $this->app = require "{$this->basePath}/bootstrap/app.php";
    }


    /**
     * Preload the bootstrap code to memory to reduce the number of disk operations
     *
     * @param  string  $basePath
     * @param          $force
     *
     * @return false|void
     */
    public static function loadBoostrapCode(string $basePath, $force = false)
    {
        if (isset(self::$bootstrapCode) && !$force) {
            return false;
        }

        $bootstrapCode = \file_get_contents("{$basePath}/bootstrap/app.php");
        self::$bootstrapCode = \str_replace('<?php', '', $bootstrapCode);
    }


    /**
     * @param  Request  $request
     *
     * @return @\Illuminate\Http\Request
     */
    public function transformRequest(Request $request)
    {
        $server = $this->transformServerParameters($request->server, $request->header);

        // fill the global variables
        $_GET = $request->get ?: [];
        $_POST = $request->post ?: [];
        // Set other global variables
        $_FILES = $swooleRequest->files ?? [];
        $_COOKIE = $swooleRequest->cookie ?? [];

        $symfonyRequestClass = $this->getProjectClass("@Component\\HttpFoundation\\Request", "Symfony");
        // swoole doesn't generate php://input stream for request - using the rawContent
        // to create Symfony and Illuminate Requests
        $symfonyRequest = new $symfonyRequestClass(
            $request->get ?? [],
            $request->post ?? [],
            [],
            $request->cookie ?? [],
            $request->files ?? [],
            $server,
            $request->rawContent(),
        );

        $illuminateRequestClass = $this->getProjectClass("@Http\\Request");
        $illuminateRequestClass::enableHttpMethodParameterOverride();

        return $illuminateRequestClass::createFromBase($symfonyRequest);
    }


    /**
     * Transforms $_SERVER array.
     *
     * @param  array  $server
     * @param  array  $header
     *
     * @return array
     */
    protected function transformServerParameters(array $server, array $header)
    {
        foreach ($server as $key => $value) {
            $key = strtoupper($key);
            $_SERVER[$key] = $value;
        }

        // Define the list of headers that shouldn't have the 'HTTP_' prefix
        $nonPrefixedHeaders = ['CONTENT_TYPE', 'CONTENT_LENGTH', 'HTTPS', 'REMOTE_ADDR', 'SERVER_PORT'];

        // Now process headers
        foreach ($header as $key => $value) {
            $key = str_replace('-', '_', $key);
            $key = strtoupper($key);

            // Only add 'HTTP_' prefix for headers not in the list of non-prefixed headers
            if (in_array($key, $nonPrefixedHeaders)) {
                $_SERVER[$key] = $value;
            }

            $key = 'HTTP_'.$key;

            // Set the header in the $_SERVER superglobal
            $_SERVER[$key] = $value;
        }

        return $_SERVER;
    }
}
