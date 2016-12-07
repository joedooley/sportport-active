<?php

namespace Wpae\Pro\VariationOptions;


use Wpae\VariationOptions\VariationOptionsInterface;

class VariationOptions extends \Wpae\VariationOptions\VariationOptions implements VariationOptionsInterface
{
    public function preprocessPost(\WP_Post $entry)
    {
        $productVariationMode = \XmlExportEngine::getProductVariationMode();

        if (!$this->shouldTitleBeProcessed($productVariationMode)) {
            return $entry;
        }
        if($entry->post_type != 'product_variation') {
            return $entry;
        }

        if ($entry->post_type == 'product_variation') {
                $entryId = $entry->ID;
                $entryTitle = $entry->post_title;
                $parentId = $entry->post_parent;
                $parent = get_post($parentId);
                $parent->originalPost = clone $entry;
                $entry = $parent;
                $entry->ID = $entryId;
                $entry->post_parent = $parentId;
                if (\XmlExportEngine::getProductVariationTitleMode() == \XmlExportEngine::VARIATION_USE_DEFAULT_TITLE) {
                    $entry->post_title = $entryTitle;
                }
                $entry->post_type = 'product_variation';
            }

        return $entry;
    }

    public function getQueryWhere($wpdb, $where, $join, $closeBracket = false)
    {
        if (\XmlExportEngine::getProductVariationMode() == \XmlExportEngine::VARIABLE_PRODUCTS_EXPORT_PARENT) {
            return " AND ($wpdb->posts.post_type = 'product') ";
        } else if (\XmlExportEngine::getProductVariationMode() == \XmlExportEngine::VARIABLE_PRODUCTS_EXPORT_VARIATION) {
            return " AND ($wpdb->posts.post_type = 'product_variation' AND $wpdb->posts.post_parent IN (
                SELECT DISTINCT $wpdb->posts.ID
                            FROM $wpdb->posts $join
                            WHERE $where
                        ) OR $wpdb->posts.post_type = 'product' AND $wpdb->posts.ID NOT IN (
                SELECT DISTINCT $wpdb->posts.post_parent
                            FROM $wpdb->posts $join
                            WHERE ". str_replace("post_type = 'product'", "post_type = 'product_variation'", $where) ."
                        ))";
        } else {
            return $this->defaultQuery($wpdb, $where, $join, $closeBracket);
        }
    }

    /**
     * @param $productVariationMode
     * @return bool
     */
    private function shouldTitleBeProcessed($productVariationMode)
    {
        return $productVariationMode != \XmlExportEngine::VARIABLE_PRODUCTS_EXPORT_PARENT ||
        $productVariationMode == \XmlExportEngine::VARIABLE_PRODUCTS_EXPORT_VARIATION;
    }
}