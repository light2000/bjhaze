<?php
use BJHaze\Widget\Widget;

class Comments extends Widget
{

    public function run()
    {
        $comments = $this->comment->getList(1, 12);
        $this->render('comments', array(
            'comments' => $comments['rows']
        ));
    }
}