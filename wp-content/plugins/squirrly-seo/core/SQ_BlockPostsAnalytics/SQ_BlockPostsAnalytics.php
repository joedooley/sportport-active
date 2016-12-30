<?php

class SQ_BlockPostsAnalytics extends SQ_BlockController {

    public function hookGetContent() {

        SQ_ObjController::getController('SQ_DisplayController', false)
                ->loadMedia(_SQ_THEME_URL_ . '/css/sq_postslist.css');

        SQ_Tools::saveOptions('sq_analytics', 1); //Save analytics viewed
        SQ_Tools::saveOptions('sq_dashboard', 1); //Save dashboard viewed
        $this->postlist = SQ_ObjController::getController('SQ_PostsList');

        $this->model->prepare_items();
    }

    public function getNavigationTop() {
        return $this->model->display_tablenav('top');
    }

    public function getNavigationBottom() {
        return $this->model->display_tablenav('bottom');
    }

    public function getHeaderColumns() {
        return $this->model->print_column_headers();
    }

    public function getRows() {
        return $this->model->display_rows();
    }

    public function hookFooter() {
        $this->postlist->setPosts($this->model->posts);
        $this->postlist->hookFooter();
    }

    public function getScripts() {
        return $this->postlist->getScripts();
    }

}
