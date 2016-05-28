<?php

/**
 * Account settings
 */
class SQ_BlockAccount extends SQ_BlockController {

    function hookGetContent() {
        parent::preloadSettings();
    }

}
