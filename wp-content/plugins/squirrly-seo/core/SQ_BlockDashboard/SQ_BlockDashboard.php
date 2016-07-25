<?php

/**
 * Dashboard settings
 */
class SQ_BlockDashboard extends SQ_BlockController {

    function hookGetContent() {
        parent::preloadSettings();
    }

}
