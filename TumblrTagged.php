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
 * @class TumblrTagged v0.1
 *
 * Provides access to the Tumblr API/v2 "tagged" method. Before using it
 * be sure that you already know all Tumblr API/v2 "tagged" method. See more
 * details here: http://www.tumblr.com/docs/en/api/v2#tagged-method
 */
class TumblrTagged
{
    protected
        // Tumblr Object
        $_tumblr;
    
    
    /**
     * Initialize a TumblrTagged Object.
     *
     * @param object $tumblr (required)
     */
    public function __construct(Tumblr $tumblr) {
        $this->_tumblr = $tumblr;
    }
    
    /**
     * Get posts with tag.
     * HTTP Method: GET, Authentication: API Key or OAuth, Details: http://www.tumblr.com/docs/en/api/v2#tagged-method
     *
     * @param string $tag          (required,default=null)
     * @param array $requestParams (default=null)
     * @param closure $callback    (default=null)
     * @return array (with executing $callback if available)
     * @throw TumblrException
     */
    public function getPosts($tag = null, Array $requestParams = null, Closure $callback = null) {
        if ($tag) {
            $query = array('tag' => $tag);
            if (!empty($requestParams)) {
                $query += $requestParams;
            }
            $uri  = sprintf('%s/tagged?api_key=%s', $this->_tumblr->getApiUrl(), $this->_tumblr->getApiKey());
            $uri .= '&'. http_build_query($query);
            
            $response = $this->_tumblr->request($uri, false);
            return is_callable($callback)
                ? call_user_func($callback, $response)
                : $response;
        }
        throw new TumblrException('Tag is required.');
    }
}
