<?php

namespace Wnx\AlfredEmojiPack\Console;

use Symfony\Component\Console\Application as ConsoleApplication;

class Application extends ConsoleApplication
{
    public function __construct()
    {
        parent::__construct('Wnx\Emoji package generator', '1.0.0');

        $command = new GenerateCommand();
        $this->add($command);
        $this->setDefaultCommand($command->getName());
    }
}
