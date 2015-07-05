# bitcoin-p2p-php
Implementation of Bitcoin protocol using ReactPHP

[![Build Status](https://travis-ci.org/Bit-Wasp/bitcoin-p2p-php.svg?branch=master)](https://travis-ci.org/Bit-Wasp/bitcoin-p2p-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Bit-Wasp/bitcoin-p2p-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Bit-Wasp/bitcoin-p2p-php/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Bit-Wasp/bitcoin-p2p-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Bit-Wasp/bitcoin-p2p-php/?branch=master)


This library aims to allow event driven communication with the bitcoin protocol. It makes use of `bitwasp/bitcoin` for underlying network structures. ReactPHP was used to handle socket connections because of it's non-blocking nature.

