<?php

namespace Symfony\Component\Translation;

use Symfony\Component\Translation\Resource\ResourceInterface;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * MessageCatalogue.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class MessageCatalogue implements MessageCatalogueInterface
{
    protected $messages = array();
    protected $locale;
    protected $resources;

    /**
     * Constructor.
     *
     * @param string $locale   The locale
     * @param array  $messages An array of messages classified by domain
     */
    public function __construct($locale, array $messages = array())
    {
        $this->locale = $locale;
        $this->messages = $messages;
        $this->resources = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getDomains()
    {
        return array_keys($this->messages);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages($domain = null)
    {
        if (null === $domain) {
            return $this->messages;
        }

        return isset($this->messages[$domain]) ? $this->messages[$domain] : array();
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage($id, $translation, $domain = 'messages')
    {
        $this->addMessages(array($id => $translation), $domain);
    }

    /**
     * {@inheritdoc}
     */
    public function hasMessage($id, $domain = 'messages')
    {
        return isset($this->messages[$domain][$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage($id, $domain = 'messages')
    {
        return isset($this->messages[$domain][$id]) ? $this->messages[$domain][$id] : $id;
    }

    /**
     * {@inheritdoc}
     */
    public function setMessages($messages, $domain = 'messages')
    {
        $this->messages[$domain] = array();

        $this->addMessages($messages, $domain);
    }

    /**
     * {@inheritdoc}
     */
    public function addMessages($messages, $domain = 'messages')
    {
        if (!isset($this->messages[$domain])) {
            $this->messages[$domain] = $messages;
        } else {
            $this->messages[$domain] = array_replace($this->messages[$domain], $messages);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addCatalogue(MessageCatalogueInterface $catalogue)
    {
        if ($catalogue->getLocale() !== $this->locale) {
            throw new \LogicException(sprintf('Cannot add a catalogue for locale "%s" as the current locale for this catalogue is "%s"', $catalogue->getLocale(), $this->locale));
        }

        foreach ($catalogue->getMessages() as $domain => $messages) {
            $this->addMessages($messages, $domain);
        }

        foreach ($catalogue->getResources() as $resource) {
            $this->addResource($resource);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addFallbackCatalogue(MessageCatalogueInterface $catalogue)
    {
        foreach ($catalogue->getDomains() as $domain) {
            foreach ($catalogue->getMessages($domain) as $id => $translation) {
                if (false === $this->hasMessage($id, $domain)) {
                    $this->setMessage($id, $translation, $domain);
                }
            }
        }

        foreach ($catalogue->getResources() as $resource) {
            $this->addResource($resource);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getResources()
    {
        return array_values(array_unique($this->resources));
    }

    /**
     * {@inheritdoc}
     */
    public function addResource(ResourceInterface $resource)
    {
        $this->resources[] = $resource;
    }
}
