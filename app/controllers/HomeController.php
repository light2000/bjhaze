<?php
use BJHaze\Routing\Controller;

class HomeController extends Controller
{

    protected $cacheEngine = 'memcache';

    protected $cacheActions = array(
            'index' => 2
    );

    public function actionIndex ($nn, $ee)
    {
        /**
         * $d = array(
         * 'sdf',
         * 'dfd',
         * 'fff' => 'fff'
         * );
         * $this->renderXML($d);*
         */
        /**
         * $e = $this->encrypter->encrypt($d);
         * $f = $this->encrypter->decrypt($e);
         * $this->renderJSON(array(
         * 'f' => $f,
         * 'e' => $e
         * ));*
         */
        $ret1 = $this->db->select()
            ->from('{{anti_word}}')
            ->where(array(
                'id' => 11
        ))
            ->queryRow();
        
        $data = array(
                array(
                        'word' => '大盘鸡',
                        'addtime' => date("Y-m-d H:i:s")
                ),
                array(
                        'word' => '大盘鸭',
                        'addtime' => date("Y-m-d H:i:s")
                ),
                array(
                        'word' => '大盘鹅',
                        'addtime' => date("Y-m-d H:i:s")
                )
        );
        $test = $this->build('Test');
        $ret2 = $test->create($data);
        
        // $ret2 = $test->delete(408);
        $ret3 = null;
        $data = array(
                'id' => 457,
                'word' => '桃树',
                'addtime' => date("Y-m-d H:i:s")
        );
        $ret4 = $test->update($data);
        $ret5 = $this->db->select()
            ->from('{{area}}')
            ->leftJoin('{{area_city}}', '{{area}}.id = {{area_city}}.id')
            ->where('{{area}}.id < ? and {{area}}.id > ?', 2000, 100)
            ->orWhere('{{area}}.id IN ?', 
                array(
                        1,
                        2,
                        3
                ))
            ->order('{{area}}.id')
            ->limit(10, 10)
            ->queryAll();
        
        $this->session->start();
        // $this->session->set('id', 457);
        $this->render('test', 
                array(
                        'data' => array(
                                $ret1,
                                $ret2,
                                $ret3,
                                $ret4,
                                $ret5,
                                $this->session->get('id')
                        )
                ));
    }
}