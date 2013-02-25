Before beginning to HOWTO;

1- Be sure your PHP version >= 5.3.* and "OAuth" extension is already installed<br>
2- Register an API following this link: http://www.tumblr.com/oauth/apps<br>
3- Some methods can take special parameters, see all them following this link: http://www.tumblr.com/docs/en/api/v2. For example `Tumblr::getLikes()` could be used like `Tumblr::getLikes(array('limit' => 1, 'offset' => 0))` according to available API/v2 parameters<br>
4- See all details here http://www.tumblr.com/docs/en/api/v2 about which URI method can take which parameters<br>
5- The post `ID`s could be very big for 32bit's, use `ID`s remembering this, e.g: `printf('Post added, id: %s', $response['response']['id'])`.<br>

**HOWTO**

- Using `Tumblr`

```php
// Define your OAuth Consumer Key and Secret Key
define('TUMBLR_CONS_KEY', 'your_tumblr_oauth_consumer_key');
define('TUMBLR_SECR_KEY', 'your_tumblr_secret_key');

$tumblr = new Tumblr(TUMBLR_CONS_KEY, TUMBLR_SECR_KEY);
// Remember to call this method before requesting OAuth required methods on API/v2
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
// Add a text post
$data = $tumblrBlog->addPost('text', array('title' => 'Test', 'body' => 'Lorem ipsum dolor!'));
// Or
$data = $tumblrBlog->addPost('text', array(
    'title' => 'Callback Test', 'body' => 'Lorem ipsum dolor!'
    ), function($response) {
        if ($response['meta']['status'] == 201) {
            printf('Post added, id: %s', $response['response']['id']);
        } else {
            printf('Error: %s', $response['response']['errors']);
        }
    });

// Add a photo post
$data = $tumblrBlog->addPost('photo', array('source' => 'http://assets.tumblr.com/images/default_avatar_128.gif'));
// Or
$data = $tumblrBlog->addPost('photo', array('data' => file_get_contents('avatar1.jpeg')));
// TODO: But this does not work, interesting... 
// Even same implementation here: https://github.com/codingjester/tumblr_client
$data = $tumblrBlog->addPost('photo', array('data' => array(
    file_get_contents('avatar1.jpeg'),
    file_get_contents('avatar2.jpeg'),
)));

// Delete a post
$tumblrBlog->deletePost('43937593214', function($response) {
    if ($response['meta']['status'] == 201) {
        printf('Post deleted, id: %s', $response['response']['id']);
    } else {
        printf('Error: %s', $response['response']['errors']);
    }
});
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
$data = $tumblrTagged->getPosts('php', array('limit' => 1));
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

**METHODS MAP**

```php
// Tumblr object
Tumblr::__construct(String $consKey, String $secrKey)
Tumblr::createOauth()
Tumblr::throwResponseErrors(Boolean $option)
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

**NOTE**

If you want to work on `localhost`: After once authenticated and redirected to your callback URL, copy all `$_GET` parameters from URL and paste it to local URL. For example: `http://qeremy.com/tumblr/?oauth_token=...` to `http://localhost/tumblr/test.php?oauth_token=...`.

**OPINIONS**

1- URI: `GET /posts`. It takes only one type as param (e.g. `/posts/text` or `/posts?type=text`), so you cannot query for multiple types. Maybe it should be better getting multiple types (e.g. `/posts?type=text,quote,...`). Link: http://www.tumblr.com/docs/en/api/v2#posts<br>
2- URI: `GET /posts?id=123`. It returns `posts` as `array`. If an `ID` is uniq, so why it returns an array such `Object ([response] => Object [posts] => Object([0] => Object([id] => 123, ...)))`? I'm requesting a uniq resource but getting multiple responses.<br>
3- When I request a non-exists resources (e.g. ID 111 is non-exists `POST /edit array('id' => 111)` or `POST /delete array('id' => 111)`), responses return like `Object ([meta] => Object([status] => 401 [msg] => Not Authorized)`. If any source is not found or not exists on a API then I think it should better returning `404 Not Found` (ref: http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html).<br>
4- I think there is no check out on `reblog` actions for same reblog `ID` (and `reblog_key`). Because, I can re-blog same post even I already did it before. Please stop twice re-blog or more.<br>
5- I cannot use multiple `ID`s for `/delete` action, why? For this reason, I need to make several requests to delete posts more than one (needless & excessive source consuming both client and server sides...).<br>
6- After `/edit` action, API returns `200 OK` if success. But `205 Reset Content` sounds more appropriate (ref: http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html).<br>
7- Some responses contain both objects and arrays (e.g. `/posts`). Better deciding on object or array, but only one type please.<br>
8- `POST /post array('data' => readfile('foo.gif'))` and `POST /post array('data' => array(readfile('foo.gif')))` okay but `POST /post array('data' => array(readfile('foo.gif'), readfile('bar.gif')))` returns an error `401 Not Authorized`. API guide says arrays are OK, and total file sizes <= 10MB. WTF?<br>

That's it!
