<?php
use BJHaze\Widget\Widget;

class Footer extends Widget
{

    public function run()
    {
        $this->render('footer', array(
            'categories' => (new Category())->getList()
        ));
    }
}