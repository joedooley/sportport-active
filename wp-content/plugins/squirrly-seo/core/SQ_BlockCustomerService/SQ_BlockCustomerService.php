<?php

/**
 * Customer Service Page
 */
class SQ_BlockCustomerService extends SQ_BlockController {

    function hookGetContent() {
        SQ_ObjController::getController('SQ_DisplayController', false)->loadMedia('sq_blocksupport');
    }
}
