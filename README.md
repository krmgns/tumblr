Before beginning to HOWTO;

1- Be sure your PHP version >= 5.3.* and "OAuth" extension is already installed<br>
2- Register an API following this link: http://www.tumblr.com/oauth/apps<br>
3- Some methods can take special parameters, see all them following this link: http://www.tumblr.com/docs/en/api/v2. For example `Tumblr::getLikes()` could be used like `Tumblr::getLikes(array('limit' => 1, 'offset' => 0))` according to available API/v2 parameters<br>
4- See all details here http://www.tumblr.com/docs/en/api/v2 about which URI method can take which parameters<br>

**HOWTO**

- Using `Tumblr`

```php
// Define your OAuth Consumer Key and Secret Key
define('CONS_KEY', 'your_tumblr_oauth_consumer_key');
define('SECR_KEY', 'your_tumblr_secret_key');

$tumblr = new Tumblr(CONS_KEY, SECR_KEY);
// Remember calling this method before requesting OAuth required methods on API/v2
$tumblr->createOauth();
```

- Using `TumblrBlog`

```php
// Init with target blog
$tumblrBlog = new TumblrBlog($tumblr, 'qeremy.tumblr.com');
// Or init without target blog, but remember setting later it before any request
$tumblrBlog = new TumblrBlog($tumblr);
$tumblrBlog->setBaseHostname('qeremy.tumblr.com');

/*** Getting Data ***/
$data = $tumblrBlog->getInfo();
print_r($data);
// or
$tumblrBlog->getInfo(function($response) {
    print $response['response']['blog']['title'];
});
// or
print $tumblrBlog->getInfo(function($response) {
    return $response['response']['blog']['title'];
});

/*** Setting Data ***/
$tumblrBlog->addPost('text', array('title' => 'Test', 'body' => 'Lorem ipsum dolor!'));
```

- Using `TumblrUser`

```php
// Init 
$tumblrUser = new TumblrUser($tumblr);

/*** Getting Data ***/
$data = $tumblrUser->getInfo();
print_r($data);
// or
$tumblrUser->getInfo(function($response) {
    print $response['response']['user']['name'];
});
// or
print $tumblrBlog->getInfo(function($response) {
    return $response['response']['user']['name'];
});

/*** Setting Data ***/
$data = $tumblrUser->followBlog('davidslog.com', function($response) use($tumblrUser) {
    return $tumblrUser->getFollowing();
});
```

- Using `TumblrTagged`

```php
$tumblrTagged = new TumblrTagged($tumblr);
$data = $tumblrTagged->getPosts('php', array('limit'=>1));
```

- Extra

```php
/*** Throw Tumblr errors ***/
try {
    // Post ID is required: ... api/v2/posts/?id=123
    $response = $tumblrBlog->getPost('');
} catch (TumblrException $e) {
    print $e->getMessage();
}

/*** Throw response errors ***/
// Set true if needed try/catch stuff for response errors
$tumblr->throwResponseErrors(true);
try {
    // Called URI does not exists: ... api/v2/posts/foo
    $response = $tumblrBlog->getPosts('foo');
} catch (TumblrException $e) {
    print $e->getMessage();
}
```

**METHOD MAP**

```php
// Tumblr object
Tumblr::__construct(String $consKey, String $secrKey)
Tumblr::createOauth()
Tumblr::throwResponseErrors(bool $option)
Tumblr::getApiKey()
Tumblr::getApiUrl()

// TumblrBlog object
TumblrBlog::__construct(Tumblr $tumblr, String $baseHostname = null)
TumblrBlog::setBaseHostname(String $baseHostname)
TumblrBlog::getInfo(Closure $callback = null)
TumblrBlog::getLikes(Array $requestParams = null, Closure $callback = null)
TumblrBlog::getFollowers(Array $requestParams = null, Closure $callback = null)
TumblrBlog::getPosts(String $type, Array $requestParams = null, Closure $callback = null)
TumblrBlog::getPost(String $id, Closure $callback = null)
TumblrBlog::addPost(String $type, Array $requestParams, Closure $callback = null)
TumblrBlog::editPost(String $id, Array $requestParams, Closure $callback = null)
TumblrBlog::deletePost(String $id, Closure $callback = null)
TumblrBlog::reblogPost(String $id, String $reblogKey, String $comment = null, Closure $callback = null)

// TumblrUser object
TumblrUser::__construct(Tumblr $tumblr)
TumblrUser::getInfo(Closure $callback = null)
TumblrUser::getDashboard(Array $requestParams = null, Closure $callback = null)
TumblrUser::getLikes(Array $requestParams = null, Closure $callback = null)
TumblrUser::getFollowing(Array $requestParams = null, Closure $callback = null)
TumblrUser::followBlog(String $url, Closure $callback = null)
TumblrUser::unfollowBlog(String $url, Closure $callback = null)
TumblrUser::likePost(String $id, String $reblogKey, Closure $callback = null)
TumblrUser::unlikePost(String $id, String $reblogKey, Closure $callback = null)

// TumblrTagged object
TumblrTagged::__construct(Tumblr $tumblr)
TumblrTagged::getPosts(String $tag, Array $requestParams = null, Closure $callback = null)
```
