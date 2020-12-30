<?php

declare(strict_types=1);

namespace Chiron\Flash;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

//https://docs.djangoproject.com/en/3.1/ref/contrib/messages/

// TODO : ajouter un level 'notice' ???

// TODO : déplacer ces constantes dans la classe Message.
// TODO : si on créé une Facade pour cette classe il faudra surement ajouter aussi dans cette facade les constantes public en faisant un lien vers Message.
// Ex : public const INFO = Message::INFO;

/*
DEBUG = 10
INFO = 20
SUCCESS = 25
WARNING = 30
ERROR = 40

DEFAULT_TAGS = {
    DEBUG: 'debug',
    INFO: 'info',
    SUCCESS: 'success',
    WARNING: 'warning',
    ERROR: 'error',
}

DEFAULT_LEVELS = {
    'DEBUG': DEBUG,
    'INFO': INFO,
    'SUCCESS': SUCCESS,
    'WARNING': WARNING,
    'ERROR': ERROR,
}
*/


//https://github.com/django/django/blob/master/django/contrib/messages/middleware.py
//https://github.com/django/django/blob/429d089d0a8fbd400e0c010708df4f0d16218970/django/contrib/messages/storage/base.py
//https://github.com/django/django/blob/429d089d0a8fbd400e0c010708df4f0d16218970/django/contrib/messages/storage/cookie.py
//https://github.com/django/django/blob/5fcfe5361e5b8c9738b1ee4c1e9a6f293a7dda40/django/contrib/messages/api.py

// TODO : il faudra surement que cette classe soit de type singleton !!!!

// TODO : créer une facade nommée "Flash" + créer un FalshManager qui ira chercher l'objet FlashMessage directement dans la request (cf ce qui se fait pour la Session) + créer un provider qui va binder la string FlashMessage::class avec l'instance présente dans la request (cf ce qui se fait dans le package Session).

class FlashBag implements \Countable, \IteratorAggregate, \JsonSerializable
{
    private $messages; // TODO : renommer en "queuedMessages"

    // TODO : passer ces 2 variables en "public" !!! et virer les méthode hasBeenUsed / hasBeenModified !!!
    private $used = false;
    private $updated = false; // TODO : renomment en "modified" ???

    // TODO : créer une méthode static "createFromJson" qui se chargerai de faire un new self et d'alimenter le $messages[] avec un tableau de classe Message::class initailisées depuis du json en entrée. Eventuellement passer le constructeur en private pour utiliser uniquement la méthode static createFromJson pour initialiser cette classe !!!!
    /**
     * @param $messages array<Message>
     */
    public function __construct(array $messages = [])
    {
        // TODO : s'assurer que c'est bien des objets de type Message !!!!
        $this->messages = $messages;


        //$this->messages[] = new Message('TOTO is BACK', 40, (array) 'classA');


        //die(var_dump(json_encode($this)));
    }

    /**
     * @param array $messages Raw array with in the order : message (string) / level (int) / extraTags (array<string>)
     */
    // TODO : faire plutot une méthode initialize() ???
    public static function create(array $messages = []): self
    {
        $bag = new self();

        foreach ($messages as $message) {
            $bag->messages[] = new Message(...$message); // TODO : sécuriser la création en vérifiant le type de paramétres qu'on passe au constructeur ???
        }

        return $bag;
    }

    // TODO : utiliser plutot des variables de classe en "public" pour accéder à ces données là !!!!
    public function hasBeenUsed(): bool
    {
        return $this->used;
    }

    /**
     * Queue a message to be stored.
     * The message is only queued if it contained something and its level is
     * not less than the recording level ('self.level').
     */
    // TODO : permettre de passer un "mixed" comme valeur pour la paramétre $message, ne pas limiter le type à une string ????
    public function add(string $message)
    {
        # Check that the message level is not less than the recording level.
        //level = int(level)
        //if level < self.level:
        //    return
        # Add the message.
        $this->updated = true;

        //message = Message(level, message, extra_tags=extra_tags)

        //$this->messages[] = $message;
        $this->messages[] = new Message($message, 40, (array) 'classA');
    }

    /**
     * Get an iterator for the items. This mean the flash messages have been readed.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        // This flag indicate the messages has been accessed (so it can be cleared/removed later).
        $this->used = true;

        return new \ArrayIterator($this->messages);
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->messages);
    }

    public function jsonSerialize()
    {
        return $this->messages;
    }

}
