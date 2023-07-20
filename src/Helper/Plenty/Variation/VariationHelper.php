<?php

namespace NetsEasyPay\Helper\Plenty\Variation;

use Plenty\Modules\Item\Item\Contracts\ItemRepositoryContract;
use Plenty\Modules\Item\Variation\Contracts\VariationLookupRepositoryContract;
use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
use Plenty\Modules\Item\Variation\Models\Variation as PlentyVariation;
use Plenty\Modules\Item\VariationCategory\Contracts\VariationCategoryRepositoryContract;
use Plenty\Modules\Item\VariationCategory\Models\VariationCategory;
use Plenty\Modules\Item\VariationSalesPrice\Contracts\VariationSalesPriceRepositoryContract;
use Plenty\Modules\Item\VariationSalesPrice\Models\VariationSalesPrice;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Plenty\Modules\Tag\Contracts\TagRelationshipRepositoryContract;
use Plenty\Modules\Tag\Models\TagRelationship;
use NetsEasyPay\Helper\Plenty\LogHelper;

class VariationHelper
{



  /**
   * @param $variationId
   * @return PlentyVariation
   */
  public static function find($variationId)
  {
    /** @var VariationRepositoryContract $repository */
    $repository = pluginApp(VariationRepositoryContract::class);

    return $repository->findById($variationId);
  }


  public static function findbyIds($variationIds)
  {
    /** @var VariationRepositoryContract $repository */
    $repository = pluginApp(VariationRepositoryContract::class);

    $data = $repository->showMultiple($variationIds, ['variationTexts']);
    
    
    return $data[0] ?? null;
  }

  /**
   * @param $variationId
   * @return string
   */
  public static function getVariationNoByVariationId($variationId)
  {
    /** @var PlentyVariation $variation */
    $variation = pluginApp(VariationRepositoryContract::class)->findById($variationId);

    if ($variation instanceof Variation && $variation->number) {
      return $variation->number;
    }

    return '';
  }

  /**
   * @param $variationId
   * @return array
   */
  public static function getSkusByVariationId($variationId)
  {
    /** @var VariationSkuRepositoryContract $repository */
    $repository = pluginApp(VariationSkuRepositoryContract::class);

    return $repository->findByVariationId($variationId);
  }

  /**
   * @param $externalId
   * @return mixed|null
   */
  public static function findByExternalId($externalId)
  {
    $variation = pluginApp(VariationLookupRepositoryContract::class)->hasExternalId($externalId)->lookup();

    if (count($variation) > 0) {
      return self::find($variation[0]['variationId']);
    }

    return null;
  }

  /**
   * @param $itemId
   * @return array
   */
  public static function findVariationsByItemId($itemId)
  {
    /** @var ItemRepositoryContract $repository */
    $repository = pluginApp(ItemRepositoryContract::class);

    return $repository->show($itemId, ['*'], 'de', ['variations'])->variations;
  }

  /**
   * @param $variationId
   * @return VariationCategory
   */
  public static function getVariationCategories($variationId)
  {
    /** @var VariationCategoryRepositoryContract $repository */
    $repository = pluginApp(VariationCategoryRepositoryContract::class);

    return $repository->findByVariationId($variationId);
  }

  /**
   * @param $itemVariations
   * @return array
   */
  public static function getAttributes($itemVariations)
  {
    /** @var VariationRepositoryContract $repo */
    $repo = pluginApp(VariationRepositoryContract::class);

    foreach ($itemVariations as $itemVariation) {
      $variation = $repo->show($itemVariation['id'], ['variationAttributeValues'], 'de');

      if ($variation instanceof PlentyVariation && count($variation->variationAttributeValues) > 0) {
        return $variation->variationAttributeValues;
      }
    }

    return [];
  }

  /**
   * @param $variationId
   * @return VariationSalesPrice
   */
  public static function getSalesPrices($variationId)
  {
    /** @var VariationSalesPriceRepositoryContract $repository */
    $repository = pluginApp(VariationSalesPriceRepositoryContract::class);

    return $repository->findByVariationId($variationId);
  }

  /**
   * @param $tagId
   * @return array
   */
  public static function findVariationsByTagId($tagId)
  {
    
    /** @var TagRelationshipRepositoryContract $tagRelationRepository */
    $tagRelationRepository = pluginApp(TagRelationshipRepositoryContract::class);
    $tagRelationData = $tagRelationRepository->findByTagId($tagId);
    
    $variationIds = array();
    foreach ($tagRelationData as $tagRelation) {
      array_push($variationIds,$tagRelation->relationshipValue);
    }
    return $variationIds;
  }
  
  /**
   * @param $variationId, $tagId
   * @return null
   */
  public static function removeTagFromVariations($variationId, $tagId)
  {
    /** @var TagRelationshipRepositoryContract $tagRelationRepository */
    $tagRelationRepository = pluginApp(TagRelationshipRepositoryContract::class);

    try {
      $tagRelationResponse = $tagRelationRepository->deleteOneRelation($variationId, 'variation', $tagId);
    } catch (\Throwable $th) {
      LogHelper::error("removeTagFromVariations error", "deleteOneRelation", $th);
      throw $th;
    }

    return $tagRelationResponse;
  }

   /**
    * Add a tag to a variation
    * @param $variationId 
     * @param $tagId
     * @return null
     */
    public static function applyTagToVariation($variationId, $tagId)
    {
        /** @var TagRelationshipRepositoryContract $tagRelationRepository */
        $tagRelationRepository = pluginApp(TagRelationshipRepositoryContract::class);
      
        try {
            $tagRelationResponse = $tagRelationRepository->create([
                "tagId" => $tagId,
                "tagType" => "variation",
                "relationshipValue" => $variationId
            ]);
        } catch (\Throwable $th) {
            LogHelper::error(__CLASS__, "Could not add Tag to variation.", $th);
            throw $th;
        }

        return $tagRelationResponse;
    }

    /**
     * Checks if a variation has a specific tag
     * @param $itemVariationId
     * @param $tagId
     *
     * @return bool
     */
    public function hasTag($itemVariationId, $tagId)
    {
      /** @var TagRelationshipRepositoryContract $tagRelationRepository */
      $tagRelationRepository = pluginApp(TagRelationshipRepositoryContract::class);

      $tagRelation = $tagRelationRepository->findRelationship((int) $tagId, (int) $itemVariationId, TagRelationship::TAG_TYPE_VARIATION);

      return ($tagRelation instanceof TagRelationship);
    }
}
