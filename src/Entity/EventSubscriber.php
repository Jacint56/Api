<?php
namespace App\Entity\EventSubscriber;
use Symfony\Component\HttpFoundation\Cookie;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
class JWTSubscriber implements EventSubscriberInterface
{
   public static function getSubscribedEvents()
   {
       return [
           Events::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess'
       ];
   }
   public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
   {
       $eventData = $event->getData();
       if(isset($eventData['token']))
       {
           $response = $event->getResponse();
           $jwt = $eventData['token'];
           // Set cookie
           $response->headers->setCookie(
               new Cookie(
                   "BEARER",
                   $jwt,
                   new \DateTime("+1 day"),
                   "/",
                   null,
                   true,
                   true,
                   false,
                   'strict'
               )
           );
       }
   }
}