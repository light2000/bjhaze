<?php
use BJHaze\Routing\Controller;

/**
 *
 * @property Comment $comment
 * @property Category $category
 * @property Blog $blog
 */
class HomeController extends Controller
{

    protected $layout = 'home/layout';

    protected $categories;
    
    protected $cacheEngine = 'redis';
    
    protected $cacheActions = array('index' => null);

    public function __construct()
    {
        $this['baseUrl'] = $this->request->getBaseUrl();
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
    
    // *** widgets begin***
    
    /**
     * footer
     */
    public function actionFooter()
    {
        if (empty($this->categories))
            $this->categories = $this->category->getList();
        $this->render('widgets/footer', array(
            'categories' => $this->categories
        ));
    }

    public function actionHeader()
    {
        if (empty($this->categories))
            $this->categories = $this->category->getList();
        $this->render('widgets/header', array(
            'categories' => $this->categories
        ));
    }

    public function actionLinks()
    {
        $this->render('widgets/links', array(
            'links' => array(
                'BJHaze Wiki' => 'https://code.csdn.net/vn700/bjhaze/wikis',
                'BJHaze Home' => 'https://code.csdn.net/vn700/bjhaze'
            )
        ));
    }

    public function actionNewPosts()
    {
        $posts = $this->blog->getList(1, 12);
        $this->render('widgets/new_posts', array(
            'posts' => $posts['rows']
        ));
    }

    public function actionComments()
    {
        $comments = $this->comment->getList(1, 12);
        $this->render('widgets/comments', array(
            'comments' => $comments['rows']
        ));
    }
    
    // *** widgets end***
}