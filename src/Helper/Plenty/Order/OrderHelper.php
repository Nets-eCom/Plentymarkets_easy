<?php

namespace NetsEasyPay\Helper\Plenty\Order;


use Plenty\Modules\Account\Address\Models\Address as PlentyAddress;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Order\Address\Contracts\OrderAddressRepositoryContract;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\OrderType;
use Plenty\Modules\Order\Models\Order as PlentyOrder;
use Plenty\Modules\Order\Models\OrderReference;
use Plenty\Modules\Order\Property\Models\OrderProperty as PlentyOrderProperty;
use Plenty\Modules\Order\Shipping\Package\Contracts\OrderShippingPackageRepositoryContract;
use Plenty\Modules\Order\Shipping\Package\Models\OrderShippingPackage;
use Plenty\Modules\Order\CreditNote\Contracts\CreditNoteRepositoryContract;

class OrderHelper
{

  /**
   * @param int $id
   * @return PlentyOrder
   */
  public static function find(int $id)
  {
    /** @var AuthHelper $auth */
        $auth = pluginApp(AuthHelper::class);
  
    /** @var OrderRepositoryContract $orderRepositoryContract */
    $orderRepositoryContract = pluginApp(OrderRepositoryContract::class);

    return $auth->processUnguarded(
      function () use ($orderRepositoryContract, $id) {
        return $orderRepositoryContract->findOrderById($id);
      }
    );
   
  }

  /**
   * @param PlentyOrder $order
   * @param $propertyTypeId
   * @return PlentyOrderProperty[]
   */
  public static function getPropertiesByType($order, $propertyTypeId)
  {
    /** @var PlentyOrderProperty[] $properties */
    $properties = $order->properties;
    $matchingProperties = [];

    foreach ($properties as $property) {
      if ((int)$property->typeId !== (int)$propertyTypeId) continue;
      $matchingProperties[] = $property;
    }

    return $matchingProperties;
  }

  /**
   * @param string $id
   * @return PlentyOrder
   */
  public static function getOrderByExternalOrderId(string $id)
  {
    /** @var OrderRepositoryContract $order */
    $order = pluginApp(OrderRepositoryContract::class);

    return $order->findOrderByExternalOrderId($id);
  }
  public static function getOrderbyIdWithReferences($orderId)
  {

    $orderRepository = pluginApp(OrderRepositoryContract::class);

    $page = 1;

    $orderRepository->setFilters([
      'orderIds' => $orderId,
    ]);

    return  $orderRepository->searchOrders($page,100,['originOrderReferences']);

  }
  public static function getallcreditnotofOrder($orderId)
  {

    $orderRepository = pluginApp(OrderRepositoryContract::class);

    $page = 1;

    $orderRepository->setFilters([
      'parentOrderId' => $orderId,
      'orderType' => OrderType::TYPE_CREDIT_NOTE,
    ]);

    return  $orderRepository->searchOrders($page)->getResult();

  }

  public static function createCreditNoteforOrder($orderId,$payload,$status = null)
  {
     
     $CreditNote =  pluginApp(CreditNoteRepositoryContract::class)->createFromParent($orderId,$payload);
    
     self::setOrderStatusId($CreditNote->id,$status);

     return $CreditNote;
  }
  
  /**
   * @param $statusFrom
   * @param int $statusTo
   * @return array
   */
  public static function getByStatus($statusFrom, $statusTo = 0)
  {
    if ($statusTo === 0) $statusTo = $statusFrom;

    /** @var OrderRepositoryContract $orderRepository */
    $orderRepository = pluginApp(OrderRepositoryContract::class);
    $orders = [];
    $page = 1;
    $response = null;

    do {
      $orderRepository->setFilters([
        'statusFrom' => (float)$statusFrom,
        'statusTo' => (float)$statusTo
      ]);

      $response = $orderRepository->searchOrders($page);
      $page++;

      foreach ($response->getResult() as $order) {
        $orders[] = $order;
      }
    } while (!$response->isLastPage());

    return $orders;
  }

  /**
   * @param int $orderId
   * @return PlentyAddress
   */
  public static function getOrderDeliveryAddress(int $orderId)
  {
    /** @var OrderAddressRepositoryContract $orderAddressRepository */
    $orderAddressRepository = pluginApp(OrderAddressRepositoryContract::class);

    return $orderAddressRepository->findAddressByType($orderId, 2);
  }

  /**
   * @param int $orderId
   * @return PlentyAddress
   */
  public static function getOrderBillingAddress(int $orderId)
  {
    /** @var OrderAddressRepositoryContract $orderAddressRepository */
    $orderAddressRepository = pluginApp(OrderAddressRepositoryContract::class);

    return $orderAddressRepository->findAddressByType($orderId, 1);
  }

  /**
   * @param int $orderId
   * @param string $statusId
   * @return PlentyOrder
   */
  public static function setOrderStatusId(int $orderId, string $statusId)
  {
    /** @var OrderRepositoryContract $orderRepository */
    $orderRepository = pluginApp(OrderRepositoryContract::class);

    return $orderRepository->updateOrder([
      "statusId" => $statusId
    ], $orderId);

  }

  /**
   * @param int $orderId
   * @param int $addressId
   * @return PlentyOrder
   */
  public static function setDeliveryAddress(int $orderId, int $addressId)
  {
    return self::setOrderAddress($orderId, 2, $addressId);
  }

  /**
   * @param int $orderId
   * @param int $addressId
   * @return PlentyOrder
   */
  public static function setBillingAddress(int $orderId, int $addressId)
  {
    return self::setOrderAddress($orderId, 1, $addressId);
  }

  /**
   * @param int $orderId
   * @param int $typeId
   * @param int $addressId
   * @return PlentyOrder
   */
  public static function setOrderAddress(int $orderId, int $typeId, int $addressId)
  {
    /** @var OrderRepositoryContract $order */
    $order = pluginApp(OrderRepositoryContract::class);

    return $order->updateOrder(["addressRelations" => [
      "typeId" => $typeId,
      "addressId" => $addressId
    ]], $orderId);
  }

  /**
   * @param $orderId
   * @param $status
   * @return mixed
   */
  public static function setStatus($orderId, $status)
  {
    /** @var AuthHelper $auth */
    $auth = pluginApp(AuthHelper::class);
    /** @var OrderRepositoryContract $orderRepositoryContract */
    $orderRepositoryContract = pluginApp(OrderRepositoryContract::class);

    return $auth->processUnguarded(
      function () use ($orderRepositoryContract, $orderId, $status) {
        return $orderRepositoryContract->updateOrder(["statusId" => (float)$status], $orderId);
      }
    );
  }

  /**
   * @param int $id
   * @param array $data
   * @return PlentyOrder
   */
  public static function updateOrder(int $id, array $data)
  {
    /** @var OrderRepositoryContract $order */
    $order = pluginApp(OrderRepositoryContract::class);

    return $order->updateOrder($data, $id);
  }

  /**
   * @param int $shippingPackageId
   * @param $packageNumber
   * @param int $packageId
   * @param int $packageType
   * @return OrderShippingPackage
   */
  public static function updateOrderShippingNumber(int $shippingPackageId, $packageNumber, $packageId = 2, $packageType = 26)
  {
    /** @var OrderShippingPackageRepositoryContract $shippingPackageRepository */
    $shippingPackageRepository = pluginApp(OrderShippingPackageRepositoryContract::class);

    return $shippingPackageRepository->updateOrderShippingPackage($shippingPackageId, [
      'packageId' => $packageId,
      'packageNumber' => $packageNumber,
      'packageType' => $packageType
    ]);
  }

  /**
   * @param int $orderId
   * @return array
   */
  public static function listOrderShippingPackages(int $orderId)
  {
    /** @var OrderShippingPackageRepositoryContract $shippingPackageRepository */
    $shippingPackageRepository = pluginApp(OrderShippingPackageRepositoryContract::class);

    return $shippingPackageRepository->listOrderShippingPackages($orderId, ["*"], []);
  }

  /**
   * @param int $shippingPackageId
   * @return mixed
   */
  public static function deleteOrderShippingPackage(int $shippingPackageId)
  {
    /** @var OrderShippingPackageRepositoryContract $shippingPackageRepository */
    $shippingPackageRepository = pluginApp(OrderShippingPackageRepositoryContract::class);

    return $shippingPackageRepository->deleteOrderShippingPackage($shippingPackageId);
  }

  /**
   * @param $orderId
   * @return int
   */
  public static function getShippingProfileId($orderId)
  {
    /** @var PlentyOrder $order */
    $order = self::find($orderId);
    $shippingProfileId = null;

    foreach ($order->properties as $property) {
      if ($property["typeId"] == "2")
        $shippingProfileId = $property["value"];
    }

    return (int) $shippingProfileId;
  }

  /**
   * @param $orderId
   * @return bool
   */
  public static function hasChildOrders($orderId): bool
  {
    /** @var PlentyOrder $order */
    $order = pluginApp(OrderRepositoryContract::class)->findOrderById($orderId, ['warehouseSender']);

    if ($order instanceof PlentyOrder) {
      return $order->typeId === 1 && $order->warehouseSender === null;
    }

    return false;
  }

  /**
   * @param $orderId
   * @return mixed
   */
  public static function getParentOrder($orderId)
  {
    /** @var PlentyOrder $order */
    $order = self::find($orderId);

    /** @var OrderReference $orderReference */
    foreach ($order->orderReferences as $orderReference) {
      if ($orderReference->referenceType === 'parent') {
        return pluginApp(OrderRepositoryContract::class)->findOrderById($orderReference->originOrderId);
      }
    }

    return null;
  }

  /**
   * @param mixed $parentOrderId
   * @param mixed $data
   * 
   * @return [type]
   */
  public static function createDeliveryOrder($parentOrderId, $data)
  {
    /** @var DeliveryOrderRepositoryContract $deliveryOrderRepository */
    $deliveryOrderRepository = pluginApp(DeliveryOrderRepositoryContract::class);

    return $deliveryOrderRepository->createFromParent($parentOrderId, $data);
  }

}
