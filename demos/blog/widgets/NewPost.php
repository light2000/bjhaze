<?php
use BJHaze\Widget\Widget;

class NewPost extends Widget
{

    public function run()
    {
        $this->render('new_posts', array(
            'posts' => (new Blog())->getList(1, 12)['rows']
        ));
    }
}