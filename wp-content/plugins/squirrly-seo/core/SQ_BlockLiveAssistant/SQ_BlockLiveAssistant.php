<?php

/**
 * Live Assistant settings
 */
class SQ_BlockLiveAssistant extends SQ_BlockController {

    function hookGetContent() {
        parent::preloadSettings();
    }
}
