<style type="text/css">
    div#response_error_ebay{
    background-color: #fff;
    border-left: 4px solid #7ad03a;
    -webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
    box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
}
</style>
<?php

/**SettingsPage
 * User: Subash
 */
class SettingsPage
{
    const OptionPrefix = 'cpf_';
    public $message;
    public $error_message;

    /**
     * SettingsPage constructor.
     */
    public function __construct()
    {
        //self::loadNavigationTab();
    }

    /**
     *
     */
    public function loadNavigationTab()
    {
        //functions to display cart-product-feed version and checks for updates
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'accounts';
        // if ( @$_REQUEST['page'] == 'wplister-settings-categories' ) $active_tab = 'categories';
        $cpf_settings_url = 'admin.php?page=eBay_settings_tabs';
        CPF_print_info();
        //require_once('ebay_listing.php');
        if (!isset($_GET['site_id'])) {
            $style = 'display:block';
        } else {
            $style = 'display:none';
        }
        ?>

    <h2 class="nav-tab-wrapper" style="<?php echo $style; ?>">
        <a href="<?php echo $cpf_settings_url; ?>&tab=settings"
           class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"><?php _e('General Settings', 'cart-product-strings') ?></a>
        <a href="<?php echo $cpf_settings_url; ?>&tab=accounts"
           class="nav-tab <?php echo $active_tab == 'accounts' ? 'nav-tab-active' : ''; ?>"><?php echo _e('Accounts', 'cart-product-strings') ?></a>
        <a href="<?php echo $cpf_settings_url; ?>&tab=createfeed"
           class="nav-tab <?php echo $active_tab == 'createfeed' ? 'nav-tab-active' : ''; ?>"><?php echo _e('Create Feed', 'cart-product-strings') ?></a>
        <a href="<?php echo $cpf_settings_url; ?>&tab=managefeed"
           class="nav-tab <?php echo $active_tab == 'managefeed' ? 'nav-tab-active' : ''; ?>"><?php echo _e('Manage Feed', 'cart-product-strings') ?></a>
        </h2><?php
        switch ($active_tab) {
            case 'accounts':
                $this->get_accounts_page();
                break;
            case 'settings';
                $this->get_settings_page();
                break;
            case 'createfeed';
                $this->get_createfeeds_page();
                break;
            case 'managefeed';
                $this->get_managefeed_page();
            default:
                # code...
                break;
        }
    }

    public function get_accounts_page()
    {
        if (!isset($_GET['site_id']))
            require_once('cart-product-ebay-settings.php');
        else
            $this->error_message = 'You will be redirect soon to login page';
        require_once(CPF_PATH . '/views/accounts/settings_add_account.php');
        $this->handleSubmit();
    }


    public function get_settings_page()
    {
        require_once 'cart-product-wpincludes.php';
        require_once 'core/classes/dialoglicensekey.php';
        include_once 'core/classes/dialogfeedpage.php';

        global $cp_feed_order, $cp_feed_order_reverse;
        require_once 'core/classes/dialogfeedsettings.php';
        require_once  'core/classes/dialogfeedeBaysettings.php';
        require_once 'core/data/savedfeed.php';

        echo PFeedSettingsDialogs::refreshTimeOutDialog();
        echo PeBayFeedSettings::defaulteBaySettings();

    }

    public function get_createfeeds_page()
    {
        require_once 'cart-product-wpincludes.php';
        require_once 'core/classes/dialoglicensekey.php';
        include_once 'core/classes/dialogfeedpage.php';
        require_once 'core/feeds/basicfeed.php';

        global $pfcore;
        $pfcore->trigger('cpf_init_feeds');
        $action = '';
        $source_feed_id = -1;
        $message2 = NULL;

         if (isset($_POST['action']))
            $action = $_POST['action'];
        if (isset($_GET['action']))
            $action = $_GET['action'];

        switch ($action) {
            case 'update_license':
                //I think this is AJAX only now -K
                //No... it is still used (2014/08/25) -K
                if (isset($_POST['license_key'])) {
                    $licence_key = $_POST['license_key'];
                    if ($licence_key != '')
                        update_option('cp_licensekey', $licence_key);
                }
                break;
            case 'reset_attributes':
                //I don't think this is used -K
                global $wpdb, $woocommerce;
                $attr_table = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
                $sql = "SELECT attribute_name FROM " . $attr_table . " WHERE 1";
                $attributes = $wpdb->get_results($sql);
                foreach ($attributes as $attr)
                    delete_option($attr->attribute_name);
                break;
            case 'edit':
                $action = '';
                $source_feed_id = $_GET['id'];
                break;
        }

        if (isset($action) && (strlen($action) > 0))
            echo "<script> window.location.assign( '" . admin_url() . "admin.php?page=cart-product-feed-admin' );</script>";

        if (isset($_GET['debug'])) {
            $debug = $_GET['debug'];
            if ($debug == 'phpinfo') {
                phpinfo(INFO_GENERAL + INFO_CONFIGURATION + INFO_MODULES);
                return;
            }
            if ($debug == 'reg') {
                echo "<pre>\r\n";
                new PLicense(true);
                echo "</pre>\r\n";
            }
        }

        # Get Variables from storage ( retrieve from wherever it's stored - DB, file, etc... )

        $reg = new PLicense();

        //Main content
        echo '
	<script type="text/javascript">
	jQuery( document ).ready( function( $ ) {
		ajaxhost = "' . plugins_url('/', __FILE__) . '";
		jQuery( "#selectFeedType" ).val( "eBaySeller" );
		doFetchLocalCategories();
        doSelectFeed();
		feed_id = ' . $source_feed_id . ';
	} );
	</script>';

        //WordPress Header ( May contain a message )

        global $message;
        if (strlen($message) > 0 && strlen($reg->error_message) > 0)
            $message .= '<br>'; //insert break after local message (if present)
        $message .= $reg->error_message;
        if (strlen($message) > 0) {
            //echo '<div id="setting-error-settings_updated" class="error settings-error">'
            echo '<div id="setting-error-settings_updated" class="updated settings-error">
			  <p>' . $message . '</p>
			  </div>';
        }

        if ($source_feed_id == -1) {
            //Page Header
            echo PFeedPageDialogs::pageHeader();
            //Page Body
            echo PFeedPageDialogs::pageBody();
        } else {
            require_once dirname(__FILE__) . '/core/classes/dialogeditfeed.php';
            echo PEditFeedDialog::pageBody($source_feed_id);
        }

        if (!$reg->valid) {
            //echo PLicenseKeyDialog::large_registration_dialog( '' );
        }

    }

    public function get_managefeed_page()
    {
        //echo 'ManageFeed Page';
        require_once 'cart-product-wpincludes.php';
        require_once 'core/classes/dialoglicensekey.php';
        include_once 'core/classes/dialogfeedpage.php';

        global $pfcore;
        $pfcore->trigger('cpf_init_feeds');
        $reg = new PLicense();
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        switch ($action) {
            case 'uploadFeed' :
            $message = NULL;

            require_once ('core/ajax/wp/uploadFeed.php');
            $feed_id = $_GET['id'];
            $feedObj = new uploadFeed();
            $message = $feedObj->uploadToEbay($feed_id);
            global $EC;
            if($EC->message){
                echo '<div id="response_error_ebay" class="response_error_ebay">'.$EC->message.'</div>';
            }
            break;
        case 'delete_account' :
            $message = NULL;
            require_once('core/model/eBayAccount.php');
            $account_id = $_GET['id'];
            $eBayAccount = new CPF_eBayAccount($account_id);
            $eBayAccount->delete();
            //wp_redirect(get_admin_url() . 'admin.php?page=eBay_settings_tabs' , "Success");
            echo "<script> window.location.assign( '" . admin_url() . "admin.php?page=eBay_settings_tabs' );</script>";
            

        case 'make_account_default' : 
            require_once('core/model/eBayAccount.php');
            $account_id = $_GET['id'];
            $eBayAccount = new CPF_eBayAccount($account_id);
            $eBayAccount->makeAccountDefault();
            echo "<script> window.location.assign( '" . admin_url() . "admin.php?page=eBay_settings_tabs' );</script>";
            break;
        }
       
         require_once 'cart-product-manage-feeds.php';

    }

    public function handleSubmit()
    {

        $action = isset($_GET['action']) ? $_GET['action'] : '';
        if ($action == 'wplRedirectToAuthURL') {
            // get auth url
            // require_once 'core/classes/eBaycontroller.php';
            global $EC;
            $EC->initEC();
            $auth_url = $EC->getAuthUrl();
            //echo "URLL".$auth_url;
            $EC->closeEbay();
            //echo $auth_url;
            //wp_redirect($auth_url);
            ?>
            <script>
                window.location.href = '<?php echo $auth_url;?>';
            </script>
            <?php
            exit();

        }
        if ($action == 'fetchToken') {
            // action FetchToken
            // FetchToken
            global $EC;
            $EC->initEC();
            $ebay_token = $EC->doFetchToken();
            $EC->closeEbay();
            $url = site_url() . '/wp-admin/admin.php?page=eBay_settings_tabs&tab=accounts';
            if ($ebay_token) {
                $EC->newAccount($ebay_token);
               ?>
                <script>
                    window.location.href = '<?php echo $url;?>';
            </script>
            <?php
            exit();
            }else {
                $this->error_message = "There was a problem fetching your token. Make sure you follow the instructions.";
            }
        }
    }


}

?>

<script type="text/javascript">
   function doUploadFeed_1(){
        var ajaxhost = "<?php echo plugins_url("/"  , (__FILE__));?>";
        var cmdUploadFeed_1 = 'core/ajax/wp/uploadfeed_ebay.php';
        jQuery.ajax({
            type : "POST",
            url : ajaxhost + cmdUploadFeed_1,
            data : {service_name : 'eBaySeller'},
            success : function(response){
                console.log(response);
            }
        });
   }
</script>
