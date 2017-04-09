<?php

/**
 * Keyword Research settings
 */
class SQ_BlockKeywordResearch extends SQ_BlockController {

    function hookGetContent() {
        parent::preloadSettings();
    }
}
