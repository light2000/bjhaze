<?php
use BJHaze\Routing\Controller;

class HomeController extends Controller
{

    protected $layout = 'home/layout';

    protected $categories;

    public function __construct()
    {
        $this['baseUrl'] = $this->request->getBaseUrl();
        $this->blog = new Blog();
        $this->category = new Category();
        $this->comment = new Comment();
    }

    /**
     * Home Page
     *
     * @return void
     */
    public function actionIndex()
    {
        $this->actionPage(1);
    }

    /**
     * blog list page
     *
     * @param int $page            
     */
    public function actionPage($page)
    {
        $page = (int) $page;
        $blogs = $this->blog->getList($page);
        
        $maxPage = ceil($blogs['total'] / $this['blog_page_size']);
        
        $pages = range(($st = $page - ceil($this['blog_bottom_page_numbers'] / 2)) > 0 ? $st : 1, $maxPage > $this['blog_bottom_page_numbers'] ? $this['blog_bottom_page_numbers'] : $maxPage);
        
        $this->render('page', array(
            'posts' => $blogs['rows'],
            'pages' => $pages,
            'page' => $page
        ));
    }

    public function actionCategory($tag, $page = 1)
    {
        $page = (int) $page;
        $blogs = $this->blog->getList($page, null, $tag);
        
        $maxPage = ceil($blogs['total'] / $this['blog_page_size']);
        
        $pages = range(($st = $page - ceil($this['blog_bottom_page_numbers'] / 2)) > 0 ? $st : 1, $maxPage > $this['blog_bottom_page_numbers'] ? $this['blog_bottom_page_numbers'] : $maxPage);
        
        $this->render('page', array(
            'posts' => $blogs['rows'],
            'pages' => $pages,
            'page' => $page
        ));
    }

    public function actionPost($id)
    {
        $post = $this->blog->read((int) $id);
        $comments = $this->comment->getList(1, 100, $id)['rows'];
        
        $this->render('post', compact('post', 'comments'));
    }

    public function actionComment(array $comment)
    {
        if (! empty($comment['blog_id']) && ! empty($comment['content'])) {
            try {
                $comment['blog_id'] = (int) $comment['blog_id'];
                $comment['addtime'] = date("Y-m-d H:i:s");
                $comment['user_ip'] = $this->request->getUserHostAddress();
                $result = $this->comment->create($comment);
            } catch (Exception $ex) {}
        }
        $this->redirect('/post/' . $comment['blog_id']);
    }
}