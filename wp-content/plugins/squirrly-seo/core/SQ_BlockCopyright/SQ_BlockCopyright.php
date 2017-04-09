<?php

/**
 * Live Assistant settings
 */
class SQ_BlockCopyright extends SQ_BlockController {

    function hookGetContent() {
        parent::preloadSettings();
    }
}
