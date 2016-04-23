#!/usr/bin/php
<?php

error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
ini_set('display_errors', 1);

$cronStartTime = time();
$cronClassName = '';
$exitCode = 0;

$out = function($string) use ($cronClassName) {
    echo date('r') . "[$cronClassName] $string" . PHP_EOL;
};

$out("Started");

if (PHP_SAPI != "cli") {
    $out("Command line only.");
    $exitCode = 1;
} else if ($_SERVER['argc']<2) {
    $out("Cron job class name must be passed as 1st command line argument.");
    $exitCode = 2;
} else {
    $cronClassName = $_SERVER['argv']['1'];
    $cronClassFilespec = __DIR__. $cronClassName . '.php';
    if (!file_exists($cronClassFilespec)) {
        $out("Could not find cron job $cronClassFilespec");
        $exitCode = 3;
    }
    else {
        require_once __DIR__.'/../inc/connect.php';
        try {
            require($cronClassFilespec);
            if (!class_exists($cronClassName)) {
                $out("Can't find job class in $cronClassFilespec.");
                $exitCode = 4;
            }
            else {
                $job = new $cronClassName;
                if ($job instanceof CronJobAbstract) {
                    $status = $registry->get('cron_status_root') . $cronClassName . 'IN_PROGRESS';
                    if (file_exists($status)) {
                        $ft = filemtime($status);
                        if ($ft < mktime(date("H"), date("i")-$job->getTTL(), date("s"), date("n") , date("j"), date("Y"))) {
                            unlink($status);
                            $out("Removed old semaphore $status created on ".date('r', $ft));
                        }
                        else {
                            $out("Another copy of $cronClassName is in progress");
                            $exitCode = 5;
                        }
                        if (!$exitCode) {
                            $sh = fopen($status, 'w'); fclose($sh);
                            $job->execute();
                            unlink($status);
                        }
                    }
                }
                else {
                    $out("Cron job class should extend CronJobAbstract.");
                    $exitCode = 6;
                }
            }
        } catch (Exception $e) {
            //do what you want with the exception, for now just print it
            $exitCode = $e->getCode();
            echo $e->getMessage() . PHP_EOL;
            echo $e->getTraceAsString();
        }
    }
}

$cronEndTime = time();
$out('Finished. Took '.($cronEndTime - $cronStartTime).' seconds '.PHP_EOL);
exit($exitCode);

