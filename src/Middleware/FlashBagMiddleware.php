<?php

declare(strict_types=1);

namespace Chiron\Flash\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Flash\FlashBag;
use Chiron\Cookies\Cookie;
use Chiron\Cookies\CookieFactory;
use Chiron\Security\Signer;
use Chiron\Support\Random;
use Chiron\Security\Exception\BadSignatureException;
use Chiron\Flash\FlashBagFactory;

//https://docs.djangoproject.com/en/3.1/ref/contrib/messages/

//https://github.com/django/django/blob/master/django/contrib/messages/middleware.py

/**
 * Add Flash messages using cookies.
 */
class FlashBagMiddleware implements MiddlewareInterface
{
	/**
     * Request attribute name used to store the token value used later.
     */
    public const ATTRIBUTE = 'flashBag'; // TODO : utiliser la valeur '__flashBag__' ???

    /** @var FlashBagFactory */
    private $flashBagFactory;

    /** @var CookieFactory */
    private $cookieFactory;

    /** @var Signer */
    private $signer;

    /**
     * @param FlashBagFactory $flashBagFactory
     * @param CookieFactory   $cookieFactory
     * @param Signer          $signer
     */
    public function __construct(FlashBagFactory $flashBagFactory, CookieFactory $cookieFactory, Signer $signer)
    {
        $this->flashBagFactory = $flashBagFactory;
        $this->cookieFactory = $cookieFactory;
        // Use the class name as salt to have a different signatures in different application module.
        $this->signer = $signer->withSalt(self::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $flashBag = $this->prepareFlashBag($request);

        // TODO : déplacer le nom de l'attribut dans la classe FlashBag::class ????
        $response = $handler->handle($request->withAttribute(self::ATTRIBUTE, $flashBag));

        $cookie = $this->prepareCookie($flashBag);

        //die(var_dump((string)$cookie));

        return $response->withAddedHeader('Set-Cookie', (string) $cookie);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return FlashBag
     */
    private function prepareFlashBag(ServerRequestInterface $request): FlashBag
    {
        $messages = $this->getMessagesFromCookie($request->getCookieParams());

        //return new FlashBag($messages ?? []);
        //return FlashBag::create($messages ?? []);
        return $this->flashBagFactory->create($messages ?? []);
    }

    /**
     * Attempt to locate session ID in request.
     *
     * @param array $cookies
     *
     * @return array|null
     */
    // TODO : améliorer ce code + faire un typehint de retour à "array" la valeur null ne sert à rien retourner un tableau vide dans ce cas là !!!
    private function getMessagesFromCookie(array $cookies): ?array
    {
        $name = 'flash-messages'; //$this->sessionConfig->getCookieName();
        $value = $cookies[$name] ?? '';

        try {
        	$data = $this->signer->unsign($value);

        	if ($data !== null) {
        		$data = json_decode($data, true);
        	}

            return $data;
        } catch (BadSignatureException $e){
            // Don't blow up the middleware if the signature is invalid.
            return null;
        }
    }

    /**
     * Create Session cookie with the signed session ID value.
     * Sign the value stored in the cookie for better security (in case of XSS attack).
     *
     * @param string $sessionId
     *
     * @return Cookie
     */
    // TODO : renommer en createCookie() !!!!
    private function prepareCookie(FlashBag $flashBag): Cookie
    {
        $name = 'flash-messages'; //$this->sessionConfig->getCookieName();

        if ($flashBag->hasBeenUsed()) {

        	// TODO : faire un DeleteCookie, c'est à dire marquer le cookie comme expiré !!!!
        	$value = '';
        	$expires = '-9999'; //new DateTimeImmutable('-1 year');
        } else {
        	// TODO : il faudra surement vérifier que le flag $flashMessages->hasBeenAdded() === true pour créer le cookie, car on si on n'a pas ajouté de valeurs cela ne sert à rien de créer ou de mettre à jour le cookie !!!! <== attention à bien gérer le cas ou le cookie a été manipulé de maniére malicieuse et qu'on veut forcer la suppression du cookie, actuellement on retourne un tableau vide de messages pour effectuer un clear lors de l'enregistrement du cookie, mais si maintenant cette enregistrement n'est pas effectuer à chaque fois on va avoir un probléme !!!! il faudra surement simuler via le flag hasBeenUsed pour forcer le delte cookie !!!!

        	// Update the cookies values.
        	// TODO : faire ce test uniquement si il y a eu un nouveau message, car si on n'a rien touché ajouté ce cookie header ne servira à rien (rappel pas de prolongation du cookie lifetime car il est à 0 pour indiquer que c'est une cookie valable jusqu'à la fermeture du browser).
        	$data = json_encode($flashBag);

        	$value = $this->signer->sign($data);
        	// The cookie will live until the browser is closed ($expires = null).
        	$expires = null;
        }

        return $this->cookieFactory->create($name, $value, $expires);
    }
}
