<?php

declare(strict_types=1);

namespace Chiron\Flash\Twig;

use Chiron\RequestContext\RequestContext;
use Chiron\Routing\UrlGeneratorInterface;
use Closure;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;
use Chiron\Flash\FlashBagScope;
use Chiron\Flash\FlashBag;

// Exemple n°1
/*
{% if messages %}
<ul class="messages">
    {% for message in messages %}
    <li{% if message.tags %} class="{{ message.tags }}"{% endif %}>{{ message }}</li>
    {% endfor %}
</ul>
{% endif %}
*/

// Exemple n°2
/*
{% if messages %}
<ul class="messages">
    {% for message in messages %}
    <li{% if message.tags %} class="{{ message.tags }}"{% endif %}>
        {% if message.level == DEFAULT_MESSAGE_LEVELS.ERROR %}Important: {% endif %}
        {{ message }}
    </li>
    {% endfor %}
</ul>
{% endif %}
*/

// TODO : déplacer cette classe dans le package chiron/twig-bidge !!!!

/**
 * Read the flash messages.
 */
final class FlashesExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @var Messages
     */
    protected $flash;

    /**
     * Constructor.
     *
     * @param Messages $flash the Flash messages service provider
     */
    public function __construct(FlashBagScope $flash)
    {
        $this->flash = $flash;
    }

    public function getGlobals(): array
    {
        return [
            'DEFAULT_MESSAGE_LEVELS' => FlashBag::DEFAULT_LEVELS
        ];
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        // TODO : virer le Closure et la méthode privée getMessages pour directement appeller le callable suivant : [$this->flash, 'getFlashBag']
        return [
            new TwigFunction('flashes', Closure::fromCallable([$this, 'getFlashBag']))
        ];
    }

    private function getFlashBag(): FlashBag//iterable
    {
        //die(var_dump($this->flash->getFlashBag()));
        return $this->flash->getFlashBag();//->getMessages();
    }
}
