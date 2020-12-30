<?php

declare(strict_types=1);

namespace Chiron\Flash;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// TODO : passer la classe en final et virer les protected !!!
class Message implements \JsonSerializable // \ArrayAccess
{
    private $message;
    private $level;
    private $extraTags;

    //private $extra_tags; // TODO : renommer en extra ou extra_tags ????

    // TODO : attention il faut que $extra_tags soit un array de string !!!!
    public function __construct(string $message, int $level, array $extraTags)
    {
        $this->message = $message;
        $this->level = $level;
        $this->extraTags = $extraTags;
    }

    /**
     * Function needed in the twig engine to ensure __get() work fine.
     * Only theses
     */
    public function __isset(string $property): bool
    {
        // TODO : faire directement un 'return in_array(xxx);'
        if (in_array($property, ['level', 'level_tags', 'extra_tags', 'tags'])) {
            return true;
        }

        return false;
    }

    /**
     * Limit the visibility of the class property read in the view or template.
     * The property "$this->message" can only be read using the ToString() method.
     */
    public function __get(string $property)
    {
        if ($property === 'level') {
            return $this->level;
        }

        if ($property === 'level_tags') {
            //return ;
            return ' class-info';
        }

        if ($property === 'extra_tags') {
            return implode(' ', $this->extraTags);
        }

        if ($property === 'tags') {
            //return '' . implode(' ', $class);
            return ' class-info, class1, class2';
        }

        // The other private properties can't be accessed !
        trigger_error(sprintf('Undefined property: %s::$%s', static::class, $property), E_USER_ERROR);
    }

    public function __toString() {
        return $this->message;
    }

    public function jsonSerialize()
    {
        return [
            $this->message,
            $this->level,
            $this->extraTags,
        ];
    }






/*
    def tags(self):
        return ' '.join(tag for tag in [self.extra_tags, self.level_tag] if tag)

    @property
    def level_tag(self):
        return LEVEL_TAGS.get(self.level, '')
*/

}

