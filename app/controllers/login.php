<?php

class ControllersLogin {
    public static function home() {

        if (!empty($_POST)) {
            $username = $_POST["login"];
            $password = $_POST["password"];
            $config = require __DIR__."/../../config.php";
            if ($config["auth_type"] == "ldap") {
                $_config = array(
                    'domain_controllers'    => $config["ldap"]["domain"],
                    'base_dn'               => $config["ldap"]["base_dn"],
                    'admin_username'        => $config["ldap"]["user"],
                    'admin_password'        => $config["ldap"]["password"],
                );
                $ad = new \Adldap\Adldap();
                $ad->addProvider($_config);

                try {
                    // If a successful connection is made to your server, the provider will be returned.
                    $provider = $ad->connect();
                    $results = $provider->search()->find("charly");
                    trace($results);
                }
                catch (\Adldap\Auth\BindException $e) {
                    trace($e);
                    die();
                }
            }
        }

        $template = ClassesTwig::getInstance();
        return $template->render("views/login.twig");
    }

    public static function logout() {
        HelpersUser::logout();
        redirect("/");
    }
}
