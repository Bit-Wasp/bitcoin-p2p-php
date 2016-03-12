<?php

namespace BitWasp\Bitcoin\Networking\Dns;

use React\Dns\Query\Query;
use React\Dns\Model\Message;

class Resolver extends \React\Dns\Resolver\Resolver
{
    /**
     * @param Query $query
     * @param Message $response
     * @return array
     * @throws \React\Dns\RecordNotFoundException
     */
    public function extractAddress(Query $query, Message $response)
    {
        $answers = $response->answers;
        $addresses = $this->resolveAliases($answers, $query->name);
        if (0 === count($addresses)) {
            $message = 'DNS Request did not return valid answer.';
            throw new \React\Dns\RecordNotFoundException($message);
        }

        return $addresses;
    }
}
