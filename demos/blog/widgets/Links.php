<?php
use BJHaze\Widget\Widget;

class Links extends Widget
{

    public function run()
    {
        $this->render('links', array(
            'links' => array(
                'BJHaze Wiki' => 'https://code.csdn.net/vn700/bjhaze/wikis',
                'BJHaze Home' => 'https://code.csdn.net/vn700/bjhaze'
            )
        ));
    }
}