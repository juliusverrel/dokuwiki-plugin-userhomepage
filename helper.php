<?php
/**
 * Helper Component for the Userhomepage Plugin
 *
 * @author: Simon Delage <simon.geekitude@gmail.com>
 * @license: CC Attribution-Share Alike 3.0 Unported <http://creativecommons.org/licenses/by-sa/3.0/>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class helper_plugin_userhomepage extends DokuWiki_Plugin {

    // Returns the ID of current user's private namespace start page (even if it doesn't exist)
    function getPrivateID() {
        if ($this->getConf('group_by_name')) {
            // private:s:simon or private:s:simon_delage
            $this->private_ns = cleanID($this->getConf('users_namespace').':'.strtolower(substr($this->privateNamespace(), 0, 1)).':'. $this->privateNamespace());
        } else {
            // private:simon or private:simon_delage
            $this->private_ns = cleanID($this->getConf('users_namespace').':'. $this->privateNamespace());
        }
        // ...:start.txt
        return $this->private_page = $this->private_ns.':'.$this->privateStart();
    }

    // Returns the ID of any (or current) user's public page (even if it doesn't exist)
    function getPublicID($userLogin=null) {
        global $conf;

        if ($userLogin == null) {
            $userLogin = $this->privateNamespace();
        }
        if (strpos($this->getConf('public_pages_ns'),':%NAME%:%START%') !== false) {
            $target = str_replace('%NAME%', $userLogin, $this->getConf('public_pages_ns'));
            $target = str_replace('%START%', $conf['start'], $target);
        } else {
            $target = $this->getConf('public_pages_ns').':'.$userLogin;
        }
        return $this->public_page = cleanID($target);
    }

    // Returns a link to current user's private namespace start page (even if it doesn't exist)
    // If @param == "loggedinas", the link will be wraped in an <li> element
    function getPrivateLink($param=null) {
        global $INFO;
        global $lang;
        $pageId = $this->getPrivateID();
        $class ='class="uhp_private wikilink2"';
        if (page_exists($pageId)) {
            $class ='class="uhp_private wikilink1"';
        }
        if ($param == "loggedinas") {
            return '<li>'.$lang['loggedinas'].' <a href="'.wl($pageId).'" '.$class.' rel="nofollow" title="'.$this->getLang('privatenamespace').'">'.$INFO['userinfo']['name'].' ('.$_SERVER['REMOTE_USER'].')</a></li>';
        } elseif ($param != null) {
            return '<a href="'.wl($pageId).'" '.$class.' rel="nofollow" title="'.$this->getLang('privatenamespace').'">'.$param.'</a>';
        } else {
            return '<a href="'.wl($pageId).'" '.$class.' rel="nofollow" title="'.$this->getLang('privatenamespace').'">'.$this->getLang('privatenamespace').'</a>';
        }
    }

    // Returns a link to current user's public page (even if it doesn't exist)
    // If @param == "loggedinas", the link will be wraped in an <li> element
    function getPublicLink($param=null) {
        global $INFO;
        global $lang;
        $pageId = $this->getPublicID();
        $class ='class="uhp_public wikilink2"';
        if (page_exists($pageId)) {
            $class ='class="uhp_public wikilink1"';
        }
        if ($param == "loggedinas") {
            return '<li>'.$lang['loggedinas'].' <a href="'.wl($pageId).'" '.$class.' rel="nofollow" title="'.$this->publicString().'">'.$INFO['userinfo']['name'].' ('.$_SERVER['REMOTE_USER'].')</a></li>';
        } elseif ($param != null) {
            return '<a href="'.wl($pageId).'" '.$class.' rel="nofollow" title="'.$this->publicString().'">'.$param.'</a>';
        } else {
            return '<a href="'.wl($pageId).'" '.$class.' rel="nofollow" title="'.$this->publicString().'">'.$this->publicString().'</a>';
        }
    }

    // Returns a more or less complex 'Logged in as' string with link(s) to private and/or public page
    function getComplexLoggedInAs() {
        global $INFO;
        global $lang;
        // If user's private namespace and public page exist, return a 'Logged in as' string with both styled links)
        if ((page_exists($this->getPrivateID())) && (page_exists($this->getPublicID()))) {
            //return '<li>'.$lang['loggedinas'].' <a href="'.wl($this->getPrivateID()).'"  class="uhp_private" rel="nofollow" title="'.$this->getLang('privatenamespace').'">'.$INFO['userinfo']['name'].'</a> (<a href="'.wl($this->getPublicID()).'"  class="uhp_public" rel="nofollow" title="'.$this->publicString().'">'.$_SERVER['REMOTE_USER'].'</a>)</li>';
            return '<li>'.$lang['loggedinas'].' '.$this->getPrivateLink($INFO['userinfo']['name']).' ('.$this->getPublicLink($_SERVER['REMOTE_USER']).')</li>';
        // Else if only private namespace exists, return 'Logged in as' string with private namespace styled link
        } elseif (page_exists($this->getPrivateID())) {
            return $this->getPrivateLink("loggedinas");
        // Else if only public page exists, return 'Logged in as' string with public page styled link
        } elseif (page_exists($this->getPublicID())) {
            return $this->getPublicLink("loggedinas");
        // Else default back to standard string
        } else {
            return '<li class="user">'.$lang['loggedinas'].' '.userlink().'</li>';
        }
    }

    // Returns a link to any user's public page (user login is required and page must exist)
    // This is to provide proper "Last update by" link
    function getAnyPublicLink($userLogin) {
        global $lang;
        if ($userLogin != null) {
            $publicID = $this->getPublicID($userLogin);
            $class ='class="uhp_public wikilink2"';
            if (page_exists($publicID)) {
                $class ='class="uhp_public wikilink1"';
            }
            $result = '<a href="'.wl($publicID).'" '.$class.' rel="nofollow" title="'.$this->publicString().'">'.editorinfo($userLogin, true).'</a>';
            return $result;
        } else {
            return false;
        }
    }

	function getButton($type="private") {
        global $INFO;
        global $lang;
		if ($type == "private") {
			echo '<form class="button btn_show" method="post" action="doku.php?id='.$this->getPrivateID().'"><input class="button" type="submit" value="'.$this->getLang('privatenamespace').'"/></form>';
        } elseif ($type == "public") {
			echo '<form class="button btn_show" method="post" action="doku.php?id='.$this->getPublicID().'"><input class="button" type="submit" value="'.$this->publicString().'"/></form>';
		}
	}

    // Returns an array containing id and language of Private NS Start Page and/or Public Page (depending on options, page existance isn't checked)
	function getElements() {
        global $INFO;
        $return = array();
        // Don't return anything if no known user is logged in
        if ($_SERVER['REMOTE_USER'] != null) {
            // Add PRIVATE NAMESPACE START PAGE INFO IF NEEDED (is required by options)
            if ($this->getConf('create_private_ns')) {
                $return['private'] = array();
                $return['private']['id'] = $this->getPrivateID();
                $return['private']['string'] = $this->getLang('privatenamespace');
                $return['private']['link'] = $this->getPrivateLink($INFO['userinfo']['name']);
            }
            // Add PUBLIC PAGE INFO IF NEEDED (is required by options)
            if ($this->getConf('create_public_page')) {
                $return['public'] = array();
                $return['public']['id'] = $this->getPublicID();
                $return['public']['string'] = $this->publicString();
                $return['public']['link'] = $this->getPublicLink($_SERVER['REMOTE_USER']);
            }
        }
        return $return;
    }

    function privateNamespace() {
        if ($this->getConf('use_name_string')) {
            global $INFO;
            $raw_string = cleanID($INFO['userinfo']['name']);
            // simon_delage
            return $raw_string;
        } else {
            // simon
            return strtolower($_SERVER['REMOTE_USER']);
        }
    }

    function privateStart() {
        if ( $this->getConf('use_start_page')) {
            global $conf;
            return cleanID($conf['start']);
        } else {
            return $this->privateNamespace();
        }
    }

    function publicString() {
        if (strpos($this->getConf('public_pages_ns'),':%NAME%:%START%') !== false) {
            return $this->getLang('publicnamespace');
        } else {
            return $this->getLang('publicpage');
        }
    }

}
