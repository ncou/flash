<?php

declare(strict_types=1);

namespace Chiron\Flash;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use JsonSerializable;

final class Message implements JsonSerializable
{
    private $level;
    private $message;
    private $extraTags;
    private $levelTags;

    public function __construct(int $level, string $message, array $extraTags = [], array $levelTags = [])
    {
        $this->level = $level;
        $this->message = $message;
        $this->extraTags = $extraTags;
        $this->levelTags = $levelTags;
    }

    /**
     * Function needed in the twig engine to ensure __get() work fine.
     * Only theses
     */
    public function __isset(string $property): bool
    {
        return in_array($property, ['level', 'extra_tags', 'level_tags', 'tags']);
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

        if ($property === 'extra_tags') {
            return implode(' ', $this->extraTags);
        }

        if ($property === 'level_tags') {
            return implode(' ', $this->levelTags);
        }

        if ($property === 'tags') {
            return implode(' ', array_merge($this->extraTags, $this->levelTags));
        }

        // The other private properties can't be accessed !
        trigger_error(sprintf('Undefined property: %s::$%s', static::class, $property), E_USER_ERROR);
    }

    public function __toString() {
        return $this->message;
    }

    /**
     * It's normal the property $this->levelTags is missing in the json serialization
     * because this data is from the FlashBag configuration, not related to the Message data.
     */
    public function jsonSerialize()
    {
        return [
            $this->level,
            $this->message,
            $this->extraTags,
            //$this->levelTags,
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

