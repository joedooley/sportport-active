<?php

namespace Wpae\VariationOptions;


interface VariationOptionsInterface
{
    public function preProcessPost(\WP_Post $entry);

    public function getQueryWhere($wpdb, $where, $join, $closeBracket = false);
}