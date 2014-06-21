<?php
use BJHaze\Routing\Controller;

class HomeController extends Controller
{

    public function __construct()
    {
        $this['baseUrl'] = $this->request->getBaseUrl();
        $this->comment = new Comment();
        $this->category = new Category();
        $this->blog = new Blog();
        $this->user = new User();
        $this->loginFilter = new LoginFilter();
    }

    public function getBeforeBehaviors($action, array $parameters = null)
    {
        $before = array();
        if ($action != 'login') {
            $this->loginFilter->setUser($this->user);
            $before[] = $this->loginFilter;
        }
        return $before;
    }

    public function actionLogin($username = null, $password = null)
    {
        if ($this->request->isPost)
            if ($this->user->login($username, $password))
                $this->renderJSON(array(
                    'success' => true
                ));
            else
                $this->renderJSON(array(
                    'success' => false,
                    'message' => 'username/password is not matched'
                ));
        else
            $this->render('login');
    }

    public function actionLogout()
    {
        $this->user->logout();
        $this->redirect('/admin/home/login');
    }

    public function actionIndex()
    {
        $this->render('index', array(
            'menuList' => array(
                array(
                    'name' => 'blog',
                    'children' => array(
                        array(
                            'name' => 'posts',
                            'target' => 'blogs'
                        ),
                        array(
                            'name' => 'categories',
                            'target' => 'categories'
                        ),
                        array(
                            'name' => 'comments',
                            'target' => 'comments'
                        )
                    )
                )
            )
        ));
    }

    public function actionBlogs($page = null, $rows = null)
    {
        if ($this->request->isPost) {
            $blogs = $this->blog->getList((int) $page, (int) $rows);
            $this->renderJSON($blogs);
        } else
            $this->render('blogs');
    }

    public function actionNewBlog(array $blog = null)
    {
        if ($this->request->isPost) {
            $blog['category_id'] = (int) $blog['category_id'];
            $blog['addtime'] = date("Y-m-d H:i:s");
            $blog['intro'] = mb_substr($blog['content'], 0, 64);
            $result = $this->blog->create($blog);
            $this->renderJSON(array(
                'success' => (boolean) $result
            ));
        } else {
            $this->render('new_blog', array(
                'catetories' => $this->category->getList()
            ));
        }
    }

    public function actionEditBlog($id, array $blog = null)
    {
        if ($this->request->isPost) {
            $blog['id'] = $id;
            if (! empty($blog['content']))
                $blog['intro'] = mb_substr($blog['content'], 0, 64);
            $result = $this->blog->update($blog);
            $this->renderJSON(array(
                'success' => $result
            ));
        } else {
            $blog = $this->blog->read((int) $id);
            $this->render('edit_blog', array(
                'blog' => $blog,
                'catetories' => $this->category->getList()
            ));
        }
    }

    public function actionRemoveBlog(array $id)
    {
        if ($this->request->isPost) {
            $result = $this->blog->delete($id);
            $this->renderJSON(array(
                'success' => $result
            ));
        }
    }

    public function actionCategories()
    {
        if ($this->request->isPost) {
            $blogs = $this->category->getList();
            $this->renderJSON(array(
                'rows' => $blogs,
                'total' => sizeof($blogs)
            ));
        } else
            $this->render('categories');
    }

    public function actionNewCategory(array $category = null)
    {
        if ($this->request->isPost) {
            $result = $this->category->create($category);
            $this->renderJSON(array(
                'success' => (boolean) $result
            ));
        } else {
            $this->render('new_category');
        }
    }

    public function actionEditCategory($id, array $category = null)
    {
        if ($this->request->isPost) {
            $category['id'] = $id;
            $result = $this->category->update($category);
            $this->renderJSON(array(
                'success' => $result
            ));
        } else {
            $category = $this->category->read((int) $id);
            $this->render('edit_category', array(
                'category' => $category
            ));
        }
    }

    public function actionRemoveCategory(array $id)
    {
        if ($this->request->isPost) {
            $result1 = $this->category->delete($id);
            $result2 = $this->blog->deletePostByCategoryId($id);
            $this->renderJSON(array(
                'success' => (boolean) ($result1 && $result2)
            ));
        }
    }

    public function actionComments($page = null, $rows = null)
    {
        if ($this->request->isPost) {
            $comments = $this->comment->getList((int) $page, (int) $rows);
            $this->renderJSON($comments);
        } else
            $this->render('comments');
    }

    public function actionRemoveComment(array $id)
    {
        if ($this->request->isPost) {
            $result = $this->comment->delete($id);
            $this->renderJSON(array(
                'success' => (boolean) $result
            ));
        }
    }
}