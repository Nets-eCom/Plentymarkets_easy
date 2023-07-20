<?php

namespace NetsEasyPay\Widgets;

use Ceres\Widgets\Helper\BaseWidget;

class PayNow extends BaseWidget
{
  // HINT: Changes to this file require a full build (devTool push is not enough).

  // This is the path to the twig file used for this Widget
  protected $template = "NetsEasyPay::Widgets.PayNow";

  protected function getTemplateData($widgetSettings, $isPreview)
  {

    $MaxValue = $widgetSettings["MaxValue"]["mobile"];

    return [
      "MaxValue" => $MaxValue,
      "isShopBuilder" => $isPreview,
    ];
  }

}
