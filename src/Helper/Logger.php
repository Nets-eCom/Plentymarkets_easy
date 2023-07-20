<?php

namespace NetsEasyPay\Helper;

use Plenty\Plugin\Log\Loggable;

class Logger
{

    use Loggable;

    /**
     * @param string $identifier
     * @param string $message
     * @param string $value
     * @param string $referenceType
     * @param int $referenceValue
     * @return mixed
     */
    public static function report($identifier, $message, $value, string $referenceType = '', int $referenceValue = 0)
    {
        /** @var Logger $logger */
        $logger = pluginApp(Logger::class);

        if ($referenceType === 0 || $referenceValue === '') {
            return $logger->getLogger($identifier)->error($message, $value);
        }

        return $logger->getLogger($identifier)->addReference($referenceType, $referenceValue)->report($message, $value);
    }

    /**
     * @param string $identifier
     * @param string $message
     * @param string $value
     * @param string $referenceType
     * @param int $referenceValue
     * @return mixed
     */
    public static function debug($identifier, $message, $value, string $referenceType = '', int $referenceValue = 0)
    {
        /** @var Logger $logger */
        $logger = pluginApp(Logger::class);

        if ($referenceType === 0 || $referenceValue === '') {
            return $logger->getLogger($identifier)->error($message, $value);
        }

        return $logger->getLogger($identifier)->addReference($referenceType, $referenceValue)->debug($message, $value);
    }

    /**
     * @param string $identifier
     * @param string $message
     * @param string $value
     * @param string $referenceType
     * @param int $referenceValue
     * @return mixed
     */
    public static function info($identifier, $message, $value, string $referenceType = '', int $referenceValue = 0)
    {
        /** @var Logger $logger */
        $logger = pluginApp(Logger::class);

        if ($referenceType === 0 || $referenceValue === '') {
            return $logger->getLogger($identifier)->error($message, $value);
        }

        return $logger->getLogger($identifier)->addReference($referenceType, $referenceValue)->info($message, $value);
    }

    /**
     * @param string $identifier
     * @param string $message
     * @param string $value
     * @param string $referenceType
     * @param int $referenceValue
     * @return mixed
     */
    public static function warning($identifier, $message, $value, string $referenceType = '', int $referenceValue = 0)
    {
        /** @var Logger $logger */
        $logger = pluginApp(Logger::class);

        if ($referenceType === 0 || $referenceValue === '') {
            return $logger->getLogger($identifier)->error($message, $value);
        }

        return $logger->getLogger($identifier)->addReference($referenceType, $referenceValue)->warning($message, $value);
    }

    /**
     * @param string $identifier
     * @param string $message
     * @param string $value
     * @param string $referenceType
     * @param int $referenceValue
     * @return mixed
     */
    public static function error($identifier, $message, $value, string $referenceType = '', int $referenceValue = 0)
    {
        /** @var Logger $logger */
        $logger = pluginApp(Logger::class);

        if ($referenceType === 0 || $referenceValue === '') {
            return $logger->getLogger($identifier)->error($message, $value);
        }

        return $logger->getLogger($identifier)->addReference($referenceType, $referenceValue)->error($message, $value);
    }
/**
     * @param string $identifier
     * @param string $message
     * @param string $value
     * @param string $referenceType
     * @param int $referenceValue
     * @return mixed
     */

    // Temp by Kazim
    public static function errorNew($identifier, $message, $value, $referenceType, $referenceValue)
    {
        /** @var Logger $logger */
        $logger = pluginApp(Logger::class);



        return $logger->getLogger($identifier)->setReferenceType($referenceType)->setReferenceValue($referenceValue)->error($message, $value);
    }

    // Temp by Kazim
    public static function errorWithRef($identifikator, $message,  $value, $refType, $refValue)
    {
        $logger = pluginApp(Logger::class);

        $logger->getLogger($identifikator)
            ->addReference($refType, $refValue)
            ->error($message, $value);
         }
    /**
     * @param string $identifier
     * @param string $message
     * @param string $value
     * @param string $referenceType
     * @param int $referenceValue
     * @return mixed
     */
    public static function critical($identifier, $message, $value, string $referenceType = '', int $referenceValue = 0)
    {
        /** @var Logger $logger */
        $logger = pluginApp(Logger::class);

        if ($referenceType === 0 || $referenceValue === '') {
            return $logger->getLogger($identifier)->critical($message, $value);
        }

        return $logger->getLogger($identifier)->addReference($referenceType, $referenceValue)->critical($message, $value);
    }
}
