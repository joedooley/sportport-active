<?php

class Model_SQ_BlockSettingsSeo
{

    var $eTypes;
    var $appleSizes;

    public function __construct()
    {
        $this->appleSizes = preg_split('/[,]+/', _SQ_MOBILE_ICON_SIZES);
    }

    /**
     * Check if ecommerce is installed
     * @return boolean
     */
    public function isEcommerce()
    {
        if (isset($this->eTypes)) {
            return $this->eTypes;
        }


        $this->eTypes = array('product', 'wpsc-product');
        foreach ($this->eTypes as $key => $type) {
            if (!in_array($type, get_post_types())) {
                unset($this->eTypes[$key]);
            }
        }

        if (!empty($this->eTypes)) {
            return $this->eTypes;
        }

        return false;
    }

    /**
     * Check the google code saved at settings
     *
     * @return string
     */
    public function checkGoogleWTCode($code)
    {

        if ($code <> '') {
            if (strpos($code, 'content') !== false) {
                preg_match('/content\\s*=\\s*[\'\"]([^\'\"]+)[\'\"]/i', $code, $result);
                if (isset($result[1]) && !empty($result[1])) $code = $result[1];
            }
            if (strpos($code, '"') !== false) {
                preg_match('/[\'\"]([^\'\"]+)[\'\"]/i', $code, $result);
                if (isset($result[1]) && !empty($result[1])) $code = $result[1];
            }

            if ($code == '') SQ_Error::setError(__("The code for Google Webmaster Tool is incorrect.", _SQ_PLUGIN_NAME_));
        }
        return $code;
    }

    /**
     * Check the google code saved at settings
     *
     * @return string
     */
    public function checkGoogleAnalyticsCode($code)
    {
        //echo $code;
        if ($code <> '') {
            if (strpos($code, 'GoogleAnalyticsObject') !== false) {
                preg_match('/ga\(\'create\',[^\'"]*[\'"]([^\'"]+)[\'"],/i', $code, $result);
                if (isset($result[1]) && !empty($result[1])) $code = $result[1];
            }

            if (strpos($code, '"') !== false) {
                preg_match('/[\'\"]([^\'\"]+)[\'\"]/i', $code, $result);
                if (isset($result[1]) && !empty($result[1])) $code = $result[1];
            }

            if (strpos($code, 'UA-') === false) {
                $code = '';
                SQ_Error::setError(__("The code for Google Analytics is incorrect.", _SQ_PLUGIN_NAME_));
            }
        }
        return trim($code);
    }

    /**
     * Check the Facebook code saved at settings
     *
     * @return string
     */
    public function checkFavebookInsightsCode($code)
    {
        if ($code <> '') {
            if (strpos($code, 'content') !== false) {
                preg_match('/content\\s*=\\s*[\'\"]([^\'\"]+)[\'\"]/i', $code, $result);
                $code = '';
                if (isset($result[1]) && !empty($result[1])) {
                    $code = $result[1];
                }
            }

            if (strpos($code, 'facebook.com/') !== false) {
                preg_match('/facebook.com\/([^\/]+)/i', $code, $result);
                $code = '';
                if (isset($result[1]) && !empty($result[1])) {
                    if (is_string($result[1])) {
                        $html = SQ_Tools::sq_remote_post('http://findmyfbid.com/', array('url' => $result[1]));
                        if ($html <> '' && strpos($html, '<code') !== false) {
                            $result = array();
                            if (preg_match('/<code[^>]*>([0-9]+)<\/code[^>]*>/i', $html, $result)) {
                                if (isset($result[1]) && !empty($result[1])) {
                                    $code = $result[1];
                                }
                            }
                        }
                    } elseif(is_numeric($result[1])) {
                        $code = $result[1];
                    }
                }
            }

            if (strpos($code, '"') !== false) {
                preg_match('/[\'\"]([^\'\"]+)[\'\"]/i', $code, $result);
                $code = '';
                if (isset($result[1]) && !empty($result[1])) {
                    $code = $result[1];
                }
            }

            if ($code == '') {
                SQ_Error::setError(__("The code for Facebook is incorrect.", _SQ_PLUGIN_NAME_));
            }
        }
        return $code;
    }

    /**
     * Check the Pinterest code saved at settings
     *
     * @return string
     */
    public function checkPinterestCode($code)
    {
        if ($code <> '') {
            if (strpos($code, 'content') !== false) {
                preg_match('/content\\s*=\\s*[\'\"]([^\'\"]+)[\'\"]/i', $code, $result);
                if (isset($result[1]) && !empty($result[1])) $code = $result[1];
            }

            if (strpos($code, '"') !== false) {
                preg_match('/[\'\"]([^\'\"]+)[\'\"]/i', $code, $result);
                if (isset($result[1]) && !empty($result[1])) $code = $result[1];
            }

            if ($code == '') SQ_Error::setError(__("The code for Pinterest is incorrect.", _SQ_PLUGIN_NAME_));
        }
        return $code;
    }

    /**
     * Check the Bing code saved at settings
     *
     * @return string
     */
    public function checkBingWTCode($code)
    {
        if ($code <> '') {
            if (strpos($code, 'content') !== false) {
                preg_match('/content\\s*=\\s*[\'\"]([^\'\"]+)[\'\"]/i', $code, $result);
                if (isset($result[1]) && !empty($result[1])) $code = $result[1];
            }

            if (strpos($code, '"') !== false) {
                preg_match('/[\'\"]([^\'\"]+)[\'\"]/i', $code, $result);
                if (isset($result[1]) && !empty($result[1])) $code = $result[1];
            }

            if ($code == '') SQ_Error::setError(__("The code for Bing is incorrect.", _SQ_PLUGIN_NAME_));
        }
        return $code;
    }

    /**
     * Check the twitter account
     *
     * @return string
     */
    public function checkTwitterAccount($account)
    {
        if (SQ_ObjController::getModel('SQ_Frontend')->getTwitterAccount($account) === false) {
            SQ_Error::setError(__("The twitter account is incorrect", _SQ_PLUGIN_NAME_));
        }
        if ($account <> '' && strpos($account, 'twitter.') === false) {
            $account = 'https://twitter.com/' . $account;
        }
        return $account;
    }

    /**
     * Check the google + account
     *
     * @return string
     */
    public function checkGoogleAccount($account)
    {
        if ($account <> '' && strpos($account, 'google.') === false) {
            $account = 'https://plus.google.com/' . $account;
        }
        return str_replace(" ", "+", $account);
    }

    /**
     * Check the google + account
     *
     * @return string
     */
    public function checkLinkeinAccount($account)
    {
        if ($account <> '' && strpos($account, 'linkedin.') === false) {
            $account = 'https://www.linkedin.com/in/' . $account;
        }
        return $account;
    }

    /**
     * Check the facebook account
     *
     * @return string
     */
    public function checkFacebookAccount($account)
    {
        if ($account <> '' && strpos($account, 'facebook.com') === false) {
            $account = 'https://www.facebook.com/' . $account;
        }
        return $account;
    }

    public function checkPinterestAccount($account)
    {
        if ($account <> '' && strpos($account, 'pinterest.com') === false) {
            $account = 'https://www.pinterest.com/' . $account;
        }
        return $account;
    }

    public function checkInstagramAccount($account)
    {
        if ($account <> '' && strpos($account, 'instagram.com') === false) {
            $account = 'https://www.instagram.com/' . $account;
        }
        return $account;
    }


    /**
     * Add the image to the root path
     *
     * @param string $file
     * @param string $path
     * @return array [name (the name of the file), favicon (the path of the ico), message (the returned message)]
     *
     */
    public function addFavicon($file, $path = ABSPATH)
    {

        /* get the file extension */
        $file_name = explode('.', $file['name']);
        $file_type = strtolower($file_name[count($file_name) - 1]);

        $out = array();
        $out['tmp'] = _SQ_CACHE_DIR_ . strtolower(md5($file['name']) . '_tmp.' . $file_type);
        $out['favicon'] = _SQ_CACHE_DIR_ . strtolower(md5($file['name']) . '.' . $file_type);
        foreach ($this->appleSizes as $size) {
            $out['favicon' . $size] = _SQ_CACHE_DIR_ . strtolower(md5($file['name']) . '.' . $file_type . $size);
        }

        /* if the file has a name */
        if (!empty($file['name'])) {
            /* Check the extension */
            $file_type = strtolower($file_type);
            $files = array('ico', 'jpeg', 'jpg', 'gif', 'png');
            $key = in_array($file_type, $files);

            if (!$key) {
                SQ_Error::setError(__("File type error: Only ICO, JPEG, JPG, GIF or PNG files are allowed.", _SQ_PLUGIN_NAME_));
                return;
            }

            /* Check for error messages */
            if (!$this->checkFunctions()) {
                SQ_Error::setError(__("GD error: The GD library must be installed on your server.", _SQ_PLUGIN_NAME_));
                return;
            } else {
                /* Delete the previous file if exists */
                if (is_file($out['favicon'])) {
                    if (!unlink($out['favicon'])) {
                        SQ_Error::setError(__("Delete error: Could not delete the old favicon.", _SQ_PLUGIN_NAME_));
                        return;
                    }
                }

                /* Upload the file */
                if (!move_uploaded_file($file['tmp_name'], $out['tmp'])) {
                    SQ_Error::setError(__("Upload error: Could not upload the favicon.", _SQ_PLUGIN_NAME_));
                    return;
                }

                /* Change the permision */
                if (!chmod($out['tmp'], 0755)) {
                    SQ_Error::setError(__("Permission error: Could not change the favicon permissions.", _SQ_PLUGIN_NAME_));
                    return;
                }

                if ($file_type <> 'ico') {
                    /* Save the file */
                    if ($out['tmp']) {
                        $ico = SQ_ObjController::getModel('SQ_Ico');
                        $ico->set_image($out['tmp'], array(32, 32));
                        if ($ico->save_ico($out['favicon'])) {
                            if (file_exists($path . "/" . 'favicon.ico')) {
                                $ico->remove_ico($path . "/" . 'favicon.ico');
                            }
                            if (!is_multisite()) {
                                $ico->save_ico($path . "/" . 'favicon.ico');
                            }
                        }
                        foreach ($this->appleSizes as $size) {
                            $ico->set_image($out['tmp'], array($size, $size));
                            $ico->save_ico($out['favicon' . $size]);
                        }
                    } else {
                        SQ_Error::setError(__("ICO Error: Could not create the ICO from file. Try with another file type.", _SQ_PLUGIN_NAME_));
                    }
                } else {
                    copy($out['tmp'], $out['favicon']);
                    foreach ($this->appleSizes as $size) {
                        copy($out['tmp'], $out['favicon' . $size]);
                    }

                    unset($out['tmp']);
                    if (file_exists($path . "/" . 'favicon.ico')) {
                        $ico = SQ_ObjController::getModel('SQ_Ico');
                        $ico->remove_ico($path . "/" . 'favicon.ico');
                    }
                    if (!is_multisite()) {
                        $ico = SQ_ObjController::getModel('SQ_Ico');
                        $ico->save_ico($path . "/" . 'favicon.ico');
                    }
                }
                unset($out['tmp']);
                $out['message'] = __("The favicon has been updated.", _SQ_PLUGIN_NAME_);

                return $out;
            }
        }
    }

    private function checkFunctions()
    {
        $required_functions = array('getimagesize', 'imagecreatefromstring', 'imagecreatetruecolor', 'imagecolortransparent', 'imagecolorallocatealpha', 'imagealphablending', 'imagesavealpha', 'imagesx', 'imagesy', 'imagecopyresampled',);

        foreach ($required_functions as $function) {
            if (!function_exists($function)) {
                SQ_Error::setError("The PHP_ICO class was unable to find the $function function, which is part of the GD library. Ensure that the system has the GD library installed and that PHP has access to it through a PHP interface, such as PHP's GD module. Since this function was not found, the library will be unable to create ICO files.");
                return false;
            }
        }

        return true;
    }

}
