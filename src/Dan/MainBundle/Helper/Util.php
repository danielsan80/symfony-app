<?php

namespace Dan\MainBundle\Helper;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Dan\MainBundle\Service\Config;

class Util
{

    static public function getRootDir()
    {
        return __DIR__ . '/../../../../app';
    }

    static public function getAssetsVersion()
    {
        $config = new Config('parameters');
        return $config->get('assets_version');
    }

    static public function sql($sql)
    {
        $config = new Config('parameters');

        $connection = mysql_connect(
                $config->get('domain.database.host'), $config->get('domain.database.username'), $config->get('database.database.password')
        );
        mysql_select_db($config->get('domain.database.name'), $connection);

        $result = mysql_query($sql, $connection);
        mysql_close($connection);
        if (mysql_error()) {
            throw new \Exception(mysql_error());
        }

        return $result;
    }

    static public function getSwiftMailer()
    {
        $config = new Config('parameters');
        $transport = \Swift_MailTransport::newInstance();
        $mailer = \Swift_Mailer::newInstance($transport);
        return $mailer;
    }

    static public function sendSwiftMessage($message)
    {
        $config = new Config('parameters');

        //Log Email
        $now = new \DateTime();
        $logFilename = self::getRootDir() . "/app/logs/mail-" . $now->format('Y-m-d') . ".log";
        $log = new \Monolog\Logger('Mail');
        $log->pushHandler(new \Monolog\Handler\StreamHandler($logFilename));
        $log->addInfo($message->getTo() . " - " . $message->getSubject());

        $mailer = self::getSwiftMailer();

        return $mailer->send($message);
    }

    static public function getSwiftMessage()
    {
        $config = new Config();
        $message = \Swift_Message::newInstance();
        $message
                ->setFrom(array($config->get('emails.from.address') => $config->get('emails.from.name')))
                ->setSender(array($config->get('emails.from.address') => $config->get('emails.from.name')))
                ->setReturnPath($config->get('emails.from.address'))
        ;
        return $message;
    }

    static public function getTwig($tplPath = null)
    {
        $paths[] = self::getRootDir() . '/../src/Dan/MainBundle/Resources/views';
        if ($tplPath) {
            $paths[] = $tplPath;
        }
        $loader = new \Twig_Loader_Filesystem($paths);
        $twig = new \Twig_Environment($loader, array(
                //'cache' => self::getRootDir().'/app/cache'
        ));

        return $twig;
    }

    static public function log($string)
    {

        $log = new Logger('debug');
        $log->pushHandler(new StreamHandler(self::getRootDir() . '/app/logs/debug.log', Logger::WARNING));

        $log->addWarning($string);
    }

}