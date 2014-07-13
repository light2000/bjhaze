<?php
use BJHaze\Widget\Widget;

class Header extends Widget
{

    public function run()
    {
        $this->render('header', array(
            'categories' => (new Category())->getList()
        ));
    }
}