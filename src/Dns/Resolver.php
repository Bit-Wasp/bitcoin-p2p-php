<?php

namespace BitWasp\Bitcoin\Networking\Dns;


class Resolver extends \React\Dns\Resolver\Resolver
{
    public function extractAddress(\React\Dns\Query\Query $query, \React\Dns\Model\Message $response)
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