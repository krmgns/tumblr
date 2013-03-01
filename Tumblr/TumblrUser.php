<?php

/**
 * Copyright 2013,  Kerem Gunes <http://qeremy.com/>.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

/**
 * @class TumblrUser v0.1
 *
 * Provides access to the Tumblr API/v2 "user" methods. Before using it
 * be sure that you already know all Tumblr API/v2 "user" methods. See more
 * details here: http://www.tumblr.com/docs/en/api/v2#user-methods
 */
class TumblrUser
{
    protected
        // Tumblr Object
        $_tumblr;
    
    
    /**
     * Initialize a TumblrUser Object.
     *
     * @param object $tumblr (required)
     */
    public function __construct(Tumblr $tumblr) {
        $this->_tumblr = $tumblr;
    }
    
    /**
     * Get a user's information.
     * HTTP Method: GET, Authentication: OAuth, Details: http://www.tumblr.com/docs/en/api/v2#m-up-info
     *
     * @param closure $callback (default=null)
     * @return array
     * @throw TumblrException
     */
    public function getInfo(Closure $callback = null) {
        return $this->_request('/info', null, $callback);
    }
    
    /**
     * Retrieve a user's dashboard.
     * HTTP Method: GET, Authentication: OAuth, Details: http://www.tumblr.com/docs/en/api/v2#m-ug-dashboard
     *
     * @param closure $requestParams (default=null)
     * @param closure $callback      (default=null)
     * @return array
     * @throw TumblrException
     */
    public function getDashboard(Array $requestParams = null, Closure $callback = null) {
        return $this->_request('/dashboard', $requestParams, $callback);
    }
    
    /**
     * Retrieve a user's likes.
     * HTTP Method: GET, Authentication: OAuth, Details: http://www.tumblr.com/docs/en/api/v2#m-ug-likes
     *
     * @param closure $requestParams (default=null)
     * @param closure $callback      (default=null)
     * @return array
     * @throw TumblrException
     */
    public function getLikes(Array $requestParams = null, Closure $callback = null) {
        return $this->_request('/likes', $requestParams, $callback);
    }
    
    /**
     * Retrieve the blogs a user is following.
     * HTTP Method: GET, Authentication: OAuth, Details: http://www.tumblr.com/docs/en/api/v2#m-ug-following
     *
     * @param closure $requestParams (default=null)
     * @param closure $callback      (default=null)
     * @return array
     * @throw TumblrException
     */
    public function getFollowing(Array $requestParams = null, Closure $callback = null) {
        return $this->_request('/following', $requestParams, $callback);
    }
    
    /**
     * Follow a blog.
     * HTTP Method: POST, Authentication: OAuth, Details: http://www.tumblr.com/docs/en/api/v2#m-up-follow
     *
     * @param closure $url      (required)
     * @param closure $callback (default=null)
     * @return array
     * @throw TumblrException
     */
    public function followBlog($url, Closure $callback = null) {
        return $this->_request('/follow', null, $callback, true, array('url' => $url));
    }
    
    /**
     * Unfollow a blog.
     * HTTP Method: POST, Authentication: OAuth, Details: http://www.tumblr.com/docs/en/api/v2#m-up-unfollow
     *
     * @param closure $url      (required)
     * @param closure $callback (default=null)
     * @return array
     * @throw TumblrException
     */
    public function unfollowBlog($url, Closure $callback = null) {
        return $this->_request('/unfollow', null, $callback, true, array('url' => $url));
    }
    
    /**
     * Like a post.
     * HTTP Method: POST, Authentication: OAuth, Details: http://www.tumblr.com/docs/en/api/v2#m-up-like
     *
     * @param closure $id        (required,default=null)
     * @param closure $reblogKey (required,default=null)
     * @param closure $callback  (default=null)
     * @return array
     * @throw TumblrException
     */
    public function likePost($id = null, $reblogKey = null, Closure $callback = null) {
        if ($id && $reblogKey) {
            return $this->_request('/like', null, $callback, true, array(
                'id' => $id, 'reblog_key' => $reblogKey
            ));
        }
        throw new TumblrException('ID and reblog_key is required.');
    }
    
    /**
     * Unlike a post.
     * HTTP Method: POST, Authentication: OAuth, Details: http://www.tumblr.com/docs/en/api/v2#m-up-unlike
     *
     * @param closure $id        (required,default=null)
     * @param closure $reblogKey (required,default=null)
     * @param closure $callback  (default=null)
     * @return array
     * @throw TumblrException
     */
    public function unlikePost($id = null, $reblogKey = null, Closure $callback = null) {
        if ($id && $reblogKey) {
            return $this->_request('/unlike', null, $callback, true, array(
                'id' => $id, 'reblog_key' => $reblogKey
            ));
        }
        throw new TumblrException('ID and reblog_key is required.');
    }
    
    /**
     * Make an API request.
     *
     * @param string $uri           (required)
     * @param string $requestParams (required|not-required,default=null)
     * @param closure $callback     (default=null)
     * @param boolean $oauth        (default=true)
     * @param array $postParams     (default=true) Only for POST requests.
     * @return array (with executing $callback if available)
     * @throw TumblrException
     */
    protected function _request($uri, Array $requestParams = null, Closure $callback = null, $oauth = true, Array $postParams = null) {
        $response = $this->_tumblr->request(
            $this->_prepareUri($uri, $requestParams), $oauth, $postParams);
        
        return is_callable($callback)
            ? call_user_func($callback, $response)
            : $response;
    }
    
    /**
     * Prepare request URI.
     *
     * @param string $uri           (required)
     * @param string $requestParams (required|not-required,default=null)
     * @return string $uri
     * @throw TumblrException
     */
    protected function _prepareUri($uri, Array $requestParams = null) {
        $uri = sprintf('%s/user/%s', $this->_tumblr->getApiUrl(), trim($uri, '/'));
        if (!empty($requestParams)) {
            $uri .= '?'. http_build_query($requestParams);
        }
        return $uri;
    }
}
