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
 * @class TumblrBlog v0.1
 *
 * Provides access to the Tumblr API/v2 "blog" methods. Before using it
 * be sure that you already know all Tumblr API/v2 "blog" methods. See more
 * details here: http://www.tumblr.com/docs/en/api/v2#blog_methods
 */
class TumblrBlog
{
    protected
        // Tumblr Object
        $_tumblr,
        // Target base-hostname
        $_baseHostname;
    
    /**
     * Initialize a TumblrBlog Object.
     *
     * @param object $tumblr (required)
     * @param string $baseHostname (default=null)
     */
    public function __construct(Tumblr $tumblr, $baseHostname = null) {
        $this->_tumblr = $tumblr;
        if ($baseHostname) {
            $this->setBaseHostname($baseHostname);
        }
    }
    
    /**
     * Set target blog's base-hostname.
     *
     * @param string $baseHostname (required)
     */
    public function setBaseHostname($baseHostname) {
        $this->_baseHostname = $baseHostname;
    }
    
    /**
     * Retrieve blog info.
     * HTTP Method: GET, Authentication: API key, Details: http://www.tumblr.com/docs/en/api/v2#blog-info
     *
     * @param closure $callback (default=null)
     * @return array
     * @throw TumblrException
     */
    public function getInfo(Closure $callback = null) {
        return $this->_request('/info', null, $callback, false);
    }
    
    /**
     * Retrieve blog's likes.
     * HTTP Method: GET, Authentication: API key, Details: http://www.tumblr.com/docs/en/api/v2#blog-likes
     *
     * @param array $requestParams (default=null)
     * @param closure $callback    (default=null)
     * @return array
     * @throw TumblrException
     */
    public function getLikes(Array $requestParams = null, Closure $callback = null) {
        return $this->_request('/likes', $requestParams, $callback, false);
    }
    
    /**
     * Retrieve a blog's followers.
     * HTTP Method: GET, Authentication: OAuth, Details: http://www.tumblr.com/docs/en/api/v2#blog-followers
     *
     * @param array $requestParams (default=null)
     * @param closure $callback    (default=null)
     * @return array
     * @throw TumblrException
     */
    public function getFollowers(Array $requestParams = null, Closure $callback = null) {
        return $this->_request('/followers', $requestParams, $callback);
    }
    
    /**
     * Retrieve published posts.
     * HTTP Method: GET, Authentication: API key or OAuth
     *  Details: http://www.tumblr.com/docs/en/api/v2#posts
                 http://www.tumblr.com/docs/en/api/v2#blog-queue
                 http://www.tumblr.com/docs/en/api/v2#blog-drafts
                 http://www.tumblr.com/docs/en/api/v2#blog-submissions
     *
     * @param string $type         (default=null,available=text|quote|link|answer|video|audio|photo|chat)
     * @param array $requestParams (default=null)
     * @param closure $callback    (default=null)
     * @return array
     * @throw TumblrException
     */
    public function getPosts($type = null, Array $requestParams = null, Closure $callback = null) {
        $uri   = '/posts';
        $oauth = false;
        if ($type) {
            // Set posts type e.g: /posts/queue
            $uri .= '/'. $type;
            // Set oauth=true if type is queue|draft|submission
            if (preg_match('~^(?:queue|draft|submission)$~', $type)) {
                $oauth = true;
            }
        }
        return $this->_request($uri, $requestParams, $callback, $oauth);
    }
    
    /**
     * Retrieve a published post by ID.
     * HTTP Method: GET, Authentication: API key, Details: http://www.tumblr.com/docs/en/api/v2#posts
     *
     * @param string $id           (required,default=null)
     * @param closure $callback    (default=null)
     * @return array
     * @throw TumblrException
     */
    public function getPost($id = null, Closure $callback = null) {
        if ($id) {
            return $this->getPosts(null, array('id' => $id), $callback);
        }
        throw new TumblrException('ID is required.');
    }
    
    /**
     * Create a new blog post.
     * HTTP Method: POST, Authentication: OAuth, Details: http://www.tumblr.com/docs/en/api/v2#posting
     *
     * @param string $type         (required,default=null)
     * @param array $requestParams (required,default=array)
     * @param closure $callback    (default=null)
     * @return array
     * @throw TumblrException
     */
    public function addPost($type = null, Array $requestParams = array(), Closure $callback = null) {
        // Only check type, other errors are handled by API
        if ($type) {
            return $this->_request('/post', null, $callback, true, array_merge(
                array('type' => $type), $requestParams
            ));
        }
        throw new TumblrException('Type is required.');
    }
    
    /**
     * Edit a blog post.
     * HTTP Method: POST, Authentication: OAuth, Details: http://www.tumblr.com/docs/en/api/v2#editing
     *
     * @param string $id           (required,default=null)
     * @param array $requestParams (required,default=array)
     * @param closure $callback    (default=null)
     * @return array
     * @throw TumblrException
     */
    public function editPost($id = null, Array $requestParams = array(), Closure $callback = null) {
        // Only check ID, other errors are handled by API
        if ($id) {
            return $this->_request('/post/edit', null, $callback, true, array_merge(
                array('id' => $id), $requestParams
            ));
        }
        throw new TumblrException('ID is required.');
    }
    
    /**
     * Delete a post.
     * HTTP Method: POST, Authentication: OAuth, Details: http://www.tumblr.com/docs/en/api/v2#deleting-posts
     *
     * @param string $id           (required,default=null)
     * @param closure $callback    (default=null)
     * @return array
     * @throw TumblrException
     */
    public function deletePost($id = null, Closure $callback = null) {
        if ($id) {
            return $this->_request('/post/delete', null, $callback, true, array('id' => $id));
        }
        throw new TumblrException('ID is required.');
    }
    
    /**
     * Reblog a post.
     * HTTP Method: POST, Authentication: OAuth, Details: http://www.tumblr.com/docs/en/api/v2#reblogging
     *
     * @param string $id           (required,default=null)
     * @param string $reblogKey    (required,default=null)
     * @param string $comment      (default=null)
     * @param closure $callback    (default=null)
     * @return array
     * @throw TumblrException
     */
    public function reblogPost($id = null, $reblogKey = null, $comment = null, Closure $callback = null) {
        if ($id && $reblogKey) {
            return $this->_request('/post/reblog', null, $callback, true, array(
                'id' => $id, 'reblog_key' => $reblogKey, 'comment' => $comment
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
        // Always base-hostname is needed
        if (!$this->_baseHostname) {
            throw new TumblrException('Target base-hostname has not been set yet.');
        }
        $uri = sprintf('%s/blog/%s/%s', $this->_tumblr->getApiUrl(), $this->_baseHostname, trim($uri, '/'));
        // These are all needed "api_key" (/posts/text|quote|link|answer|video|audio|photo|chat)
        if (preg_match('~(?:info|likes|posts/*(?:text|quote|link|answer|video|audio|photo|chat|))$~i', $uri)) {
            $requestParams['api_key'] = $this->_tumblr->getApiKey();
        }
        // Append get params
        if (!empty($requestParams)) {
            $uri .= '?'. http_build_query($requestParams);
        }
        return $uri;
    }
}
