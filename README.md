OrkestraGuzzleBundle
====================

Symfony2 bundle for Guzzle

Installation
============

1. Using Composer
-------------------------------

To install OrkestraGuzzleBundle with Composer just add the following to your
`composer.json` file:

    // composer.json
    {
        // ...
        require: {
            // ...
            "orkestra/guzzle-bundle": "dev-master"
        }
    }
    
Then, you can install the new dependencies by running Composer's ``update``
command from the directory where your ``composer.json`` file is located:

    $ php composer.phar update
    
Composer will automatically download all required files, and install them
for you. All that is left to do is to update your ``AppKernel.php`` file, and
register the new bundle:

    <?php

    // in AppKernel::registerBundles()
    $bundles = array(
        // ...
        new Orkestra\Bundle\GuzzleBundle\GuzzleBundle(),
        // ...
    );
        
Usage
=====
To create a service you must create a service file:

    <?php
    // ..src/Acme/AcmeBundle/Services/AcmeService.php
    namespace Acme\AcmeBundle\Services;
    
    use Orkestra\Bundle\GuzzleBundle\Services\Service as BaseService;
    use Orkestra\Bundle\GuzzleBundle\Services\Annotation\Command;
    use Orkestra\Bundle\GuzzleBundle\Services\Annotation\Doc;
    use Orkestra\Bundle\GuzzleBundle\Services\Annotation\Param;
    use Orkestra\Bundle\GuzzleBundle\Services\Annotation\Headers;
    use Orkestra\Bundle\GuzzleBundle\Services\Annotation\Type;
    
    class AcmeService extends BaseService
    {
        protected $vars;
        
        public function __construct(array $vars = array())
        {
            $this->vars = array_merge($this->vars, $vars);
            parent::__construct($this->vars);
        }
        
        public function authenticate()
        {
            //Use this function to create hashes and set headers for authenication
        }
        
        /**
         * @Command(name="acme_users", method="GET", uri="/users")
         * @Doc("Get list of Acme users")
         */
        public function acmeUsersCommand()
        {
            return $this->getResponse();
        }
        
        /**
         * @Command(name="acme_user_id", method="GET", uri="/users/{{user_id}}")
         * @Doc("Find user by id")
         * @Param(name="user_id", type="integer", required="true")
         */
        public function acmeUserByIdCommand()
        {
            //Here you can manipulate the response, you can binding it to an object for example
            return $this->getResponse();
        }
    }
    
After the service is created you must define it in your `config.yml` file

    guzzle:
      services:
        AcmeService:
          class: Acme\AcmeBundle\Services\AcmeService
          params:
            base_url: https://api.acme.com
          
The params are passed to the constructor as an array and is processed into Guzzle's configuration. Now you can
use your service with Guzzle!!

    <?php
      //...
      class AcmeController extends Controller
      {
          public function acmeAction()
          {
              $guzzle = $this->get('guzzle');
              //Get list of users
              $users = $guzzle->get('AcmeService')->execute('acme_users');
              //Get a user by id
              $oneUser = $guzzle->get('AcmeService')->excute('acme_user_id', array('user_id' => 1));
          }
      }
      
This bundle is work in progress and may have changes that breaks compatitbility.
