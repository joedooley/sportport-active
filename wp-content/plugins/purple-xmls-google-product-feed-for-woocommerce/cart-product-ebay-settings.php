<style type="text/css">

    div.tablenav.top {
        display: none;
    }

    th.column-site,
    th.column-user_name {
        width: 20%;
    }

    th.column-valid_until {
        width: 20%;
    }

    #AuthSettingsBox ol li {
        margin-bottom: 25px;
    }

    #AuthSettingsBox ol li > small {
        margin-left: 4px;
    }

    #poststuff #side-sortables .postbox input.text_input,
    #poststuff #side-sortables .postbox select.select {
        width: 50%;
    }

    #poststuff #side-sortables .postbox label.text_label {
        width: 45%;
    }

    #poststuff #side-sortables .postbox p.desc {
        margin-left: 5px;
    }

</style>

<div class="wrap cpf-page">
    <?php
    $cpf_ebay_accounts = CPF_eBayAccount::getAll();
    $cpf_default_account = CPF_eBayAccount::getDefaultAccount();
    if (!$cpf_default_account) {
        $cpf_default_account = 1;
    }

    //echo $cpf_message
    ?>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">

            <div id="postbox-container-1" class="postbox-container">
                <div id="side-sortables" class="meta-box">


                    <!-- first sidebox -->
                    <div class="postbox" id="submitdiv">
                        <!--<div title="Click to toggle" class="handlediv"><br></div>-->
                        <h3><span><?php echo __('Account Status ', 'cart-product-strings'); ?></span></h3>
                        <div class="inside">

                            <div id="submitpost" class="submitbox">

                                <div id="misc-publishing-actions">
                                    <div class="misc-pub-section">
                                        <?php if (sizeof($cpf_ebay_accounts) == 0) : ?>
                                            <p><?php echo __('Cart Product Feed is not linked to your eBay account yet. ', 'cart-product-strings') ?></p>
                                        <?php elseif (!$cpf_default_account) : ?>
                                            <p><?php echo __('You need to select a default account. ', 'cart-product-strings') ?></p>
                                        <?php else : ?>
                                            <p>
                                                <?php echo sprintf(__('Your default account is <b>%s</b>. ', 'cart-product-strings '), CPF_eBayAccount::getAccountTitle($cpf_default_account)) ?>
                                            </p>
                                            <p>
                                                <?php echo __('The default account will always be used by Cart Product Feed .', 'cart-product-strings') ?>
                                            </p>
                                        <?php endif; ?>


                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="postbox dev_box" id="DevToolsBox" style="display:none">
                        <h3><span><?php echo __('Developer ', 'wplister'); ?></span></h3>
                        <div class="inside">
                            <p>
                                <a href="<?php echo $wpl_form_action ?>&action=wple_add_dev_account"
                                   class="button-secondary">Add Developer Account</a>
                            </p>
                            <p>
                                This is only intended for developers.
                            </p>
                        </div>
                    </div>

                    

                </div>
            </div> <!-- #postbox-container-1 -->


            <!-- #postbox-container-2 -->
            <div id="postbox-container-2" class="postbox-container">
                <div class="meta-box-sortables ui-sortable">

                    <?php if (sizeof($cpf_ebay_accounts) == 0) : ?>

                        <div class="postbox" id="AuthSettingsBox">
                            <h3 class="hndle"><span><?php echo __('Welcome ', 'cart-product-strings') ?></span></h3>
                            <div class="inside">
                                <p>
                                    <strong><?php echo __('Before you can begin listing your products on eBay, you need to set up your eBay account. ', 'cart-product-strings') ?></strong>
                                </p>
                                <p>
                                    <?php echo __('Please select the eBay site you want to use and follow the instructions that will appear below. ', 'cart-product-strings') ?>
                                </p>
                            </div>
                        </div>

                    <?php else: ?>

                        <!-- show accounts table -->
                        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
                        <form id="accounts-filter" method="post" action="<?php echo $wpl_form_action; ?>">
                            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                            <!-- Now we can render the completed list table -->
                            <?php displayAccountsTable($cpf_ebay_accounts); ?>
                        </form>

                        <div class="postbox" id="AccountsBox" style="display:none">
                            <h3 class="hndle"><span><?php echo __('Accounts ', 'wplister') ?></span></h3>
                            <div class="inside">

                            </div>
                        </div>

                    <?php endif; // $cpf_ebay_accounts  ?>

                    <?php if ((sizeof($cpf_ebay_accounts) == 0)) : ?>
                        <?php
                        $max_accounts = apply_filters('wple_max_number_of_accounts ', 100);
                        if (sizeof($cpf_ebay_accounts) < $max_accounts) {
                            require_once(CPF_PATH . '/views/accounts/settings_add_account.php');
                        }
                        ?>
                    <?php endif; ?>

                </div> <!-- .meta-box-sortables -->
            </div> <!-- #postbox-container-1 -->


        </div> <!-- #post-body -->
        <br class="clear">
    </div> <!-- #poststuff -->


    <?php
    if (isset($_REQUEST['debug'])) {
        echo "<pre>";
        print_r($cpf_ebay_accounts);
        echo "</pre>";
    }
    ?>
    <?php #echo "<pre>";print_r($wpl_ebay_markets);echo"</pre>";   ?>


    <script type="text/javascript">
        jQuery(document).ready(
            function () {

                // ask again before deleting items
                jQuery('a.delete').on('click', function () {
                    return confirm("<?php echo __('Are you sure you want to remove this account from Cart Product List? ', 'cart-product-strings') ?>");
                });
                // ask again before deleting items
                jQuery('.row-actions .delete_account a').on('click', function () {
                    return confirm("<?php echo __('Are you sure you want to remove this account from Cart Product List? ', 'cart-product-strings') ?>");
                })

            }
        );
        //ajax function to make default ebay account for cart product feed
        function makeAccountDefault(account_id) {
            var ajaxhost = "<?php echo plugins_url('/', __FILE__); ?>";
            var cmdDefaultAccount = "core/ajax/wp/eBay_account.php";
            var data = {account_id: account_id, ajaxunq: ajaxUnq()};
            jQuery.ajax({
                type: 'POST',
                url: ajaxhost + cmdDefaultAccount,
                data: data,
                dataType: 'json',
                success: function (result) {
                    console.log('success');
                    if (result.status) {
                        location.reload();
                    } else {
                        alert('This is your current default account.');
                    }
                },
                error: function (result) {
                    console.log('Error');
                }

            });

        }//makeAccountDefault End


        function ajaxUnq() {
            var d = new Date();
            var unq = d.getYear() + '' + d.getMonth() + '' + d.getDay() + '' + d.getHours() + '' + d.getMinutes() + '' + d.getSeconds();
            return unq;
        }

    </script>

    <?php

    function displayAccountsTable($cpf_ebay_accounts)
    {
        $html = '';
        $html .= '<table class = "cp-list-table widefat fixed striped accounts">
            <thead>
            <tr>
            <th scope = "col" id = "details" class = "manage-column column-details column-primary">Account</th>
            <th scope = "col" id = "user_name" class = "manage-column column-user_name">User</th>
            <th scope = "col" id = "site" class = "manage-column column-site">Site</th>
            <th scope = "col" id = "valid_until" class = "manage-column column-valid_until">Valid Until</th>
            </tr>
            </thead>
            <tbody id = "the-list" data-cpf-lists = "list:ebay_account">';
        foreach ($cpf_ebay_accounts as $data => $records) {
            if ($records->default_account == 1) {
               $disabled = 'class = "disabled " ';
            } else {
                $disabled = '';
            }
        $action_url = get_admin_url() . 'admin.php?page=cart-product-feed-admin&action=eBay_action&id=' .$records->id;
         $html .= '<tr><td>' . $records->title . ' <div class="row-actions"><span class="id">ID: '.$records->id.' | </span><span class="delete">
         <a href="'.get_admin_url() . 'admin.php?page=cart-product-feed-admin&action=delete_account&id=' .$records->id.'&task=0" title="Delete this account" rel="permalink" '.$disabled.'>Delete</a> | </span>
         <span class="make_default"> <a href="'.get_admin_url() . 'admin.php?page=cart-product-feed-admin&action=make_account_default&id=' .$records->id.'&task=1" title="Make this account Default" rel="permalink" '.$disabled.'>Make Default</a></span></div></td><td>' . $records->user_name . ' </td><td>' . $records->site_code . ' </td><td>' . $records->valid_until . ' </td>';
        }
        $html .= ' </tbody>';
        $html .= ' <tfoot>
            <tr>
            <th scope = "col" class = "manage-column column-details column-primary">Account</th><th scope = "col" class = "manage-column column-user_name">User</th><th scope = "col" class = "manage-column column-site">Site</th><th scope = "col" class = "manage-column column-valid_until">Valid Until</th>
            </tr>
            </tfoot>

            </table>';

        echo $html;
    }

    ?>
</div>
<style type="text/css">
    a.disabled {
        pointer-events: none;
        cursor: default;
        color : gray !important;
    }
</style>