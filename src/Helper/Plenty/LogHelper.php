<?php

namespace NetsEasyPay\Helper\Plenty;

use Plenty\Plugin\Log\Loggable;

class LogHelper
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
  public static function report($identifier, $message, $value, $referenceType = '', $referenceValue = 0)
  {
    $logHelper = pluginApp(self::class);

    if ($referenceType === 0 || $referenceValue === '') {
      return $logHelper->getLogger($identifier)->report($message, $value);
    }

    return $logHelper->getLogger($identifier)->addReference($referenceType, $referenceValue)->report($message, $value);
  }

  /**
   * @param string $identifier
   * @param string $message
   * @param string $value
   * @param string $referenceType
   * @param int $referenceValue
   * @return mixed
   */
  public static function debug($identifier, $message, $value, $referenceType = '', $referenceValue = 0)
  {
    $logHelper = pluginApp(self::class);

    if ($referenceType === 0 || $referenceValue === '') {
      return $logHelper->getLogger($identifier)->debug($message, $value);
    }

    return $logHelper->getLogger($identifier)->addReference($referenceType, $referenceValue)->debug($message, $value);
  }

  /**
   * @param string $identifier
   * @param string $message
   * @param string $value
   * @param string $referenceType
   * @param int $referenceValue
   * @return mixed
   */
  public static function info($identifier, $message, $value, $referenceType = '', $referenceValue = 0)
  {
    $logHelper = pluginApp(self::class);

    if ($referenceType === 0 || $referenceValue === '') {
      return $logHelper->getLogger($identifier)->info($message, $value);
    }

    return $logHelper->getLogger($identifier)->addReference($referenceType, $referenceValue)->info($message, $value);
  }

  /**
   * @param string $identifier
   * @param string $message
   * @param string $value
   * @param string $referenceType
   * @param int $referenceValue
   * @return mixed
   */
  public static function warning($identifier, $message, $value, $referenceType = '', $referenceValue = 0)
  {
    $logHelper = pluginApp(self::class);

    if ($referenceType === 0 || $referenceValue === '') {
      return $logHelper->getLogger($identifier)->warning($message, $value);
    }

    return $logHelper->getLogger($identifier)->addReference($referenceType, $referenceValue)->warning($message, $value);
  }

  /**
   * @param string $identifier
   * @param string $message
   * @param string $value
   * @param string $referenceType
   * @param int $referenceValue
   * @return mixed
   */
  public static function error($identifier, $message, $value, $referenceType = '', $referenceValue = 0)
  {
    $logHelper = pluginApp(self::class);

    if ($referenceType === 0 || $referenceValue === '') {
      return $logHelper->getLogger($identifier)->error($message, $value);
    }

    return $logHelper->getLogger($identifier)->addReference($referenceType, $referenceValue)->error($message, $value);
  }
}
