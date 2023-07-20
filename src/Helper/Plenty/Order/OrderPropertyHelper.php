<?php

namespace NetsEasyPay\Helper\Plenty\Order;

use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Authorization\Services\AuthHelper;

class OrderPropertyHelper
{
  /**
   * @param $orderId
   * @param $propertyId
   * @param $propertyValue
   * @return mixed
   */
  public static function updateOrCreateValue($orderId, $propertyId, $propertyValue)
  {

    $auth = pluginApp(AuthHelper::class);
    /** @var OrderRepositoryContract $orderRepository */
    $orderRepository = pluginApp(OrderRepositoryContract::class);

    return $auth->processUnguarded(
      function () use ($orderRepository, $orderId, $propertyId, $propertyValue) {
        return $orderRepository->updateOrder([
          'properties' => [[
              'typeId' => $propertyId,
              'value' => $propertyValue,
          ]]
        ], $orderId);

      }
    );

  }
}
