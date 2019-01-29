<?php

namespace App\Controllers;

class Controller {

   protected $container;

   // constructor receives container instance
   public function __construct($container) {
       $this->container = $container;
   }

   // get easy access to container instances
   public function __get($instance) {
   		if($this->container->{$instance}) {
   			return $this->container->{$instance};
   		}
   }
}